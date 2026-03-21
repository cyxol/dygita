# Dygita 主题构建指南

本主题使用现代化的构建工具链来确保代码质量和性能优化。

## 安装依赖

```bash
npm install
```

## 可用命令

### 代码检查

```bash
# 检查 JavaScript 代码
npm run lint:js

# 检查 CSS 代码
npm run lint:css

# 检查所有代码
npm run lint

# 自动修复代码问题
npm run lint:fix
```

### 构建

```bash
# 构建 CSS（开发模式）
npm run build-css

# 构建 CSS（生产模式，压缩）
npm run build-css:prod

# 构建 JavaScript（开发模式）
npm run build-js

# 构建 JavaScript（生产模式，压缩）
npm run build-js:prod

# 完整构建（检查 + 压缩 CSS + 压缩 JS）
npm run build
```

## 构建工具说明

### ESLint
- 用于 JavaScript 代码静态检查
- 配置文件：`.eslintrc.json`
- 检查代码风格、潜在错误和最佳实践

### Stylelint
- 用于 CSS 代码静态检查
- 配置文件：`.stylelintrc.json`
- 确保 CSS 代码符合规范

### Terser
- 用于 JavaScript 代码压缩
- 生产环境自动移除 console.log
- 变量名混淆（保留 DYGITA 全局变量）
- 平均压缩率：50-60%

### PostCSS
- 用于 CSS 处理和压缩
- 自动添加浏览器前缀（Autoprefixer）
- 生产环境使用 cssnano 压缩

## 压缩效果

### JavaScript 压缩率
- main.js: 31.50 KB → 11.46 KB (63.64% 更小)
- post-share.js: 5.19 KB → 2.98 KB (42.55% 更小)
- reading-progress.js: 2.96 KB → 1.22 KB (58.87% 更小)
- sidebar.js: 5.25 KB → 2.29 KB (56.45% 更小)
- swiper-init.js: 1.40 KB → 661 B (54.03% 更小)
- theme-switcher.js: 4.65 KB → 2.42 KB (47.92% 更小)
- archives-page.js: 706 B → 464 B (34.28% 更小)
- headerCanvas.js: 7.04 KB → 2.56 KB (63.61% 更小)

## 生产环境部署

在生产环境部署前，请运行：

```bash
npm run build
```

这将：
1. 检查所有代码质量
2. 压缩 CSS 文件到 `css/build.css`
3. 压缩 JavaScript 文件到 `js/dist/` 目录

然后在 HTML 中引用压缩后的文件：
- CSS: `css/build.css`
- JS: `js/dist/*.js`

## 开发建议

1. **提交代码前**：运行 `npm run lint` 检查代码质量
2. **修复问题**：运行 `npm run lint:fix` 自动修复大部分问题
3. **构建测试**：运行 `npm run build` 确保构建成功
4. **性能优化**：始终使用压缩后的文件部署到生产环境

## 浏览器支持

根据 `browserslist` 配置，支持：
- 市场份额 > 1% 的浏览器
- 最近 2 个版本
- 不包括已停止维护的浏览器
- 不支持 IE 11 及以下版本
