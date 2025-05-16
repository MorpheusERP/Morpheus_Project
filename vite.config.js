import { defineConfig } from 'vite'; import laravel from 
'laravel-vite-plugin'; import path from 'path'; import fs 
from 'fs';
// Função para buscar todos os arquivos .css dentro de 
// resources/css
function getAllCssFiles(dirPath, arrayOfFiles = []) { const 
    files = fs.readdirSync(dirPath); files.forEach((file) => 
    {
        const fullPath = path.join(dirPath, file); if 
        (fs.statSync(fullPath).isDirectory()) {
            getAllCssFiles(fullPath, arrayOfFiles);
        } else if (file.endsWith('.css')) {
            arrayOfFiles.push(fullPath.replace(/\\/g, '/')); 
            // compatível com Linux/Unix
        }
    });
    return arrayOfFiles;
}
const cssFiles = getAllCssFiles('resources/css'); export 
default defineConfig({
    plugins: [ laravel({ input: [ 'resources/js/app.js', 
                ...cssFiles,
            ], refresh: true,
        }),
    ], resolve: { alias: { css: path.resolve(__dirname, 
            'resources/css'),
        },
    },
});
