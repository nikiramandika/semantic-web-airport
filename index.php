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
</head>

<body>
    <div class="video-container">
        <div class="video-wrapper active" id="video1-wrapper">
            <video id="video1" muted playsinline>
                <source src="assets/plane2.mp4" type="video/mp4">
            </video>
            <div class="overlay"></div>
        </div>
        <div class="video-wrapper" id="video2-wrapper">
            <video id="video2" muted playsinline>
                <source src="assets/plane1.mp4" type="video/mp4">
            </video>
            <div class="overlay"></div>
        </div>
    </div>

    <div class="content">
        <!-- Area teks yang bisa digunakan untuk tampilan dinamis -->
        <div class="typing-text" id="typing-text">
            <!-- Tambahkan teks dinamis di sini jika diperlukan -->
        </div>

        <!-- Kotak pencarian -->
        <div class="search-box" id="search-box">
            <form action="results.php" method="get" onsubmit="return validateInput()">
                <input type="text" name="query" id="queryInput" class="search-input"
                    placeholder="Enter airport name or code..." required minlength="3">
                <p id="errorMsg" style="color: red; font-size: 14px; display: none; margin-top: 5px;">
                    Please enter at least 3 characters.
                </p>
                <button type="submit" hidden class="search-button">cari</button>
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
            "What are you looking for?",
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

        // Event listeners for videos
        video1.addEventListener('ended', switchVideos);
        video2.addEventListener('ended', switchVideos);

        // Start everything
        video1.play();
        typeText();
        setTimeout(() => {
            searchBox.classList.add('visible');
        }, 1000);

        // Preload second video
        video2.load();
    </script>
</body>

</html>