<?php
// categories.php
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Airport Search by Country</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="icon" type="image/x-icon" href="assets/img/logo.png">
</head>
<style>
    body::-webkit-scrollbar-track
{
	-webkit-box-shadow: inset 0 0 6px rgba(0,0,0,0.3);
	border-radius: 10px;
	background-color: transparent;
}

body::-webkit-scrollbar
{
	width: 12px;
	background-color: transparent;
}

body::-webkit-scrollbar-thumb
{
	border-radius: 10px;
	-webkit-box-shadow: inset 0 0 6px rgba(0,0,0,.3);
	background-color: rgb(31 41 55);
}
</style>

<body class="bg-gray-900 text-white">
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
    <div class="container mx-auto px-4 py-32">
        <h1 class="text-3xl font-semibold text-center mb-16">Select a Country</h1>

        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-8">
            <!-- Category for Indonesia -->
            <div class="category-item flex flex-col items-center cursor-pointer p-4 hover:bg-gray-700 rounded-lg"
                onclick="searchByCountry('Indonesia')">
                <img src="https://flagcdn.com/w320/id.png" alt="Indonesia"
                    class="w-20 h-20 object-cover rounded-full mb-3">
                <p class="text-lg">Indonesia</p>
            </div>

            <!-- Category for United States -->
            <div class="category-item flex flex-col items-center cursor-pointer p-4 hover:bg-gray-700 rounded-lg"
                onclick="searchByCountry('United States')">
                <img src="https://flagcdn.com/w320/us.png" alt="United States"
                    class="w-20 h-20 object-cover rounded-full mb-3">
                <p class="text-lg">United States</p>
            </div>

            <!-- Category for Japan -->
            <div class="category-item flex flex-col items-center cursor-pointer p-4 hover:bg-gray-700 rounded-lg"
                onclick="searchByCountry('Japan')">
                <img src="https://flagcdn.com/w320/jp.png" alt="Japan" class="w-20 h-20 object-cover rounded-full mb-3">
                <p class="text-lg">Japan</p>
            </div>

            <!-- Category for Germany -->
            <div class="category-item flex flex-col items-center cursor-pointer p-4 hover:bg-gray-700 rounded-lg"
                onclick="searchByCountry('Germany')">
                <img src="https://flagcdn.com/w320/de.png" alt="Germany"
                    class="w-20 h-20 object-cover rounded-full mb-3">
                <p class="text-lg">Germany</p>
            </div>

            <!-- Category for France -->
            <div class="category-item flex flex-col items-center cursor-pointer p-4 hover:bg-gray-700 rounded-lg"
                onclick="searchByCountry('France')">
                <img src="https://flagcdn.com/w320/fr.png" alt="France"
                    class="w-20 h-20 object-cover rounded-full mb-3">
                <p class="text-lg">France</p>
            </div>

            <!-- Category for Canada -->
            <div class="category-item flex flex-col items-center cursor-pointer p-4 hover:bg-gray-700 rounded-lg"
                onclick="searchByCountry('Canada')">
                <img src="https://flagcdn.com/w320/ca.png" alt="Canada"
                    class="w-20 h-20 object-cover rounded-full mb-3">
                <p class="text-lg">Canada</p>
            </div>

            <!-- Category for Brazil -->
            <div class="category-item flex flex-col items-center cursor-pointer p-4 hover:bg-gray-700 rounded-lg"
                onclick="searchByCountry('Brazil')">
                <img src="https://flagcdn.com/w320/br.png" alt="Brazil"
                    class="w-20 h-20 object-cover rounded-full mb-3">
                <p class="text-lg">Brazil</p>
            </div>

            <!-- Category for Australia -->
            <div class="category-item flex flex-col items-center cursor-pointer p-4 hover:bg-gray-700 rounded-lg"
                onclick="searchByCountry('Australia')">
                <img src="https://flagcdn.com/w320/au.png" alt="Australia"
                    class="w-20 h-20 object-cover rounded-full mb-3">
                <p class="text-lg">Australia</p>
            </div>

            <!-- Category for Mexico -->
            <div class="category-item flex flex-col items-center cursor-pointer p-4 hover:bg-gray-700 rounded-lg"
                onclick="searchByCountry('Mexico')">
                <img src="https://flagcdn.com/w320/mx.png" alt="Mexico"
                    class="w-20 h-20 object-cover rounded-full mb-3">
                <p class="text-lg">Mexico</p>
            </div>

            <!-- Category for South Korea -->
            <div class="category-item flex flex-col items-center cursor-pointer p-4 hover:bg-gray-700 rounded-lg"
                onclick="searchByCountry('South Korea')">
                <img src="https://flagcdn.com/w320/kr.png" alt="South Korea"
                    class="w-20 h-20 object-cover rounded-full mb-3">
                <p class="text-lg">South Korea</p>
            </div>


            <!-- Category for Italy -->
            <div class="category-item flex flex-col items-center cursor-pointer p-4 hover:bg-gray-700 rounded-lg"
                onclick="searchByCountry('Italy')">
                <img src="https://flagcdn.com/w320/it.png" alt="Italy" class="w-20 h-20 object-cover rounded-full mb-3">
                <p class="text-lg">Italy</p>
            </div>

            <!-- Category for Spain -->
            <div class="category-item flex flex-col items-center cursor-pointer p-4 hover:bg-gray-700 rounded-lg"
                onclick="searchByCountry('Spain')">
                <img src="https://flagcdn.com/w320/es.png" alt="Spain" class="w-20 h-20 object-cover rounded-full mb-3">
                <p class="text-lg">Spain</p>
            </div>

            <!-- Category for India -->
            <div class="category-item flex flex-col items-center cursor-pointer p-4 hover:bg-gray-700 rounded-lg"
                onclick="searchByCountry('India')">
                <img src="https://flagcdn.com/w320/in.png" alt="India" class="w-20 h-20 object-cover rounded-full mb-3">
                <p class="text-lg">India</p>
            </div>

            <!-- Category for Russia -->
            <div class="category-item flex flex-col items-center cursor-pointer p-4 hover:bg-gray-700 rounded-lg"
                onclick="searchByCountry('Russia')">
                <img src="https://flagcdn.com/w320/ru.png" alt="Russia"
                    class="w-20 h-20 object-cover rounded-full mb-3">
                <p class="text-lg">Russia</p>
            </div>


            <!-- Category for Argentina -->
            <div class="category-item flex flex-col items-center cursor-pointer p-4 hover:bg-gray-700 rounded-lg"
                onclick="searchByCountry('Argentina')">
                <img src="https://flagcdn.com/w320/ar.png" alt="Argentina"
                    class="w-20 h-20 object-cover rounded-full mb-3">
                <p class="text-lg">Argentina</p>
            </div>

            <!-- Category for Saudi Arabia -->
            <div class="category-item flex flex-col items-center cursor-pointer p-4 hover:bg-gray-700 rounded-lg"
                onclick="searchByCountry('Saudi Arabia')">
                <img src="https://flagcdn.com/w320/sa.png" alt="Saudi Arabia"
                    class="w-20 h-20 object-cover rounded-full mb-3">
                <p class="text-lg">Saudi Arabia</p>
            </div>

            <!-- Category for Egypt -->
            <div class="category-item flex flex-col items-center cursor-pointer p-4 hover:bg-gray-700 rounded-lg"
                onclick="searchByCountry('Egypt')">
                <img src="https://flagcdn.com/w320/eg.png" alt="Egypt" class="w-20 h-20 object-cover rounded-full mb-3">
                <p class="text-lg">Egypt</p>
            </div>

            <!-- Category for South Africa -->
            <div class="category-item flex flex-col items-center cursor-pointer p-4 hover:bg-gray-700 rounded-lg"
                onclick="searchByCountry('South Africa')">
                <img src="https://flagcdn.com/w320/za.png" alt="South Africa"
                    class="w-20 h-20 object-cover rounded-full mb-3">
                <p class="text-lg">South Africa</p>
            </div>

            <!-- Category for Nigeria -->
            <div class="category-item flex flex-col items-center cursor-pointer p-4 hover:bg-gray-700 rounded-lg"
                onclick="searchByCountry('Nigeria')">
                <img src="https://flagcdn.com/w320/ng.png" alt="Nigeria"
                    class="w-20 h-20 object-cover rounded-full mb-3">
                <p class="text-lg">Nigeria</p>
            </div>

            <!-- Category for Sweden -->
            <div class="category-item flex flex-col items-center cursor-pointer p-4 hover:bg-gray-700 rounded-lg"
                onclick="searchByCountry('Sweden')">
                <img src="https://flagcdn.com/w320/se.png" alt="Sweden"
                    class="w-20 h-20 object-cover rounded-full mb-3">
                <p class="text-lg">Sweden</p>
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