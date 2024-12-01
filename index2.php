<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My PHP Project</title>
    <link rel="stylesheet" href="css/output.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body class=" ">
    


<div class="flex items-center justify-center h-screen w-full">
    <div class="w-full">
        <div class="judul mb-8">
        <h1 class="text-5xl text-center">AirSearch</h1>
        </div>
    
    <form class="max-w-xl mx-auto w-full">   
    <label for="default-search" class="mb-2 text-sm font-medium text-gray-900 sr-only dark:text-white">Search</label>
    <div class="relative w-full">
        <div class="absolute inset-y-0 start-0 flex items-center ps-6 pointer-events-none">
            <svg class="w-6 h-6 text-gray-500 dark:text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z"/>
            </svg>
        </div>
        <input type="search" id="default-search" class="bg-sky-100/[.02] block w-full py-5 ps-16 pr-32 text-2xl text-gray-900 border  border-gray-300 border-opacity-60 rounded-full bg-gray-50 focus:outline-none" placeholder="Search airports, by city, country" required />
        <button type="submit" class="text-white absolute end-2.5 bottom-4 bg-blue-700/[0.6] hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-full text-xl px-5 py-1.5  my-auto">Search</button>
    </div>
</form>
    </div>


</div>


</body>
</html>
