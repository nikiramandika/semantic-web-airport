<?php
// index.php
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Airport Search</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="css/output.css">
    <link rel="stylesheet" href="css/output.css">
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body>
    <div class="video-container">
        <div class="video-wrapper active" id="video1-wrapper">
            <video id="video1" muted playsinline>
                <source src="assets/img/awan.mp4" type="video/mp4">
            </video>
        </div>
        <div class="video-wrapper" id="video2-wrapper">
            <video id="video2" muted playsinline>
                <source src="assets/img/awan.mp4" type="video/mp4">
            </video>
        </div>
    </div>
    <nav class="fixed top-0 left-0 w-full z-10 bg-transparent">
  <div class="mx-auto max-w-7xl px-2 sm:px-6 lg:px-8 py-2">
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


    <div class="content">
        
        <!-- Area teks yang bisa digunakan untuk tampilan dinamis -->
        <div class="typing-text text-gray-100 h-28" id="typing-text">
            <!-- Tambahkan teks dinamis di sini jika diperlukan -->
        </div>

        <!-- Kotak pencarian -->
        <!-- <div class="search-box" id="search-box">
            <form action="results.php" method="get" onsubmit="return validateInput()">
                <input type="text" name="query" id="queryInput" class="search-input"
                    placeholder="Enter airport name or code..." required minlength="3">
                <p id="errorMsg" style="color: red; font-size: 14px; display: none; margin-top: 5px;">
                    Please enter at least 3 characters.
                </p>
                <button type="submit" hidden class="search-button">cari</button>
            </form>
        </div> -->
        <div class="w-full bg-transparent">
        <form action="results.php" method="get" onsubmit="return validateInput()">
        <div class="relative w-7/12 mx-auto bg-transparent">
        
        <input type="search" id="default-search" class="bg-blue-800/[0.1] backdrop-blur-md block w-full py-5 ps-8 pr-16 text-2xl text-gray-100 border border-slate-400/20 ring-1 ring-blue-800/5 rounded-full focus:outline-none placeholder:text-gray-300 focus:bg-blue-800/[0.1] focus:text-gray-100 autofill:text-gray-100 active:bg-blue-800/[0.1]" placeholder="Search airports, by city, country, code" required name="query" id="queryInput" autocomplete="off"/>
        <!-- <div class="absolute inset-y-0 start-0 flex items-center ps-6 pointer-events-none">
            <svg class="w-6 h-6 text-gray-100" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z"/>
            </svg>
        </div> -->
        <button type="submit" class="text-white absolute end-2.5 bottom-4 focus:outline-none focus:ring-blue-300 font-medium rounded-full text-xl px-5 py-2  my-auto"><svg class="w-6 h-6 text-gray-100" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z"/>
            </svg> </button>
        </div>


        </form>

        </div>
       
    </div>

    <!-- Script untuk validasi input -->
    <script>
        function validateInput() {
            const input = document.getElementById("queryInput"); // Ambil input
            const errorMsg = document.getElementById("errorMsg"); // Ambil pesan error

            // Validasi panjang input
            if (input.value.length < 3) {
                errorMsg.style.display = "block"; // Tampilkan pesan error
                return false; // Hentikan pengiriman form
            } else {
                errorMsg.style.display = "none"; // Sembunyikan pesan error
                return true; // Lanjutkan pengiriman form
            }
        }
    </script>



    <script>
        const phrases = [
            "Find your airport",
            "Search for your destination",
            "Find your airport",
            "Where would you like to go?",
            "Discover airports worldwide"
        ];

        let currentPhraseIndex = 0;
        let currentCharIndex = 0;
        let isDeleting = false;
        const typingText = document.getElementById('typing-text');
        const searchBox = document.getElementById('search-box');

        function typeText() {
            const currentPhrase = phrases[currentPhraseIndex];

            if (isDeleting) {
                typingText.textContent = currentPhrase.substring(0, currentCharIndex - 1);
                currentCharIndex--;
            } else {
                typingText.textContent = currentPhrase.substring(0, currentCharIndex + 1);
                currentCharIndex++;
            }

            let typingSpeed = isDeleting ? 50 : 100;

            if (!isDeleting && currentCharIndex === currentPhrase.length) {
                setTimeout(() => {
                    isDeleting = true;
                }, 2000);
                return setTimeout(typeText, typingSpeed);
            }

            if (isDeleting && currentCharIndex === 0) {
                isDeleting = false;
                currentPhraseIndex = (currentPhraseIndex + 1) % phrases.length;
                return setTimeout(typeText, 500);
            }

            setTimeout(typeText, typingSpeed);
        }

        // Video switching code
        const video1 = document.getElementById('video1');
        const video2 = document.getElementById('video2');
        const wrapper1 = document.getElementById('video1-wrapper');
        const wrapper2 = document.getElementById('video2-wrapper');
        let currentVideo = 1;

        function switchVideos() {
            if (currentVideo === 1) {
                wrapper1.classList.remove('active');
                wrapper2.classList.add('active');
                video2.play();
                currentVideo = 2;
            } else {
                wrapper2.classList.remove('active');
                wrapper1.classList.add('active');
                video1.play();
                currentVideo = 1;
            }
        }

        typeText();
        setTimeout(() => {
            searchBox.classList.add('visible');
        }, 1000);



        video2.load(); // Preload video2, no need to modify PHP logic

// Event listeners for videos to trigger the switch when one ends
video1.addEventListener('ended', switchVideos);
video2.addEventListener('ended', switchVideos);

// Start video1 when the page loads
video1.play();
    </script>
</body>

</html>