;(function () {
  if (typeof window === 'undefined') return;

  var NAMESPACE = 'DygitaShare';

  // 依赖于全局的 shareData（由 post.php 内联输出，挂在 window.DYGITA.shareData）
  function getShareData() {
    if (!window.DYGITA || !window.DYGITA.shareData) {
      return null;
    }
    return window.DYGITA.shareData;
  }

  function shareToWechat() {
    alert('请打开微信，使用扫一扫功能扫描下方二维码分享');
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
          alert('链接已复制到剪贴板');
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
    document.body.appendChild(t);
    t.select();
    try {
      document.execCommand('copy');
    } catch (e) {}
    document.body.removeChild(t);
    alert('链接已复制到剪贴板');
  }

  window[NAMESPACE] = {
    shareToWechat: shareToWechat,
    shareToWeibo: shareToWeibo,
    shareToQQ: shareToQQ,
    copyLink: copyLink
  };
})();

