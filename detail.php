<?php
// Aktifkan pelaporan error untuk debugging
error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
require 'vendor/autoload.php';

use EasyRdf\Sparql\Client;

// Ambil URI dari parameter GET
$uri = $_GET['uri'] ?? '';

if (empty($uri)) {
    die("Invalid URI.");
}

// Buat SPARQL client
$sparql = new Client('https://dbpedia.org/sparql');

try {
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

    // Query untuk mendapatkan detail bandara
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
        <?php elseif ($result): ?>
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
                    <p class="no-image">Tidak ada gambar tersedia</p>
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
                                // Koordinat lokasi
                                const latitude = <?php echo $result->lat; ?>;
                                const longitude = <?php echo $result->long; ?>;
                                const name = <?php
                                $name = str_replace('–', ' ', $result->name);
                                echo json_encode(htmlspecialchars($name)); ?>;

                                // Inisialisasi peta
                                const map = L.map('map').setView([latitude, longitude], 12);

                                // Tambahkan layer peta OpenStreetMap
                                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                                    attribution: 'Map data &copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a> contributors',
                                    maxZoom: 19
                                }).addTo(map);

                                // Tambahkan marker ke peta
                                L.marker([latitude, longitude]).addTo(map)
                                    .bindPopup(`<strong>${name}</strong><br>Coordinates: ${latitude}, ${longitude}`)
                                    .openPopup();
                            });
                        </script>
                    <?php endif; ?>

                </div>
            </div>
        <?php else: ?>
            <div class="error-message">Airport not found.</div>
        <?php endif; ?>
    </div>
</body>

</html>