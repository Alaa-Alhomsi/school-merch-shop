/** @type {import('tailwindcss').Config} */
module.exports = { 
  content: [
    './**/*.php', // Nimmt alle PHP-Dateien im aktuellen Verzeichnis und Unterverzeichnissen
    './css/**/*.css', // Wenn Sie auch CSS-Dateien haben
  ],
  theme: {
    extend: {},
  },
  plugins: [],
}

