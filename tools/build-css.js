#!/usr/bin/env node

/**
 * Dygita Theme CSS Build Script
 *
 * Usage (from theme root): node tools/build-css.js
 * Usage (production): node tools/build-css.js --production
 *
 * Merges CSS source files into a single build.css with PostCSS processing.
 * style.css (the main Typecho theme entry) is NOT included here
 * because it is loaded separately by Typecho's theme system.
 */

const fs = require('fs');
const path = require('path');
const { execSync } = require('child_process');

const cssFiles = [
    'css/base/variables.css',
    'css/base/skin.css',
    'css/base/reset.css',
    'css/base/typography.css',
    'css/layout/header.css',
    'css/layout/sidebar-left.css',
    'css/layout/main-content.css',
    'css/layout/sidebar-right.css',
    'css/layout/footer.css',
    'css/components/buttons.css',
    'css/components/article.css',
    'css/components/tag-cloud.css',
    'css/components/toc.css',
    'css/components/pagination.css',
    'css/components/toast.css',
    'css/themes/dark-mode.css'
];

const outputFile = 'css/build.css';
const themeDir = path.join(__dirname, '..');
const isProduction = process.argv.includes('--production');

console.log('Building CSS' + (isProduction ? ' (production)' : '') + '...');

// Check if PostCSS dependencies are available
function checkDependencies() {
    try {
        require('postcss');
        require('autoprefixer');
        require('cssnano');
        return true;
    } catch (e) {
        console.log('\n! PostCSS dependencies not found.');
        console.log('Please run: npm install');
        return false;
    }
}

// Build combined CSS
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
const tempPath = path.join(themeDir, 'css/build-temp.css');

// Write temporary file
fs.writeFileSync(tempPath, combinedCSS);

// Process with PostCSS if dependencies are available
if (isProduction && checkDependencies()) {
    try {
        console.log('\nProcessing with PostCSS (Autoprefixer + Minification)...');
        
        // Use postcss-cli to process the file
        const postcssCmd = `npx postcss "${tempPath}" --config "${path.join(themeDir, 'postcss.config.js')}" --output "${outputPath}"`;
        execSync(postcssCmd, { stdio: 'inherit', cwd: themeDir });
        
        // Clean up temp file
        fs.unlinkSync(tempPath);
        
        const sizeKB = (fs.statSync(outputPath).size / 1024).toFixed(2);
        const originalSizeKB = (combinedCSS.length / 1024).toFixed(2);
        const compressionRatio = ((1 - fs.statSync(outputPath).size / combinedCSS.length) * 100).toFixed(1);
        
        console.log('\n✅ Production build complete!');
        console.log('   Output: ' + outputFile + ' (' + sizeKB + ' KB)');
        console.log('   Original: ' + originalSizeKB + ' KB');
        console.log('   Compression: ' + compressionRatio + '%');
        console.log('   ✓ Autoprefixer applied');
        console.log('   ✓ Minified with cssnano');
        
    } catch (error) {
        console.error('\n❌ PostCSS processing failed:', error.message);
        console.log('Falling back to unprocessed CSS...');
        
        // Fallback: copy temp file to output
        fs.copyFileSync(tempPath, outputPath);
        fs.unlinkSync(tempPath);
        
        const sizeKB = (fs.statSync(outputPath).size / 1024).toFixed(2);
        console.log('\nFallback complete: ' + outputFile + ' (' + sizeKB + ' KB)');
    }
} else {
    // Development build or no dependencies - just copy
    fs.copyFileSync(tempPath, outputPath);
    fs.unlinkSync(tempPath);
    
    const sizeKB = (fs.statSync(outputPath).size / 1024).toFixed(2);
    console.log('\n✅ Development build complete: ' + outputFile + ' (' + sizeKB + ' KB)');
    
    if (!isProduction) {
        console.log('Tip: Use --production flag for optimized build with Autoprefixer and minification');
    } else {
        console.log('Tip: Install dependencies with "npm install" for production optimizations');
    }
}
