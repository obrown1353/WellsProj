
<!DOCTYPE html>
<html>
    <head>
	<script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="css/login.css">
    	<link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300;400;500;700&display=swap" rel="stylesheet">
	<style>

* { font-family: StromaBold, 'Lucida Sans'; }
	</style>
        <title>Seacobeck Library | Check Out</title>
    </head>
   
<body class="min-h-screen flex flex-col bg-cover bg-center relative"
      style="background-image: url('images/library.jpg');">

  <!-- Blue Overlay -->
  <div class="absolute inset-0 bg-[#1e12c9d5]"></div>

  <!-- Main Content (Centered) -->
  <!-- Main Content Wrapper -->
<div class="flex-grow flex items-center justify-center relative z-10">

  <div class="w-full sm:w-2/3 sm:max-w-md px-6 py-8 flex flex-col items-center text-white bg-white/10 backdrop-blur-md rounded-xl shadow-xl">

    <h2 class="text-3xl font-bold mb-6 text-center"
        style="text-shadow: 1px 1px 0 black, -1px -1px 0 black, 1px -1px 0 black, -1px 1px 0 black; color: #bfe5ed;">
      Found what you need? Check it out!
    </h2>

    <form action="/action_page.php" method="POST" class="w-full space-y-5">

      <input 
        type="text" 
        id="name" 
        name="name"
        placeholder="Name: First, Last"
        class="w-full bg-white/90 text-black border border-gray-300 rounded-md px-4 py-3 focus:outline-none focus:ring-2 focus:ring-[#9C2007] placeholder-gray-500"
        required
      >

      <input 
        type="text" 
        id="materialName" 
        name="materialName"
        placeholder="Name of Item"
        class="w-full bg-white/90 text-black border border-gray-300 rounded-md px-4 py-3 focus:outline-none focus:ring-2 focus:ring-[#9C2007] placeholder-gray-500"
        required
      >

      <input 
        type="email" 
        id="email" 
        name="email"
        placeholder="Email"
        class="w-full bg-white/90 text-black border border-gray-300 rounded-md px-4 py-3 focus:outline-none focus:ring-2 focus:ring-[#9C2007] placeholder-gray-500"
        required
      >

      <button 
        type="submit"
        class="w-full bg-[#9C2007] text-white font-bold py-3 rounded-lg hover:bg-[#7a1905] active:scale-95 transition duration-300">
        Check Out
      </button>

    </form>

  </div>
</div>
  <!-- Footer -->
  <footer class="relative z-10 w-full text-center text-white bg-black bg-opacity-50 py-4 mt-4">
    Questions? Contact Dr. Mellisa Wells <a href="mailto:mwells@umw.edu" class="underline hover:text-blue-400">mwells@umw.edu</a>
  </footer>

</body>

  
</html>
