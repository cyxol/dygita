/**
 * 侧边栏左右折叠功能
 */
(function() {
    var chevronLeft = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"></polyline></svg>';
    var chevronRight = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 6 15 12 9 18"></polyline></svg>';

    function initSidebarToggle() {
        var sidebarLeft = document.querySelector('.sidebar-left');
        var sidebarRight = document.querySelector('.sidebar-right');
        var mainContainer = document.querySelector('.main-container');
        var leftToggle = document.querySelector('.sidebar-toggle.left');
        var rightToggle = document.querySelector('.sidebar-toggle.right');

        function updateBothCollapsedClass() {
            if (!mainContainer || !sidebarLeft || !sidebarRight) return;
            if (sidebarLeft.classList.contains('collapsed') && sidebarRight.classList.contains('collapsed')) {
                mainContainer.classList.add('both-collapsed');
            } else {
                mainContainer.classList.remove('both-collapsed');
            }
        }

        function saveSidebarState() {
            var state = {
                leftCollapsed: sidebarLeft ? sidebarLeft.classList.contains('collapsed') : false,
                rightCollapsed: sidebarRight ? sidebarRight.classList.contains('collapsed') : false
            };
            try { localStorage.setItem('sidebarState', JSON.stringify(state)); } catch (e) {}
        }

        function moveToggle(toggle, sidebar, isCollapsed) {
            if (!toggle || !sidebar) return;
            if (isCollapsed) {
                document.body.appendChild(toggle);
            } else {
                sidebar.insertBefore(toggle, sidebar.firstChild);
            }
        }

        function updateIcon(toggle, side, isCollapsed) {
            if (!toggle) return;
            if (side === 'left') {
                toggle.innerHTML = isCollapsed ? chevronRight : chevronLeft;
                toggle.setAttribute('title', isCollapsed ? '展开左侧栏' : '折叠左侧栏');
            } else {
                toggle.innerHTML = isCollapsed ? chevronLeft : chevronRight;
                toggle.setAttribute('title', isCollapsed ? '展开右侧栏' : '折叠右侧栏');
            }
        }

        function loadSidebarState() {
            var state = {};
            try { state = JSON.parse(localStorage.getItem('sidebarState') || '{}'); } catch (e) {}

            if (state.leftCollapsed && sidebarLeft) {
                sidebarLeft.classList.add('collapsed');
                if (leftToggle) leftToggle.classList.add('collapsed');
                if (mainContainer) mainContainer.classList.add('left-collapsed');
                moveToggle(leftToggle, sidebarLeft, true);
                updateIcon(leftToggle, 'left', true);
            }
            if (state.rightCollapsed && sidebarRight) {
                sidebarRight.classList.add('collapsed');
                if (rightToggle) rightToggle.classList.add('collapsed');
                if (mainContainer) mainContainer.classList.add('right-collapsed');
                moveToggle(rightToggle, sidebarRight, true);
                updateIcon(rightToggle, 'right', true);
            }
            updateBothCollapsedClass();
        }

        if (leftToggle) {
            leftToggle.addEventListener('click', function() {
                if (sidebarLeft) sidebarLeft.classList.toggle('collapsed');
                this.classList.toggle('collapsed');
                var isCollapsed = sidebarLeft && sidebarLeft.classList.contains('collapsed');
                if (mainContainer) {
                    if (isCollapsed) {
                        mainContainer.classList.add('left-collapsed');
                    } else {
                        mainContainer.classList.remove('left-collapsed');
                    }
                }
                moveToggle(this, sidebarLeft, isCollapsed);
                updateIcon(this, 'left', isCollapsed);
                updateBothCollapsedClass();
                saveSidebarState();
            });
        }

        if (rightToggle) {
            rightToggle.addEventListener('click', function() {
                if (sidebarRight) sidebarRight.classList.toggle('collapsed');
                this.classList.toggle('collapsed');
                var isCollapsed = sidebarRight && sidebarRight.classList.contains('collapsed');
                if (mainContainer) {
                    if (isCollapsed) {
                        mainContainer.classList.add('right-collapsed');
                    } else {
                        mainContainer.classList.remove('right-collapsed');
                    }
                }
                moveToggle(this, sidebarRight, isCollapsed);
                updateIcon(this, 'right', isCollapsed);
                updateBothCollapsedClass();
                saveSidebarState();
            });
        }

        loadSidebarState();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initSidebarToggle);
    } else {
        initSidebarToggle();
    }
})();
