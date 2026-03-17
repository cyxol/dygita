## Dygita 主题 CSS 结构说明

本目录下的样式文件按“分层 + 职责单一”的方式组织，保证长期维护和多人协作时容易理解和扩展。

- **variables.css**：设计令牌 / 主题变量层  
  - 只包含 `:root` 与 `[data-theme="dark"]` 等变量定义（颜色、字号、间距、阴影、断点等）。  
  - 不写任何 class/元素选择器规则。

- **base.css**：基础 Reset 与 HTML 元素默认样式  
  - Reset / Normalize，以及 `html/body/h1~h6/p/a/table/form/input` 等原生元素的默认样式。  
  - 禁止出现业务类名（如 `.post-*`、`.sidebar-*` 等）。

- **layout.css**：布局结构层  
  - 负责整体布局：`.main-container`、`.content-wrap`、`.sidebar-left/right`、`.container-inner` 等容器和栅格。  
  - 包含各断点（如 1200 / 992 / 768）下的 flex/宽度变化与列方向切换，只关心“摆位置”，不改配色。

- **components.css**：可复用组件层  
  - 存放通用 UI 组件：按钮、导航、卡片、分页、标签云、表单控件组等。  
  - 按注释分块维护，例如 `/* Buttons */`、`/* Navigation */`、`/* Cards */`、`/* Pagination */` 等。

- **custom.css**：页面 / 业务定制层  
  - 与具体页面或模板强绑定的样式，例如：  
    - 首页（轮播、首页文章列表细节等）；  
    - 文章页（正文、上一篇/下一篇等）；  
    - 特定模板页（tag cloud、导航页、下载页等）。  
  - 建议按页面/模块分块注释：`/* Index page */`、`/* Post page */`、`/* Page: tag cloud */` 等。

- **inline.css**：小体量补丁层  
  - 零散的小补丁、浏览器兼容性修复、局部 1–2 条规则的覆盖，例如特定场景下的 `z-index` 或 `position` 调整。  
  - 建议写明补丁来源和原因；长期存在的补丁应定期“回收”到对应职责文件。

- **style.css**：主题主入口 / 兼容层  
  - 包含主题头信息（Theme Name 等）以及极少量全局兜底规则（如深色模式 tap 高亮、首屏防白闪等）。  
  - 新增样式原则上不要再往这里堆，而是放入上面相应的分层文件中。

- **build.css**：构建产物（只读）  
  - 由 `variables.css`、`base.css`、`layout.css`、`components.css`、`custom.css`、`inline.css` 通过构建脚本合并生成。  
  - 不建议手动编辑，如需修改请改源文件并重新构建。

- **editor-style.css**：后台编辑器样式  
  - 仅作用于 Typecho 后台编辑器，使编辑内容预览更接近前台展示效果。

- **font-awesome.min.css**：第三方 icon 字体库（vendor）  
  - 作为外部依赖引入，不与主题自有样式混合编辑。

### 写样式时的落点规则

- 新颜色 / 尺寸 / 断点：写入 `variables.css`。  
- 调整 HTML 元素默认样式：写入 `base.css`。  
- 调整整体布局 / 栅格 / 侧栏宽度：写入 `layout.css`。  
- 新增通用组件（按钮、导航、卡片、分页等）：写入 `components.css` 对应分块。  
- 仅在某个页面或模板使用的样式：写入 `custom.css` 对应页面分块。  
- 临时、小范围 bug 补丁：写入 `inline.css`，后续有机会再归类到对应层。

### 命名与 `!important` 约定

- 建议使用语义化、统一风格的类名（接近 BEM），例如：`post-card`、`post-card__title`、`post-card--featured`。  
- 避免 `.box1`、`.left1`、`.right2` 等不具备语义的类名。  
- **禁止在组件层 / 布局层随意使用 `!important`**：  
  - 如确实为补丁所需，应将规则写入 `inline.css`，并注明原因与作用范围。

