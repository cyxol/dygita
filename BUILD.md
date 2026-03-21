# Dygita 主题构建工具

## 快速开始

### 安装依赖
```bash
npm install
```

### 构建样式
```bash
# 开发构建（仅合并文件）
npm run build-css

# 生产构建（Autoprefixer + 压缩）
npm run build-css:prod

# 简化命令
npm run build
```

## 构建脚本说明

### 开发模式
- 合并所有 CSS 源文件到 `css/build.css`
- 保留注释和格式，便于调试
- 不进行任何优化处理

### 生产模式
- 合并所有 CSS 源文件
- **Autoprefixer**: 自动添加浏览器前缀，提高兼容性
- **cssnano 压缩**: 移除注释、空白，优化文件大小
- 显示压缩统计信息

### 浏览器支持
配置的浏览器支持范围：
- `> 1%`: 全球使用率大于 1% 的浏览器
- `last 2 versions`: 每个浏览器的最近两个版本
- `not dead`: 排除已停止维护的浏览器
- `not ie <= 11`: 排除 IE 11 及以下版本

## 文件结构

```
tools/
├── build-css.js          # 主构建脚本
├── postcss.config.js     # PostCSS 配置
package.json              # 依赖和脚本配置
```

## 故障排除

### PostCSS 依赖缺失
如果看到 "PostCSS dependencies not found" 错误：
```bash
npm install
```

### 构建失败
构建脚本包含错误处理，如果 PostCSS 处理失败会自动回退到基础合并模式。

## 手动构建

如果不使用 npm，也可以直接运行：
```bash
# 开发构建
node tools/build-css.js

# 生产构建
node tools/build-css.js --production
```
