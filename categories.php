<?php
// categories.php
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Airport Search by Country</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>

<body class="bg-gray-900 text-white">

    <div class="container mx-auto px-4 py-10">
        <h1 class="text-3xl font-semibold text-center mb-8">Select a Country</h1>

        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-8">
            <!-- Category for Indonesia -->
            <div class="category-item flex flex-col items-center cursor-pointer p-4 hover:bg-gray-700 rounded-lg"
                onclick="searchByCountry('Indonesia')">
                <img src="assets/flags/indonesia.png" alt="Indonesia" class="w-20 h-20 object-cover rounded-full mb-3">
                <p class="text-lg">Indonesia</p>
            </div>

            <!-- Category for United States -->
            <div class="category-item flex flex-col items-center cursor-pointer p-4 hover:bg-gray-700 rounded-lg"
                onclick="searchByCountry('United States')">
                <img src="assets/flags/United States.png" alt="United States" class="w-20 h-20 object-cover rounded-full mb-3">
                <p class="text-lg">United States</p>
            </div>

            <!-- Category for Japan -->
            <div class="category-item flex flex-col items-center cursor-pointer p-4 hover:bg-gray-700 rounded-lg"
                onclick="searchByCountry('Japan')">
                <img src="assets/flags/japan.png" alt="Japan" class="w-20 h-20 object-cover rounded-full mb-3">
                <p class="text-lg">Japan</p>
            </div>

            <!-- Add more categories as needed -->

        </div>

        <!-- Hidden form to handle country selection -->
        <form id="searchForm" action="results.php" method="get" class="hidden">
            <input type="text" name="query" id="queryInput" hidden />
        </form>
    </div>

    <script>
        function searchByCountry(countryName) {
            // Set the value of the hidden input field to the selected country name
            document.getElementById('queryInput').value = countryName;
            // Submit the form
            document.getElementById('searchForm').submit();
        }
    </script>

</body>

</html>