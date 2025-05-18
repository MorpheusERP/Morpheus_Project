import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import path from 'path';
import fs from 'fs';

// Função para buscar todos os arquivos .css dentro de resources/css
function getAllCssFiles(dirPath, arrayOfFiles = []) {
    const files = fs.readdirSync(dirPath);

    files.forEach((file) => {
        const fullPath = path.join(dirPath, file);
        if (fs.statSync(fullPath).isDirectory()) {
            getAllCssFiles(fullPath, arrayOfFiles);
        } else if (file.endsWith('.css')) {
            arrayOfFiles.push(fullPath.replace(/\\/g, '/'));
        }
    });

    return arrayOfFiles;
}
const cssFiles = getAllCssFiles('resources/css');

//Função para buscar todos os arquivos .js dentro da pasta resources/js
function getAlljsFiles(dirPath, arrayOfFiles = []) {
    const files = fs.readdirSync(dirPath);

    files.forEach((file) => {
        const fullPath = path.join(dirPath, file);
        if (fs.statSync(fullPath).isDirectory()){
            getAlljsFiles(fullPath, arrayOfFiles);
        } else if (file.endsWith('.js')) {
            arrayOfFiles.push(fullPath.replace(/\\/g, '/'));
        }
    });

    return arrayOfFiles;
}
const jsFiles = getAlljsFiles('resources/js');

export default defineConfig({
    /*base: '',
    
    server: {
        host: true,
        strictPort: true,
        port: 5173,
        allowedHosts: ['.ngrok-free.app'], 
    },*/
    
    plugins: [
        laravel({
            input: [
                ...jsFiles,
                ...cssFiles,
            ],
            refresh: true,
        }),
    ],
    resolve: {
        alias: {
            css: path.resolve(__dirname, 'resources/css'),
        },
    },
});
