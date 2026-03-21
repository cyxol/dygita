document.addEventListener('DOMContentLoaded', function () {
  var buttons = document.querySelectorAll('.collapseButton');
  if (!buttons || !buttons.forEach) return;

  buttons.forEach(function (btn) {
    btn.addEventListener('click', function () {
      var parent = this.closest('.xControl');
      if (!parent) return;

      var list = parent.querySelector('.archives-list');
      if (list) {
        list.style.display = list.style.display === 'none' ? 'block' : 'none';
      }
      this.classList.toggle('collapsed');
      var expanded = this.getAttribute('aria-expanded') === 'true';
      this.setAttribute('aria-expanded', expanded ? 'false' : 'true');
    });
  });
});

