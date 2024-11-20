<?php
// Aktifkan pelaporan error untuk debugging
error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
require 'vendor/autoload.php';

use EasyRdf\Sparql\Client;

// Ambil URI dan sumber data dari parameter GET
$uri = $_GET['uri'] ?? '';
$source = $_GET['source'] ?? '';

if (empty($uri) || empty($source)) {
    die("Invalid URI or source.");
}

try {
    if ($source === 'DBpedia') {
        // Buat SPARQL client untuk DBpedia
        $sparql = new Client('https://dbpedia.org/sparql');

        // Query untuk mendapatkan URI canonical jika ada pengalihan
        $redirectQuery = "
            SELECT DISTINCT ?canonicalUri
            WHERE {
                <$uri> dbo:wikiPageRedirects ?canonicalUri
            }
            LIMIT 1
        ";
        $redirectResult = $sparql->query($redirectQuery)->current();

        // Jika ada URI canonical, gunakan itu
        if ($redirectResult && isset($redirectResult->canonicalUri)) {
            $uri = $redirectResult->canonicalUri;
        }

        // Query untuk mendapatkan detail bandara dari DBpedia
        $sparqlQuery = "
            SELECT DISTINCT ?name ?abstract ?country ?city ?iata ?icao ?elevation 
                          ?runways ?operator ?website ?image ?lat ?long
            WHERE {
                <$uri> rdfs:label ?name ;
                       dbo:abstract ?abstract .
                OPTIONAL { <$uri> dbo:country ?country }
                OPTIONAL { <$uri> dbo:city ?city }
                OPTIONAL { <$uri> dbo:iataLocationIdentifier ?iata }
                OPTIONAL { <$uri> dbo:icaoLocationIdentifier ?icao }
                OPTIONAL { <$uri> dbo:elevation ?elevation }
                OPTIONAL { <$uri> dbo:runwayCount ?runways }
                OPTIONAL { <$uri> dbo:operator ?operator }
                OPTIONAL { <$uri> foaf:homepage ?website }
                OPTIONAL { <$uri> dbo:thumbnail ?image }
                OPTIONAL { <$uri> geo:lat ?lat }
                OPTIONAL { <$uri> geo:long ?long }
                FILTER(lang(?name) = 'en' && lang(?abstract) = 'en')
            }
            LIMIT 1
        ";

        $result = $sparql->query($sparqlQuery)->current();
    } elseif ($source === 'Fuseki') {
        // Buat SPARQL client untuk Jena Fuseki
        $fuseki = new Client('http://localhost:3030/Airport/query');
        $namespace = 'http://www.tubes_ws_airport.org#';
        $uri = $namespace . $uri;


        // Query untuk mendapatkan detail bandara dari Fuseki
        $sparqlQuery = "
        PREFIX www: <http://www.tubes_ws_airport.org#>
        SELECT ?airport ?kodeIATA ?kodeICAO ?city ?operator ?location ?latitude ?longitude ?thumbnail ?email
        WHERE {
            ?airport a www:Airport .
            OPTIONAL { ?airport www:Kode_IATA ?kodeIATA . }
            OPTIONAL { ?airport www:Kode_ICAO ?kodeICAO . }
            OPTIONAL { ?airport www:email ?email . }
            OPTIONAL { ?airport www:city ?city . }
            OPTIONAL { ?airport www:operator ?operator . }
            OPTIONAL { ?airport www:locatedIn ?location . }
            OPTIONAL { ?airport www:latitude ?latitude . }
            OPTIONAL { ?airport www:longtitude ?longitude . }
            OPTIONAL { ?airport www:thumbnail ?thumbnail . }
            FILTER(STR(?airport) = '$uri')
        }
        LIMIT 1
    ";


        $result = $fuseki->query($sparqlQuery)->current();
        $airport = str_replace($namespace, '', $result->airport);
        $airport = str_replace('_', ' ', $airport);
        $location = str_replace($namespace, '', $result->location);
        $location = str_replace('_', ' ', $location);
    } else {
        throw new Exception("Invalid source specified.");
    }

    if (!$result) {
        throw new Exception("No details found for the provided URI.");
    }
} catch (Exception $e) {
    $error = $e->getMessage();
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $result ? htmlspecialchars($result->name) : 'Airport Details'; ?></title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>


    <!-- OpenGraph tags -->
    <?php if ($result): ?>
        <meta property="og:title" content="<?= htmlspecialchars($result->name); ?>">
        <meta property="og:type" content="website">
        <meta property="og:url" content="<?= "https://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}"; ?>">
        <?php
        if (isset($result->image)) {
            echo '<meta property="og:image" content="' . htmlspecialchars($result->image) . '">';
        }
        ?>
        <meta property="og:description" content="<?= htmlspecialchars(substr($result->abstract, 0, 200)) . '...'; ?>">
    <?php endif; ?>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.6;
            color: #333;
            background: #f5f5f5;
        }

        .container {
            max-width: 1000px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        .airport-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .airport-header {
            padding: 2rem;
            background: #2c3e50;
            color: white;
        }

        .airport-header h1 {
            margin-bottom: 0.5rem;
        }

        .airport-codes {
            font-size: 1.2rem;
            opacity: 0.9;
        }

        .airport-content {
            padding: 2rem;
        }

        .airport-image {
            width: 100%;
            max-height: 400px;
            object-fit: cover;
            margin-bottom: 2rem;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin: 2rem 0;
        }

        .info-section {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 8px;
        }

        .info-section h3 {
            color: #2c3e50;
            margin-bottom: 1rem;
            border-bottom: 2px solid #e9ecef;
            padding-bottom: 0.5rem;
        }

        .abstract {
            line-height: 1.8;
            margin: 2rem 0;
        }

        #map {
            height: 400px;
            width: 100%;
            border-radius: 8px;
            margin: 2rem 0;
        }

        .error-message {
            padding: 2rem;
            background: #fee;
            color: #c00;
            border-radius: 4px;
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="container">
        <?php if (isset($error)): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php elseif ($source === 'DBpedia'): ?>
            <div class="airport-card">
                <div class="airport-header">
                    <h1><?php echo htmlspecialchars($result->name); ?></h1>
                    <?php if (isset($result->iata) || isset($result->icao)): ?>
                        <div class="airport-codes">
                            <?php
                            if (isset($result->iata))
                                echo "IATA: " . htmlspecialchars($result->iata);
                            if (isset($result->iata) && isset($result->icao))
                                echo " | ";
                            if (isset($result->icao))
                                echo "ICAO: " . htmlspecialchars($result->icao);
                            ?>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if (isset($result->image)): ?>
                    <img src="<?= htmlspecialchars($result->image); ?>" alt="<?= htmlspecialchars($result->name); ?>"
                        class="airport-image">
                <?php else: ?>
                    <p class="no-image">No image available</p>
                <?php endif; ?>

                <div class="airport-content">
                    <div class="abstract">
                        <?php echo htmlspecialchars($result->abstract); ?>
                    </div>

                    <div class="info-grid">
                        <div class="info-section">
                            <h3>Location Information</h3>
                            <?php
                            if (isset($result->city)) {
                                $city = str_replace('http://dbpedia.org/resource/', '', $result->city);
                                $city = str_replace('_', ' ', $city);
                                echo "<p><strong>City:</strong> " . htmlspecialchars($city) . "</p>";
                            }
                            if (isset($result->country)) {
                                echo "<p><strong>Country:</strong> " . htmlspecialchars($result->country) . "</p>";
                            }
                            if (isset($result->lat) && isset($result->long)) {
                                echo "<p><strong>Coordinates:</strong> " . htmlspecialchars($result->lat) . ", " . htmlspecialchars($result->long) . "</p>";
                            }
                            ?>
                        </div>

                        <div class="info-section">
                            <h3>Airport Details</h3>
                            <?php
                            if (isset($result->elevation)) {
                                echo "<p><strong>Elevation:</strong> " . htmlspecialchars($result->elevation) . " meters</p>";
                            }
                            if (isset($result->runways)) {
                                echo "<p><strong>Number of Runways:</strong> " . htmlspecialchars($result->runways) . "</p>";
                            }
                            if (isset($result->operator)) {
                                $operator = str_replace('http://dbpedia.org/resource/', '', $result->operator);
                                $operator = str_replace('_', ' ', $operator);
                                echo "<p><strong>Operator:</strong> " . htmlspecialchars($operator) . "</p>";
                            }
                            if (isset($result->website)) {
                                echo "<p><strong>Website:</strong> <a href=\"" . htmlspecialchars($result->website) . "\" target=\"_blank\">" . htmlspecialchars($result->website) . "</a></p>";
                            }
                            ?>
                        </div>
                    </div>

                    <?php if (isset($result->lat) && isset($result->long)): ?>
                        <div id="map" style="height: 400px; width: 100%;"></div>
                        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
                        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
                        <script>
                            document.addEventListener('DOMContentLoaded', function () {
                                const latitude = <?php echo $result->lat; ?>;
                                const longitude = <?php echo $result->long; ?>;
                                const name = <?php echo json_encode(htmlspecialchars($result->name)); ?>;

                                const map = L.map('map').setView([latitude, longitude], 12);
                                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                                    attribution: 'Map data &copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a> contributors',
                                    maxZoom: 19
                                }).addTo(map);

                                L.marker([latitude, longitude]).addTo(map)
                                    .bindPopup(`<strong>${name}</strong><br>Coordinates: ${latitude}, ${longitude}`)
                                    .openPopup();
                            });
                        </script>
                    <?php endif; ?>
                </div>
            </div>
        <?php elseif ($source === 'Fuseki'): ?>
            <div class="airport-card">
                <div class="airport-header">
                    <h1><?= htmlspecialchars($airport ?? 'Unknown Airport'); ?></h1>
                    <?php if (isset($result->kodeIATA) || isset($result->kodeICAO)): ?>
                        <div class="airport-codes">
                            <?= isset($result->kodeIATA) ? "IATA: " . htmlspecialchars($result->kodeIATA) : ''; ?>
                            <?= (isset($result->kodeIATA) && isset($result->kodeICAO)) ? ' | ' : ''; ?>
                            <?= isset($result->kodeICAO) ? "ICAO: " . htmlspecialchars($result->kodeICAO) : ''; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if (isset($result->thumbnail)): ?>
                    <img src="<?= htmlspecialchars($result->thumbnail); ?>"
                        alt="<?= htmlspecialchars($airport ?? 'Unknown Airport'); ?>" class="airport-image">
                <?php else: ?>
                    <p class="no-image">No image available</p>
                <?php endif; ?>

                <div class="airport-content">
                    <div class="info-grid">
                        <div class="info-section">
                            <h3>Location Information</h3>
                            <?= isset($result->city) ? "<p><strong>City:</strong> " . htmlspecialchars($result->city) . "</p>" : ''; ?>
                            <?= isset($location) ? "<p><strong>Location:</strong> " . htmlspecialchars($location) . "</p>" : ''; ?>
                            <?php if (isset($result->latitude) && isset($result->longitude)): ?>
                                <p><strong>Coordinates:</strong> <?= htmlspecialchars($result->latitude); ?>,
                                    <?= htmlspecialchars($result->longitude); ?>
                                </p>
                            <?php endif; ?>
                        </div>

                        <div class="info-section">
                            <h3>Airport Details</h3>
                            <?= isset($result->operator) ? "<p><strong>Operator:</strong> " . htmlspecialchars($result->operator) . "</p>" : ''; ?>
                            <?= isset($result->email) ? "<p><strong>Email:</strong> " . htmlspecialchars($result->email) . "</p>" : ''; ?>
                        </div>
                    </div>
                    <?php if (isset($result->latitude) && isset($result->longitude)): ?>
                        <div id="map" style="height: 400px; width: 100%;"></div>
                        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
                        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
                        <script>
                            document.addEventListener('DOMContentLoaded', function () {
                                const latitude = <?php echo $result->latitude; ?>;
                                const longitude = <?php echo $result->longitude; ?>;
                                const name = <?php echo json_encode(htmlspecialchars($airport ?? 'Unknown Airport')); ?>;

                                const map = L.map('map').setView([latitude, longitude], 12);
                                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                                    attribution: 'Map data &copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a> contributors',
                                    maxZoom: 19
                                }).addTo(map);

                                L.marker([latitude, longitude]).addTo(map)
                                    .bindPopup(`<strong>${name}</strong><br>Coordinates: ${latitude}, ${longitude}`)
                                    .openPopup();
                            });
                        </script>
                    <?php endif; ?>

                </div>
            </div>
        <?php else: ?>
            <div class="error-message">Unknown data source.</div>
        <?php endif; ?>
    </div>

</body>

</html>