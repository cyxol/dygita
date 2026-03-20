## Dygita CSS 目录结构（Phase 1）

当前已按新分层目录落地，目标是在不改变浏览器视觉与交互的前提下逐步完成细化拆分。

### 目录

- vendor/
  - font-awesome.min.css
  - prism-tomorrow.min.css
  - swiper-bundle.min.css
- base/
  - reset.css
  - typography.css
  - variables.css
- layout/
  - grid.css
  - header.css
  - sidebar-left.css
  - main-content.css
  - sidebar-right.css
  - footer.css
- components/
  - buttons.css
  - article.css
  - tag-cloud.css
  - toc.css
  - pagination.css
- themes/
  - dark-mode.css
- style.css (主题入口)
- build.css (构建产物)

### 构建顺序

由 js/build-css.js 合并以下文件到 build.css：

1. css/base/variables.css
2. css/base/reset.css
3. css/base/typography.css
4. css/layout/grid.css
5. css/layout/header.css
6. css/layout/sidebar-left.css
7. css/layout/main-content.css
8. css/layout/sidebar-right.css
9. css/layout/footer.css
10. css/components/buttons.css
11. css/components/article.css
12. css/components/tag-cloud.css
13. css/components/toc.css
14. css/components/pagination.css
15. css/themes/dark-mode.css

### Phase 1 说明（零差异迁移）

- 本阶段重点是目录结构迁移与构建链路切换，不做视觉重设计。
- 为保证零差异，部分规则暂时集中承载在少数文件中：
  - buttons.css 承载原 components.css 内容
  - article.css 承载原 custom.css 内容
  - pagination.css 承载原 inline.css 内容
- 其余新文件先作为职责占位，后续 Phase 2 再细化拆分。

### 开发约定

- 修改样式优先改分层源文件，不直接手改 build.css。
- 旧 style-legacy-* 文件已并入现有分层文件，不再保留 style-legacy 前缀文件。
- 根目录旧文件 `variables.css` / `base.css` / `layout.css` / `components.css` / `custom.css` / `inline.css` 仅保留迁移占位说明，不再承载实际样式。
- 每次改动后执行：node js/build-css.js（在主题目录执行）或 node usr/themes/dygita/js/build-css.js（在仓库根目录执行）。
- style.css 继续作为主题入口文件加载，build.css 为主样式产物。

