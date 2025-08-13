<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visitor Management System</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.1/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.1/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="styles.css">
</head>
<body class="min-h-screen gradient-bg p-4">
<div class="container mx-auto w-full max-w-full">
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-xl sm:text-2xl font-bold text-white"><i class="fas fa-building mr-2"></i>Visitor Management System</h1>
        <div class="flex items-center space-x-2">
            <select id="role" class="p-2 border border-indigo-300 rounded-lg bg-white text-indigo-600 focus:ring-2 focus:ring-indigo-600 text-base">
                <option value="admin">Admin</option>
                <option value="gatekeeper">Gatekeeper</option>
                <option value="host">Host</option>
            </select>
            <button id="hamburger" class="hamburger text-white text-2xl sm:hidden">☰</button>
        </div>
    </div>
    <div id="nav-menu" class="nav-menu hidden sm:flex flex-wrap justify-center space-x-2 sm:space-x-4 mb-4"></div>
    <div id="content" class="w-full max-w-full"></div>
</div>

<!-- Modals -->
<div id="visitorModal" class="fixed inset-0 bg-gray-800 bg-opacity-50 flex items-center justify-center hidden transition-opacity duration-300">
    <div class="bg-white p-6 rounded-xl shadow-custom w-full max-w-md transform transition-transform duration-300 scale-95">
        <h2 id="modalTitle" class="text-lg font-semibold mb-4 text-indigo-600"><i class="fas fa-user-plus mr-2"></i><span id="modalTitleText"></span></h2>
        <form id="visitorForm" class="space-y-3">
            <input type="hidden" id="visitorId">
            <input type="text" id="name" placeholder="Name" class="w-full p-2 border border-indigo-300 rounded-lg focus:ring-2 focus:ring-indigo-600 text-base" required>
            <input type="text" id="vehicleInfo" placeholder="Vehicle Info" class="w-full p-2 border border-indigo-300 rounded-lg focus:ring-2 focus:ring-indigo-600 text-base">
            <input type="number" id="accompanyingPersons" placeholder="No. of Accompanying Persons" class="w-full p-2 border border-indigo-300 rounded-lg focus:ring-2 focus:ring-indigo-600 text-base" min="0">
            <input type="text" id="idCard" placeholder="ID Card #" class="w-full p-2 border border-indigo-300 rounded-lg focus:ring-2 focus:ring-indigo-600 text-base" required>
            <input type="tel" id="phone" placeholder="Phone" class="w-full p-2 border border-indigo-300 rounded-lg focus:ring-2 focus:ring-indigo-600 text-base" required>
            <select id="host" class="w-full p-2 border border-indigo-300 rounded-lg focus:ring-2 focus:ring-indigo-600 text-base" required>
                <option value="">Select Host</option>
            </select>
            <input type="text" id="hostSearch" placeholder="Search Host" class="w-full p-2 border border-indigo-300 rounded-lg focus:ring-2 focus:ring-indigo-600 text-base">
            <input type="date" id="stayStart" class="w-full p-2 border border-indigo-300 rounded-lg focus:ring-2 focus:ring-indigo-600 text-base" required>
            <input type="date" id="stayEnd" class="w-full p-2 border border-indigo-300 rounded-lg focus:ring-2 focus:ring-indigo-600 text-base" required>
            <input type="text" id="reason" placeholder="Reason for Visit" class="w-full p-2 border border-indigo-300 rounded-lg focus:ring-2 focus:ring-indigo-600 text-base" required>
            <div class="flex justify-end space-x-2">
                <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 hover:scale-105 transition-transform text-base"><i class="fas fa-save mr-2"></i>Submit</button>
                <button type="button" id="closeModal" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 hover:scale-105 transition-transform text-base"><i class="fas fa-times mr-2"></i>Close</button>
            </div>
        </form>
    </div>
</div>
<div id="confirmModal" class="fixed inset-0 bg-gray-800 bg-opacity-50 flex items-center justify-center hidden transition-opacity duration-300">
    <div class="bg-white p-6 rounded-xl shadow-custom w-full max-w-md transform transition-transform duration-300 scale-95">
        <h2 class="text-lg font-semibold mb-4 text-indigo-600"><i class="fas fa-question-circle mr-2"></i>Confirm Action</h2>
        <p id="confirmMessage" class="text-base mb-4"></p>
        <div class="flex justify-end space-x-2">
            <button id="confirmAction" class="bg-emerald-600 text-white px-4 py-2 rounded-lg hover:bg-emerald-700 hover:scale-105 transition-transform text-base"><i class="fas fa-check mr-2"></i>Confirm</button>
            <button id="cancelAction" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 hover:scale-105 transition-transform text-base"><i class="fas fa-times mr-2"></i>Cancel</button>
        </div>
    </div>
</div>
<script src="data.js"></script>
<script src="app.js"></script>
</body>
</html>