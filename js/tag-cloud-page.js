document.addEventListener('DOMContentLoaded', function () {
  if (typeof TagCanvas === 'undefined') {
    return;
  }

  var canvas = document.getElementById('tag-cloud-tags');
  var fallback = document.querySelector('.tag-cloud-fallback');

  try {
    TagCanvas.Start('tag-cloud-tags', '', {
      textColour: null,
      outlineColour: 'transparent',
      reverse: true,
      depth: 0.8,
      maxSpeed: 0.05,
      weight: true,
      weightFrom: 'data-weight',
      weightMode: 'both',
      wheelZoom: false,
    });

    if (canvas) {
      canvas.style.display = 'block';
    }
    if (fallback) {
      fallback.style.display = 'none';
    }
  } catch (e) {
    if (canvas) {
      canvas.style.display = 'none';
    }
  }
});

