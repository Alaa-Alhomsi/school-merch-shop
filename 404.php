<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Seite nicht gefunden</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    animation: {
                        'fade-in-down': 'fadeIn 1s ease-in'
                    },
                    keyframes: {
                        fadeIn: {
                            '0%': { opacity: '0', transform: 'translateY(-20px)' },
                            '100%': { opacity: '1', transform: 'translateY(0)' }
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="min-h-screen flex items-center justify-center bg-gradient-to-br from-gray-100 to-gray-300 p-4">
    <div class="text-center animate-fade-in-down">
        <h1 class="text-9xl font-bold bg-gradient-to-r from-purple-600 to-purple-400 bg-clip-text text-transparent">404</h1>
        
        <div class="max-w-lg mx-auto my-8">
            <svg viewBox="0 0 200 200" class="w-64 h-64 mx-auto">
                <circle cx="100" cy="100" r="90" class="fill-purple-600 opacity-10"/>
                <text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" class="text-2xl fill-gray-700">
                    Oh oh!
                </text>
                <g transform="translate(70,80)">
                    <circle cx="20" cy="20" r="15" class="fill-purple-600"/>
                    <circle cx="40" cy="20" r="15" class="fill-purple-600"/>
                    <path d="M 15 40 Q 30 30 45 40" class="stroke-purple-600 stroke-2 fill-none"/>
                </g>
            </svg>
        </div>

        <p class="text-2xl text-gray-700 mb-4">Ups! Diese Seite wurde leider nicht gefunden.</p>
        <p class="text-gray-600 mb-8">Die gesuchte Seite existiert nicht oder wurde möglicherweise verschoben.</p>
        
        <a href="/" class="inline-block px-8 py-3 bg-gradient-to-r from-purple-600 to-purple-400 text-white font-bold rounded-full transition-transform hover:scale-105">
            Zurück zur Startseite
        </a>
    </div>
</body>
</html> 