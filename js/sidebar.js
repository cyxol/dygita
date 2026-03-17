/**
 * 侧边栏左右折叠功能
 */
(function() {
    function initSidebarToggle() {
        var body = document.body;

        if (!document.querySelector('.sidebar-toggle.left')) {
            var leftBtn = document.createElement('div');
            leftBtn.className = 'sidebar-toggle left';
            body.appendChild(leftBtn);
        }

        if (!document.querySelector('.sidebar-toggle.right')) {
            var rightBtn = document.createElement('div');
            rightBtn.className = 'sidebar-toggle right';
            body.appendChild(rightBtn);
        }

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

        function loadSidebarState() {
            var state = {};
            try { state = JSON.parse(localStorage.getItem('sidebarState') || '{}'); } catch (e) {}
            // Use closure-scoped variables (queried once above)

            if (state.leftCollapsed && sidebarLeft) {
                sidebarLeft.classList.add('collapsed');
                if (leftToggle) leftToggle.classList.add('collapsed');
                if (mainContainer) mainContainer.classList.add('left-collapsed');
            }
            if (state.rightCollapsed && sidebarRight) {
                sidebarRight.classList.add('collapsed');
                if (rightToggle) rightToggle.classList.add('collapsed');
                if (mainContainer) mainContainer.classList.add('right-collapsed');
            }
            updateBothCollapsedClass();
        }

        if (leftToggle) {
            leftToggle.addEventListener('click', function() {
                if (sidebarLeft) sidebarLeft.classList.toggle('collapsed');
                this.classList.toggle('collapsed');
                if (mainContainer) {
                    if (sidebarLeft && sidebarLeft.classList.contains('collapsed')) {
                        mainContainer.classList.add('left-collapsed');
                    } else {
                        mainContainer.classList.remove('left-collapsed');
                    }
                }
                updateBothCollapsedClass();
                saveSidebarState();
            });
        }

        if (rightToggle) {
            rightToggle.addEventListener('click', function() {
                if (sidebarRight) sidebarRight.classList.toggle('collapsed');
                this.classList.toggle('collapsed');
                if (mainContainer) {
                    if (sidebarRight && sidebarRight.classList.contains('collapsed')) {
                        mainContainer.classList.add('right-collapsed');
                    } else {
                        mainContainer.classList.remove('right-collapsed');
                    }
                }
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
