<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'Young 911 Autowerks') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex items-center justify-center">
        <div class="bg-white p-8 rounded-lg shadow-md max-w-md w-full">
            <h1 class="text-3xl font-bold text-center text-gray-800 mb-4">
                Young 911 Autowerks
            </h1>
            <p class="text-center text-gray-600 mb-6">
                Professional Porsche Service & Repair
            </p>
            <div class="space-y-4">
                <a href="/admin" class="block w-full bg-blue-600 text-white text-center py-3 rounded-md hover:bg-blue-700 transition">
                    Admin Dashboard
                </a>
                <a href="/bookings/create" class="block w-full bg-green-600 text-white text-center py-3 rounded-md hover:bg-green-700 transition">
                    Book a Service
                </a>
            </div>
            <p class="text-center text-sm text-gray-500 mt-6">
                &copy; {{ date('Y') }} Young 911 Autowerks. All rights reserved.
            </p>
        </div>
    </div>
</body>
</html>
