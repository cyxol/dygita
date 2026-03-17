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
            var main = document.querySelector('.main-container');
            var left = document.querySelector('.sidebar-left');
            var right = document.querySelector('.sidebar-right');
            if (!main || !left || !right) return;
            if (left.classList.contains('collapsed') && right.classList.contains('collapsed')) {
                main.classList.add('both-collapsed');
            } else {
                main.classList.remove('both-collapsed');
            }
        }

        function saveSidebarState() {
            var left = document.querySelector('.sidebar-left');
            var right = document.querySelector('.sidebar-right');
            var state = {
                leftCollapsed: left ? left.classList.contains('collapsed') : false,
                rightCollapsed: right ? right.classList.contains('collapsed') : false
            };
            try { localStorage.setItem('sidebarState', JSON.stringify(state)); } catch (e) {}
        }

        function loadSidebarState() {
            var state = {};
            try { state = JSON.parse(localStorage.getItem('sidebarState') || '{}'); } catch (e) {}
            var sidebarLeft = document.querySelector('.sidebar-left');
            var sidebarRight = document.querySelector('.sidebar-right');
            var leftToggle = document.querySelector('.sidebar-toggle.left');
            var rightToggle = document.querySelector('.sidebar-toggle.right');
            var mainContainer = document.querySelector('.main-container');

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
