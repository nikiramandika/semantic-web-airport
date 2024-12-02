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

if (empty($query)) {
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results - <?php echo htmlspecialchars($query); ?></title>
    <link rel="icon" type="image/x-icon" href="assets/img/logo.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="css/output.css">
    <style>
        input[type="search"]::-webkit-search-cancel-button {
            appearance: none;
            /* Remove the default cancel button appearance */
            background: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20' fill='white'%3E%3Cpath fill-rule='evenodd' d='M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z' clip-rule='evenodd'/%3E%3C/svg%3E") no-repeat center;
            background-size: 16px;
            /* Adjust the size of the X icon */
            width: 30px;
            /* Size of the cancel button */
            height: 30px;
            cursor: pointer;
            /* Pointer cursor for better UX */
            background-color: transparent;
            /* Transparent button background */
        }

        /* Works on Firefox */
        body::-webkit-scrollbar-track {
            -webkit-box-shadow: inset 0 0 6px rgba(0, 0, 0, 0.3);
            border-radius: 10px;
            background-color: transparent;
        }

        body::-webkit-scrollbar {
            width: 12px;
            background-color: transparent;
        }

        body::-webkit-scrollbar-thumb {
            border-radius: 10px;
            -webkit-box-shadow: inset 0 0 6px rgba(0, 0, 0, .3);
            background-color: rgb(31 41 55);
        }


        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        /* body {
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
        } */

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

        input:-webkit-autofill {
            background-color: #1e293b !important;
            color: white !important;
            box-shadow: 0 0 0px 1000px rgb(31 41 55 / var(--tw-bg-opacity, 1)) inset !important;
            border: 0.1px solid white;
            -webkit-text-fill-color: white !important;
            font-size: 1.5rem
                /* 24px */
            ;
            line-height: 2rem
                /* 32px */
            ;
        }

        input:active {
            background-color: #1e293b !important;
            color: white !important;
            box-shadow: 0 0 0px 1000px rgb(31 41 55 / var(--tw-bg-opacity, 1)) inset !important;
            border: 0.1px solid transparent;
        }
    </style>
</head>

<body class="bg-gray-900">
    <nav class="fixed top-0 left-0 w-full z-10 bg-gray-500/[0.1] backdrop-blur-md">
        <div class="mx-auto max-w-7xl px-2 sm:px-6 lg:px-8 py-2 ">
            <div class="relative flex h-16 items-center justify-center">
                <!-- Bagian logo yang akan diposisikan di tengah -->
                <div class="flex flex-1 items-center justify-center">
                    <div class="shrink-0 flex text-white">
                        <a href="index.php">
                            <img class="h-10 w-auto" src="assets/img/logo.png" alt="Your Company">
                        </a>
                        <!-- <p class="text-md ml-2 my-auto">Airsearch</p> -->
                    </div>
                </div>
            </div>
        </div>
    </nav>
    <div class="container w-full mx-auto my-auto p-16 mt-16">


        <?php
        function findClosestMatch($query, $validQuery)
        {
            $bestMatch = null;
            $lowestDistance = PHP_INT_MAX;

            foreach ($validQuery as $valid) {
                $distance = levenshtein(strtolower($query), strtolower($valid));
                if ($distance < $lowestDistance && $distance > 0) {
                    $lowestDistance = $distance;
                    $bestMatch = $valid;
                }
            }

            return ($lowestDistance <= 2) ? $bestMatch : null;
        }

        $validQuery = [
            'Jakarta',
            'Surabaya',
            'Bandung',
            'Medan',
            'Semarang',
            'Yogyakarta',
            'Denpasar',
            'Makassar',
            'Palembang',
            'Malang',
            'Tangerang',
            'Depok',
            'Bekasi',
            'Samarinda',
            'Batam',
            'Bandar Lampung',
            'Pekanbaru',
            'Pontianak',
            'Banjarmasin',
            'Mataram',
            'Cirebon',
            'Manado',
            'Ambon',
            'Kupang',
            'Tasikmalaya',
            'Cilegon',
            'Surakarta',
            'Padang',
            'Balikpapan',
            'Jambi',
            'Kendari',
            'Sukabumi',
            'Bengkulu',
            'Gorontalo',
            'Palu',
            'Ternate',
            'Jayapura',
            'Bontang',
            'Magelang',
            'Solok',
            'Blitar',
            'Probolinggo',
            'Jember',
            'Sidoarjo',
            'Sragen',
            'Sungai Penuh',
            'Banjarbaru',
            'Palopo',
            'Banjarmasin',
            'Pati',
            'Karawang',
            'Lombok',
            'Ponorogo',
            'Banyuwangi',
            'Kediri',
            'Klaten',
            'Tegal',
            'Luwuk',
            'Bima',
            'Samarinda',
            'Madiun',
            'Selayar',
            'Bondowoso',
            'Bontang',
            'Tegal',
            'Magelang',
            'Pontianak',
            'Serang',
            'Langsa',
            'Batang',
            'Subang',
            'Rembang',
            'Raja Ampat',
            'Buru',
            'Cianjur',
            'Kuningan',
            'Sukabumi',
            'Indonesia',
            'United States',
            'Canada',
            'United Kingdom',
            'Australia',
            'Germany',
            'France',
            'Italy',
            'Spain',
            'Netherlands',
            'Sweden',
            'Norway',
            'Denmark',
            'Finland',
            'Portugal',
            'Belgium',
            'Switzerland',
            'Austria',
            'Poland',
            'Greece',
            'Japan',
            'South Korea',
            'China',
            'India',
            'Russia',
            'Brazil',
            'Argentina',
            'Mexico',
            'South Africa',
            'Egypt',
            'Saudi Arabia',
            'Turkey',
            'United Arab Emirates',
            'Singapore',
            'Malaysia',
            'Thailand',
            'Vietnam',
            'Philippines',
            'New Zealand',
            'South Korea',
            'India',
            'Pakistan',
            'Bangladesh',
            'Chile',
            'Peru',
            'Colombia',
            'Ecuador',
            'Czech Republic',
            'Slovakia',
            'Ukraine',
            'Romania',
            'Bulgaria',
            'Turkey',
            'Israel',
            'Iraq',
            'Iran'
        ];
        $query = $_GET['query'] ?? '';
        $suggestion = null;

        if (!empty($query)) {
            $suggestion = findClosestMatch($query, $validQuery);
        }
        ?>

        <div class="w-full">
            <form action="results.php" method="get">
                <div class="relative w-7/12 mx-auto">
                    <input type="search" id="default-search"
                        class="bg-gray-800 backdrop-blur-lg block w-full py-5 ps-8 pr-16 text-2xl text-gray-100 border border-slate-400/20  ring-1 ring-blue-800/5 rounded-full focus:outline-none placeholder:text-gray-600 placeholder:text-md placeholder:my-4  focus:bg-gray-800 focus:text-gray-100 autofill:text-gray-100 active:bg-gray-800"
                        value="<?php echo htmlspecialchars($query) ?>" placeholder="Search airports, by city, country"
                        required name="query" id="queryInput" />
                    <button type="submit"
                        class="text-white absolute end-2.5 bottom-4 focus:outline-none focus:ring-blue-300 font-medium rounded-full text-xl px-5 py-2  my-auto"><svg
                            class="w-6 h-6 text-gray-100" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                            fill="none" viewBox="0 0 20 20">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z" />
                        </svg> </button>
                </div>
            </form>

            <?php if ($suggestion): ?>
                <div class="text-center mt-2 text-blue-500">
                    Did you mean:
                    <a class="hover:underline font-bold" href="?query=<?php echo urlencode($suggestion); ?>">
                        <?php echo $suggestion; ?>
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <div class="search-results">
            <?php
            if (empty($query)) {
                header("Location: index.php");
                exit();
            } else {
                try {
                    // Format query untuk `dbr:`
                    $formattedQuery = formatToDBR($query);

                    // Query SPARQL untuk DBpedia
                    $sparqlQuery = "
SELECT DISTINCT (SAMPLE(?airport) AS ?airport) (SAMPLE(?name) AS ?name) (SAMPLE(?iata) AS ?iata)
    (SAMPLE(?location) AS ?location) (SAMPLE(?city) AS ?city)
WHERE {
    ?airport rdf:type dbo:Airport .
    ?airport rdfs:label ?name .
    OPTIONAL { ?airport geo:lat ?lat . }
    OPTIONAL { ?airport geo:long ?long . }
    OPTIONAL { ?airport dbo:location ?location . }
    OPTIONAL { ?airport dbo:city ?city . }
    { ?airport dbo:iataLocationIdentifier ?iata . }
    FILTER (
        lang(?name) = 'en' &&
        (
            (CONTAINS(LCASE(STR(?location)), LCASE('$formattedQuery')) ||
            CONTAINS(LCASE(STR(?city)), LCASE('$formattedQuery')) ||
            CONTAINS(LCASE(?name), LCASE('$query')) ||
            LCASE(?iata) = LCASE('$query'))
        )
    )
    BIND(REPLACE(LCASE(?name), '[^a-z0-9]', '') AS ?normalizedName)
}
GROUP BY ?normalizedName
ORDER BY ?normalizedName

                ";

                    // Query SPARQL untuk Jena Fuseki
                    $fusekiQuery = "
                    PREFIX www: <http://www.tubes_ws_airport.org#>
                    SELECT 
                        (REPLACE(STR(?airport), 'http://www.tubes_ws_airport.org#', '') AS ?airportName) 
                        ?kodeIATA 
                        (REPLACE(STR(?location), 'http://www.tubes_ws_airport.org#', '') AS ?locationName)
                        ?city
                        ?location
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

                        // Ambil location dan city, dan formatkan
                        $location = isset($result->locationName) ? str_replace('_', ' ', htmlspecialchars($result->locationName)) : null;
                        $city = isset($result->city) ? str_replace('_', ' ', htmlspecialchars($result->city)) : null;

                        if ($iata && !isset($addedIATA[$iata])) {
                            $combinedResults[] = [
                                'airport' => htmlspecialchars($result->airportName),
                                'name' => $airportNama,
                                'iata' => $iata,
                                'location' => $location,  // Menambahkan lokasi
                                'city' => $city,          // Menambahkan kota
                                'source' => 'Fuseki',
                            ];
                            // Tandai IATA sudah ditambahkan
                            $addedIATA[$iata] = true;
                        }
                    }


                    // Tambahkan hasil dari DBpedia
                    foreach ($results as $result) {
                        $iata = isset($result->iata) ? htmlspecialchars($result->iata) : null;
                        $location = isset($result->location) ? htmlspecialchars($result->location) : null;
                        $city = isset($result->city) ? htmlspecialchars($result->city) : null;

                        // Hanya tambahkan jika kode IATA belum ada dalam $addedIATA
                        if ($iata && !isset($addedIATA[$iata])) {
                            $combinedResults[] = [
                                'airport' => htmlspecialchars($result->airport),
                                'name' => htmlspecialchars($result->name),
                                'iata' => $iata,
                                'location' => $location,
                                'city' => $city,
                                'source' => 'DBpedia',
                            ];
                            // Tandai IATA sudah ditambahkan
                            $addedIATA[$iata] = true;
                        }
                    }

                    $resultsPerPage = 20; // Menampilkan 10 hasil per halaman
            
                    $totalResults = count($combinedResults);
                    $totalPages = ceil($totalResults / $resultsPerPage);

                    // Menentukan halaman yang saat ini
                    $currentPage = isset($_GET['page']) ? (int) $_GET['page'] : 1;
                    $startIndex = ($currentPage - 1) * $resultsPerPage;

                    // Mengambil data hasil untuk halaman saat ini
                    $currentResults = array_slice($combinedResults, $startIndex, $resultsPerPage);


                    // Tampilkan hasil pencarian
                    echo "<div class='results-header mx-auto'>
    <h2 class='text-gray-400 my-8 mx-auto text-center p-3'>Search Results for \"" . htmlspecialchars($query) . "\"</h2>
</div>";

                    if (count($currentResults) > 0) {
                        echo "<div class=''> <ul class='results-list grid gap-8 lg:grid-cols-2'>";  // Menambahkan jarak antar list items
                        foreach ($currentResults as $result) {
                            // Memformat location dan city (menghilangkan URL dan mengganti _ dengan spasi)
                            $location = isset($result['location']) ? str_replace(['http://dbpedia.org/resource/', '_'], ['', ' '], $result['location']) : null;
                            $city = isset($result['city']) ? str_replace(['http://dbpedia.org/resource/', '_'], ['', ' '], $result['city']) : null;

                            echo "<a href='detail.php?uri=" . urlencode($result['airport']) . "&source=" . urlencode($result['source']) . "' class='block p-5 rounded-lg shadow-sm hover:shadow-md transition-shadow duration-300 bg-gray-800 hover:bg-gray-700'>";

                            echo "<li class='cursor-pointer'>";

                            echo "<span class='font-semibold text-lg text-blue-100 hover:text-teal-400'>{$result['name']} ({$result['iata']})</span>";

                            // Menampilkan lokasi dan kota jika ada
                            if ($location || $city) {
                                echo "<div class='airport-details text-gray-400 text-sm mt-2'>"; // mt-2 untuk memberi jarak antara nama bandara dan detail
                                if ($location && $city) {
                                    echo "<span class='font-medium'>{$city}</span>";
                                    echo "<span class='font-medium'>, {$location}</span>";
                                } else if ($city) {
                                    echo "<span class='font-medium'>{$city}</span>";
                                } else if ($location) {
                                    echo "<span class='font-medium'>{$location}</span>";
                                }
                                echo "</div>";
                            }

                            echo "</li>";
                            echo "</a>";
                        }
                        echo "</ul> </div>";
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
        <?php
        if ($totalPages > 1) {
            echo "<div class='pagination flex justify-center items-center mt-16 space-x-2'>";

            // Tombol Previous
            if ($currentPage > 1) {
                echo "<a href='?query=" . urlencode($query) . "&page=" . ($currentPage - 1) . "' class='px-4 py-2 bg-gray-800 hover:bg-gray-700 text-teal-500 rounded-l-lg flex items-center'>
        <svg xmlns='http://www.w3.org/2000/svg' class='w-4 h-4 mr-2' fill='none' stroke='currentColor' viewBox='0 0 24 24' stroke-width='2'>
            <path stroke-linecap='round' stroke-linejoin='round' d='M15 19l-7-7 7-7'></path>
        </svg>Previous
      </a>";
            } else {
                echo "<span class='px-4 py-2 bg-gray-600 text-gray-400 rounded-l-lg flex items-center'>
        <svg xmlns='http://www.w3.org/2000/svg' class='w-4 h-4 mr-2' fill='none' stroke='currentColor' viewBox='0 0 24 24' stroke-width='2'>
            <path stroke-linecap='round' stroke-linejoin='round' d='M15 19l-7-7 7-7'></path>
        </svg>Previous
      </span>";
            }

            // Tombol Halaman
            for ($i = 1; $i <= $totalPages; $i++) {
                if ($i == $currentPage) {
                    echo "<span class='px-4 py-2 bg-gray-800 text-teal-500 rounded-lg'>{$i}</span>";
                } else {
                    echo "<a href='?query=" . urlencode($query) . "&page={$i}' class='px-4 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded-lg'>{$i}</a>";
                }
            }

            // Tombol Next
            if ($currentPage < $totalPages) {
                echo "<a href='?query=" . urlencode($query) . "&page=" . ($currentPage + 1) . "' class='px-4 py-2 bg-gray-800 hover:bg-gray-700 text-teal-500 rounded-r-lg flex items-center'>
        Next
        <svg xmlns='http://www.w3.org/2000/svg' class='w-4 h-4 ml-2' fill='none' stroke='currentColor' viewBox='0 0 24 24' stroke-width='2'>
            <path stroke-linecap='round' stroke-linejoin='round' d='M9 5l7 7-7 7'></path>
        </svg>
      </a>";
            } else {
                echo "<span class='px-4 py-2 bg-gray-600 text-gray-400 rounded-r-lg flex items-center'>
        Next
        <svg xmlns='http://www.w3.org/2000/svg' class='w-4 h-4 ml-2' fill='none' stroke='currentColor' viewBox='0 0 24 24' stroke-width='2'>
            <path stroke-linecap='round' stroke-linejoin='round' d='M9 5l7 7-7 7'></path>
        </svg>
      </span>";
            }

            echo "</div>";
        }
        ?>


    </div>
</body>

</html>