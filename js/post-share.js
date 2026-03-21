;(function () {
  if (typeof window === 'undefined') return;

  var NAMESPACE = 'DygitaShare';

  // Toast 提示框 - 与 main.js 共享
  function showToast(message, type) {
    // 尝试使用 main.js 中的 showToast（如果已加载）
    if (window.DYGITA && typeof window.DYGITA.showToast === 'function') {
      window.DYGITA.showToast(message, type);
      return;
    }
    
    // Fallback: 简化版 toast
    type = type || 'info';
    var container = document.getElementById('dygita-toast-container');
    if (!container) {
      container = document.createElement('div');
      container.id = 'dygita-toast-container';
      container.style.cssText = 'position:fixed;top:20px;right:20px;z-index:99999;pointer-events:none;';
      document.body.appendChild(container);
    }
    
    var toast = document.createElement('div');
    var icons = {
      'success': 'fa-check-circle',
      'error': 'fa-times-circle',
      'info': 'fa-info-circle',
      'warning': 'fa-exclamation-triangle'
    };
    var iconClass = icons[type] || icons.info;
    
    var escapeHtml = function(str) {
      var div = document.createElement('div');
      div.textContent = str;
      return div.innerHTML;
    };
    
    toast.innerHTML = '<i class="fa ' + iconClass + '"></i><span>' + escapeHtml(message) + '</span>';
    toast.style.cssText = 'display:flex;align-items:center;gap:8px;padding:12px 20px;margin-bottom:10px;'
      + 'background:#fff;border-radius:4px;box-shadow:0 2px 12px rgba(0,0,0,0.15);'
      + 'font-size:14px;line-height:1.5;pointer-events:auto;'
      + 'transform:translateX(400px);transition:transform 0.3s ease,opacity 0.3s ease;opacity:0;'
      + 'max-width:350px;word-break:break-word;';
    
    var colors = {
      'success': '#52c41a',
      'error': '#f5222d',
      'info': '#1890ff',
      'warning': '#faad14'
    };
    toast.querySelector('i').style.color = colors[type] || colors.info;
    
    container.appendChild(toast);
    setTimeout(function() {
      toast.style.transform = 'translateX(0)';
      toast.style.opacity = '1';
    }, 10);
    
    setTimeout(function() {
      toast.style.transform = 'translateX(400px)';
      toast.style.opacity = '0';
      setTimeout(function() {
        if (toast.parentNode) toast.parentNode.removeChild(toast);
        if (container.children.length === 0 && container.parentNode) {
          container.parentNode.removeChild(container);
        }
      }, 300);
    }, 3000);
    
    toast.addEventListener('click', function() {
      toast.style.transform = 'translateX(400px)';
      toast.style.opacity = '0';
      setTimeout(function() {
        if (toast.parentNode) toast.parentNode.removeChild(toast);
      }, 300);
    });
  }

  // 依赖于全局的 shareData（由 post.php 内联输出，挂在 window.DYGITA.shareData）
  function getShareData() {
    if (!window.DYGITA || !window.DYGITA.shareData) {
      return null;
    }
    return window.DYGITA.shareData;
  }

  function shareToWechat() {
    showToast('请打开微信，使用扫一扫功能扫描二维码分享', 'info');
    return false;
  }

  function shareToWeibo() {
    var data = getShareData();
    if (!data) return false;
    var url =
      'http://service.weibo.com/share/share.php?url=' +
      encodeURIComponent(data.url) +
      '&title=' +
      encodeURIComponent(data.title) +
      '&content=' +
      encodeURIComponent(data.excerpt) +
      '&pic=' +
      encodeURIComponent(data.pic);
    window.open(url, '_blank', 'width=600,height=400');
    return false;
  }

  function shareToQQ() {
    var data = getShareData();
    if (!data) return false;
    var url =
      'https://connect.qq.com/widget/shareqq/index.html?url=' +
      encodeURIComponent(data.url) +
      '&title=' +
      encodeURIComponent(data.title) +
      '&summary=' +
      encodeURIComponent(data.excerpt) +
      '&pics=' +
      encodeURIComponent(data.pic);
    window.open(url, '_blank', 'width=600,height=400');
    return false;
  }

  function copyLink() {
    var data = getShareData();
    if (!data) return false;
    var link = data.url;

    if (navigator.clipboard && navigator.clipboard.writeText) {
      navigator.clipboard
        .writeText(link)
        .then(function () {
          showToast('链接已复制到剪贴板', 'success');
        })
        .catch(function () {
          fallbackCopy(link);
        });
    } else {
      fallbackCopy(link);
    }
    return false;
  }

  function fallbackCopy(text) {
    var t = document.createElement('textarea');
    t.value = text;
    t.style.cssText = 'position:fixed;top:-9999px;left:-9999px;';
    document.body.appendChild(t);
    t.select();
    var success = false;
    try {
      success = document.execCommand('copy');
    } catch (e) {}
    document.body.removeChild(t);
    
    if (success) {
      showToast('链接已复制到剪贴板', 'success');
    } else {
      showToast('复制失败，请手动复制', 'error');
    }
  }

  window[NAMESPACE] = {
    shareToWechat: shareToWechat,
    shareToWeibo: shareToWeibo,
    shareToQQ: shareToQQ,
    copyLink: copyLink
  };
})();
