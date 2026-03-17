#!/usr/bin/env node

/**
 * Dygita Theme CSS Build Script
 *
 * Usage (from theme root): node js/build-css.js
 *
 * Merges CSS source files into a single build.css.
 * style.css (the main Typecho theme entry) is NOT included here
 * because it is loaded separately by Typecho's theme system.
 */

const fs = require('fs');
const path = require('path');

const cssFiles = [
    'css/variables.css',
    'css/base.css',
    'css/layout.css',
    'css/components.css',
    'css/custom.css',
    'css/inline.css'
];

const outputFile = 'css/build.css';
const themeDir = path.join(__dirname, '..');

console.log('Building CSS...');

let combinedCSS = '/* Dygita Theme - Build: ' + new Date().toISOString() + ' */\n\n';

cssFiles.forEach(filePath => {
    const fullPath = path.join(themeDir, filePath);

    if (fs.existsSync(fullPath)) {
        const content = fs.readFileSync(fullPath, 'utf8');
        combinedCSS += '/* === ' + filePath + ' === */\n';
        combinedCSS += content + '\n\n';
        console.log('  + ' + filePath);
    } else {
        console.log('  ! Missing: ' + filePath);
    }
});

const outputPath = path.join(themeDir, outputFile);
fs.writeFileSync(outputPath, combinedCSS);

const sizeKB = (fs.statSync(outputPath).size / 1024).toFixed(2);
console.log('\nDone: ' + outputFile + ' (' + sizeKB + ' KB)');
console.log('Tip: For production, run through cssnano or clean-css for minification.');
