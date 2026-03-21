#!/usr/bin/env node

/**
 * Dygita Theme - JavaScript Build Script
 * 使用 Terser 压缩 JavaScript 文件
 */

const fs = require('fs');
const path = require('path');
const { minify } = require('terser');

// 配置
const config = {
    sourceDir: path.join(__dirname, '../js'),
    outputDir: path.join(__dirname, '../js/dist'),
    isProduction: process.argv.includes('--production') || process.argv.includes('-p')
};

// 需要压缩的 JS 文件列表（排除 vendor 目录）
const jsFiles = [
    'main.js',
    'post-share.js',
    'reading-progress.js',
    'sidebar.js',
    'swiper-init.js',
    'theme-switcher.js',
    'archives-page.js',
    'headerCanvas.js'
];

// Terser 配置
const terserOptions = {
    compress: {
        drop_console: config.isProduction, // 生产环境移除 console
        drop_debugger: true,
        pure_funcs: config.isProduction ? ['console.log', 'console.info'] : [],
        passes: 2
    },
    mangle: config.isProduction ? {
        // 保留特定的全局变量名
        reserved: ['DYGITA']
    } : false,
    format: {
        comments: false, // 移除注释
        beautify: !config.isProduction // 开发环境保持可读性
    },
    sourceMap: !config.isProduction // 开发环境生成 source map
};

// 确保输出目录存在
if (!fs.existsSync(config.outputDir)) {
    fs.mkdirSync(config.outputDir, { recursive: true });
}

// 处理单个文件
async function processFile(filename) {
    const inputPath = path.join(config.sourceDir, filename);
    const outputPath = path.join(config.outputDir, filename);

    try {
        // 读取源文件
        const code = fs.readFileSync(inputPath, 'utf8');

        // 压缩
        const result = await minify(code, terserOptions);

        if (result.error) {
            throw result.error;
        }

        // 写入输出文件
        fs.writeFileSync(outputPath, result.code, 'utf8');

        // 如果有 source map，也写入
        if (result.map) {
            fs.writeFileSync(outputPath + '.map', result.map, 'utf8');
        }

        // 计算压缩率
        const originalSize = Buffer.byteLength(code, 'utf8');
        const minifiedSize = Buffer.byteLength(result.code, 'utf8');
        const ratio = ((1 - minifiedSize / originalSize) * 100).toFixed(2);

        console.log(`✓ ${filename}: ${formatBytes(originalSize)} → ${formatBytes(minifiedSize)} (${ratio}% smaller)`);
    } catch (error) {
        console.error(`✗ Error processing ${filename}:`, error.message);
        process.exit(1);
    }
}

// 格式化字节大小
function formatBytes(bytes) {
    if (bytes < 1024) return bytes + ' B';
    if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(2) + ' KB';
    return (bytes / (1024 * 1024)).toFixed(2) + ' MB';
}

// 主函数
async function build() {
    console.log('🚀 Building JavaScript files...');
    console.log(`Mode: ${config.isProduction ? 'PRODUCTION' : 'DEVELOPMENT'}\n`);

    const startTime = Date.now();

    // 并行处理所有文件
    await Promise.all(jsFiles.map(processFile));

    const duration = ((Date.now() - startTime) / 1000).toFixed(2);
    console.log(`\n✨ Build completed in ${duration}s`);
    console.log(`📦 Output: ${config.outputDir}`);
}

// 执行构建
build().catch(error => {
    console.error('Build failed:', error);
    process.exit(1);
});
