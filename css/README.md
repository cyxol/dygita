## Dygita CSS 目录结构（已完成重构）

当前 CSS 已按分层架构落地，实现了高度模块化和可维护性。所有的构建逻辑由 `tools/build-css.js` 处理。

### 1. 目录说明

- **`base/`** (基础层): 定义设计令牌和全局重置
  - `variables.css`: 设计变量（颜色、间距、Z-index 等），支持暗色模式。
  - `skin.css`: 动态皮肤颜色映射。
  - `reset.css`: 基础 HTML 元素重置。
  - `typography.css`: 字体与排版规范。
- **`layout/`** (布局层): 核心 Grid 与页面框架
  - `grid.css`: 主容器 Flex/Grid 响应式布局。
  - `header.css`: 顶部导航与英雄头部。
  - `sidebar-left.css`: 左侧 AI 聊天侧边栏。
  - `sidebar-right.css`: 右侧工具栏。
  - `footer.css`: 页脚样式。
- **`components/`** (组件层): 独立的功能模块
  - `article.css`: 文章详情页样式。
  - `excerpts.css`: 首页文章列表卡片。
  - `pagination.css`: 翻页控件（已修复居中对齐）。
  - `search.css`: 搜索弹出层。
  - `comments.css`: 评论系统。
  - `code.css`: 代码块美化。
  - `carousel.css`: 幻灯片（Swiper）。
  - `sidebar-widgets.css`: 侧边栏小工具容器。
  - `tag-cloud.css`: 标签云。
  - `toc.css`: 文章目录。
  - `links.css`: 友情链接与导航模板（独立加载）。
  - `toast.css`: 提示消息。
  - `buttons.css`, `table.css`: 通用 UI 元素。
- **`themes/`** (主题层)
  - `dark-mode.css`: 暗色模式的详细覆盖规则。
- **`vendor/`** (第三方库)
  - 系统依赖的外部 CSS 库。

### 2. 构建产物

- **`style.css`**: 主题注册入口（仅含元数据，不含逻辑）。
- **`build.css`**: 由 `tools/build-css.js` 自动生成的最终合并产物，前台实际调用的样式表。

### 3. 开发约定

- **禁止直接修改 `build.css`**：所有的样式修改必须在 `css/` 下对应的源文件中进行。
- **构建命令**：
  - 在主题根目录执行：`npm run build-css` (推荐) 或 `node tools/build-css.js`。
- **防止闪白**：
  - 核心暗色模式初始化样式已内联至 `header.php`，不再使用独立的 `dark-init.css` 以减少 HTTP 请求。
- **独立文件**：
  - `links.css` 仅在链接和导航页面按需加载，未合并进 `build.css`。

### 4. Git 规范

- 每次提交样式修改前，请确保执行构建并同时提交源文件与更新后的 `build.css`。
