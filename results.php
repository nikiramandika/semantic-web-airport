<?php
// results.php
error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
require 'vendor/autoload.php';

use EasyRdf\Sparql\Client;
// Koneksi ke endpoint Jena Fuseki
$fuseki = new Client('http://localhost:3030/Airport/query');

$sparql = new Client('https://dbpedia.org/sparql');
// Fungsi untuk memformat input menjadi format `dbr:`
function formatToDBR($input)
{
    $formatted = ucwords(strtolower(trim($input))); // Format kapitalisasi
    return str_replace(' ', '_', $formatted);       // Ganti spasi dengan underscore
}

// Ambil input dari pengguna
$query = isset($_GET['query']) ? trim($_GET['query']) : '';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results - <?php echo htmlspecialchars($query); ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            background: #f5f5f5;
            color: #333;
            line-height: 1.6;
        }

        .container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        .search-results {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .results-header {
            padding: 1.5rem;
            background: #2c3e50;
            color: white;
        }

        .results-list {
            list-style: none;
        }

        .results-list li {
            border-bottom: 1px solid #eee;
            transition: background-color 0.2s;
        }

        .results-list li:last-child {
            border-bottom: none;
        }

        .results-list a {
            display: block;
            padding: 1rem 1.5rem;
            color: #2c3e50;
            text-decoration: none;
            transition: background-color 0.2s;
        }

        .results-list a:hover {
            background-color: #f8f9fa;
        }

        .airport-name {
            font-size: 1.1rem;
            font-weight: 500;
        }

        .no-results {
            padding: 2rem;
            text-align: center;
            color: #666;
        }

        .error-message {
            padding: 1rem;
            background: #fee;
            color: #c00;
            border-radius: 4px;
            margin: 1rem 0;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="search-results">
            <?php
            if (empty($query)) {
                echo "<div class='no-results'><p>Search query is required.</p></div>";
            } else {
                try {
                    // Format query untuk `dbr:`
                    $formattedQuery = formatToDBR($query);

                    // Query SPARQL untuk DBpedia
                    $sparqlQuery = "
                    SELECT DISTINCT (SAMPLE(?airport) AS ?airport) (SAMPLE(?name) AS ?name) (SAMPLE(?iata) AS ?iata)
                    WHERE {
                        ?airport rdf:type dbo:Airport .
                        ?airport rdfs:label ?name .
                        OPTIONAL { ?airport geo:lat ?lat . }
                        OPTIONAL { ?airport geo:long ?long . }
                        OPTIONAL { ?airport dbo:location ?location . }
                        OPTIONAL { ?airport dbo:city ?city . }
                         { ?airport dbo:iataLocationIdentifier ?iata . } # Tambahkan properti IATA
                        FILTER (
                            lang(?name) = 'en' &&
                            (
                                # Cocokkan lokasi, kota, nama bandara, atau kode IATA
                                (CONTAINS(LCASE(STR(?location)), LCASE('$formattedQuery')) ||
                                CONTAINS(LCASE(STR(?city)), LCASE('$formattedQuery')) ||
                                CONTAINS(LCASE(?name), LCASE('$query')) ||
                                LCASE(?iata) = LCASE('$query'))
                            )
                        )
                        # Normalisasi nama bandara untuk mengelompokkan hasil
                        BIND(REPLACE(LCASE(?name), '[^a-z0-9]', '') AS ?normalizedName)
                    }
                    GROUP BY ?normalizedName
                    ORDER BY ?normalizedName
                    limit 20
                ";

                    // Query SPARQL untuk Jena Fuseki
                    $fusekiQuery = "
                    PREFIX www: <http://www.tubes_ws_airport.org#>
                    SELECT 
                        (REPLACE(STR(?airport), 'http://www.tubes_ws_airport.org#', '') AS ?airportName) 
                        ?kodeIATA 
                        (REPLACE(STR(?location), 'http://www.tubes_ws_airport.org#', '') AS ?locationName)
                    WHERE {
                        ?airport a www:Airport ;
                                 www:Kode_IATA ?kodeIATA ;
                                 www:Kode_ICAO ?kodeICAO ;
                                 www:city ?city ;
                                 www:email ?email ;
                                 www:latitude ?latitude ;
                                 www:longtitude ?longitude ;
                                 www:operator ?operator ;
                                 www:locatedIn ?location .
                        OPTIONAL { ?airport www:thumbnail ?thumbnail . }
                        FILTER (
                            CONTAINS(LCASE(STR(?location)), LCASE('$formattedQuery')) ||
                            CONTAINS(LCASE(STR(?city)), LCASE('$formattedQuery')) ||
                            CONTAINS(LCASE(STR(?airport)), LCASE('$query')) ||
                            LCASE(?kodeIATA) = LCASE('$query')
                        )
                    }
                    LIMIT 20
                ";

                    // Eksekusi query ke DBpedia dan Fuseki
                    // Eksekusi query ke DBpedia dan Fuseki
                    $results = $sparql->query($sparqlQuery);
                    $fusekiResults = $fuseki->query($fusekiQuery);

                    // Gabungkan hasil dari DBpedia dan Fuseki
                    $combinedResults = [];
                    $addedIATA = []; // Array untuk melacak IATA yang sudah ditambahkan
            
                    // Tambahkan hasil dari Fuseki terlebih dahulu untuk memprioritaskan
                    foreach ($fusekiResults as $result) {
                        // Ambil nama bandara dari Fuseki
                        $airportName = isset($result->airportName) ? htmlspecialchars($result->airportName) : 'Unknown Airport';
                        $airportNama = str_replace('_', ' ', $airportName);
                        $iata = isset($result->kodeIATA) ? htmlspecialchars($result->kodeIATA) : null;

                        if ($iata && !isset($addedIATA[$iata])) {
                            $combinedResults[] = [
                                'airport' => htmlspecialchars($result->airportName),
                                'name' => $airportNama,
                                'iata' => $iata,
                                'source' => 'Fuseki',
                            ];
                            // Tandai IATA sudah ditambahkan
                            $addedIATA[$iata] = true;
                        }
                    }

                    // Tambahkan hasil dari DBpedia
                    foreach ($results as $result) {
                        $iata = isset($result->iata) ? htmlspecialchars($result->iata) : null;

                        // Hanya tambahkan jika kode IATA belum ada dalam $addedIATA
                        if ($iata && !isset($addedIATA[$iata])) {
                            $combinedResults[] = [
                                'airport' => htmlspecialchars($result->airport),
                                'name' => htmlspecialchars($result->name),
                                'iata' => $iata,
                                'source' => 'DBpedia',
                            ];
                            // Tandai IATA sudah ditambahkan
                            $addedIATA[$iata] = true;
                        }
                    }

                    // Tampilkan hasil pencarian
                    echo "<div class='results-header'>
    <h2>Search Results for \"" . htmlspecialchars($query) . "\"</h2>
</div>";

                    if (count($combinedResults) > 0) {
                        echo "<ul class='results-list'>";
                        foreach ($combinedResults as $result) {
                            echo "<li>
            <a href='detail.php?uri=" . urlencode($result['airport']) . "&source=" . urlencode($result['source']) . "'>
                <span class='airport-name'>{$result['name']} ({$result['iata']})</span>
            </a>
        </li>";
                        }
                        echo "</ul>";
                    } else {
                        echo "<div class='no-results'>No airports found matching your search.</div>";
                    }
                } catch (Exception $e) {
                    echo "<div style='text-align:center; margin-top:50px; margin-bottom:50px;'>
                <p style='color:red; font-size:18px;'>We encountered a connection issue. Please refresh the page.</p>
                <button onclick='window.location.href=\"index.php\";' 
                    style='
                        padding:12px 25px; 
                        font-size:16px; 
                        color:white; 
                        background-color:#007BFF; 
                        border:none; 
                        border-radius:5px; 
                        cursor:pointer; 
                        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                        transition: background-color 0.3s, transform 0.2s;'
                    onmouseover='this.style.backgroundColor=\"#0056b3\"; this.style.transform=\"scale(1.05)\";' 
                    onmouseout='this.style.backgroundColor=\"#007BFF\"; this.style.transform=\"scale(1)\";'
                >
                    Go back
                </button>
                <button onclick='window.location.reload();' 
                    style='
                        padding:12px 25px; 
                        font-size:16px; 
                        color:white; 
                        background-color:#007BFF; 
                        border:none; 
                        border-radius:5px; 
                        cursor:pointer; 
                        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                        transition: background-color 0.3s, transform 0.2s;'
                    onmouseover='this.style.backgroundColor=\"#0056b3\"; this.style.transform=\"scale(1.05)\";' 
                    onmouseout='this.style.backgroundColor=\"#007BFF\"; this.style.transform=\"scale(1)\";'
                >
                    Refresh
                </button>
            </div>";
                }
            }
            ?>
        </div>
    </div>
</body>

</html>