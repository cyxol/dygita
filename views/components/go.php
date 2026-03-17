<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
<?php
$t_url = isset($_GET['url']) ? trim((string) $_GET['url']) : '';
$options = Typecho\Widget::widget('Widget_Options');

function dygita_go_is_host_allowed($host, array $allowHosts) {
	$host = strtolower(trim((string) $host));
	if ($host === '') {
		return false;
	}

	foreach ($allowHosts as $allowed) {
		$allowed = strtolower(trim((string) $allowed));
		if ($allowed === '') {
			continue;
		}
		if ($host === $allowed) {
			return true;
		}
		if (substr($allowed, 0, 1) === '.' && substr($host, -strlen($allowed)) === $allowed) {
			return true;
		}
		if ($host !== $allowed && substr($host, -strlen('.' . $allowed)) === '.' . $allowed) {
			return true;
		}
	}

	return false;
}

$siteHost = strtolower((string) parse_url((string) $options->siteUrl, PHP_URL_HOST));
$allowHostsRaw = (string) dygita_opt($options, 'dygita_go_allow_hosts', 'git_go_allow_hosts');
$allowHosts = preg_split('/[\s,;|]+/', $allowHostsRaw, -1, PREG_SPLIT_NO_EMPTY);
if ($siteHost !== '') {
	$allowHosts[] = $siteHost;
}
$allowHosts = array_values(array_unique(array_map('strtolower', $allowHosts)));

if (!empty($t_url)) {
    $candidate = $t_url;
    if (!preg_match('#^https?://#i', $candidate)) {
        if (strpos($candidate, '.') !== false) {
            $candidate = 'http://' . $candidate;
        }
    }

    $isValid = false;
    if (filter_var($candidate, FILTER_VALIDATE_URL)) {
        $parts = parse_url($candidate);
        $scheme = isset($parts['scheme']) ? strtolower($parts['scheme']) : '';
        $host = isset($parts['host']) ? $parts['host'] : '';
		$isValid = in_array($scheme, array('http', 'https'), true)
			&& $host !== ''
			&& dygita_go_is_host_allowed($host, $allowHosts);
    }

    if ($isValid) {
        $url = $candidate;
        $title = '页面加载中,请稍候...';
    } else {
        $url = $options->siteUrl;
		$title = '链接未通过校验，正在返回首页...';
    }
} else {
	$title = '参数缺失，正在返回首页...';
	$url = $options->siteUrl;
}
?>
<html>

<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<meta http-equiv="refresh" content="1;url=<?php echo htmlspecialchars($url, ENT_QUOTES, 'UTF-8'); ?>">
	<title><?php echo htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?></title>
	<style>
		body {
			background: #000
		}

		.loading {
			-webkit-animation: fadein 2s;
			-moz-animation: fadein 2s;
			-o-animation: fadein 2s;
			animation: fadein 2s
		}

		@-moz-keyframes fadein {
			from {
				opacity: 0
			}

			to {
				opacity: 1
			}
		}

		@-webkit-keyframes fadein {
			from {
				opacity: 0
			}

			to {
				opacity: 1
			}
		}

		@-o-keyframes fadein {
			from {
				opacity: 0
			}

			to {
				opacity: 1
			}
		}

		@keyframes fadein {
			from {
				opacity: 0
			}

			to {
				opacity: 1
			}
		}

		.spinner-wrapper {
			position: absolute;
			top: 0;
			left: 0;
			z-index: 300;
			height: 100%;
			min-width: 100%;
			min-height: 100%;
			background: rgba(255, 255, 255, 0.93)
		}

		.spinner-text {
			position: absolute;
			top: 50%;
			left: 50%;
			margin-left: -90px;
			margin-top: 2px;
			color: #BBB;
			letter-spacing: 1px;
			font-weight: 700;
			font-size: 36px;
			font-family: Arial
		}

		.spinner {
			position: absolute;
			top: 50%;
			left: 50%;
			display: block;
			margin-left: -160px;
			width: 1px;
			height: 1px;
			border: 25px solid rgba(100, 100, 100, 0.2);
			-webkit-border-radius: 50px;
			-moz-border-radius: 50px;
			border-radius: 50px;
			border-left-color: transparent;
			border-right-color: transparent;
			-webkit-animation: spin 1.5s infinite;
			-moz-animation: spin 1.5s infinite;
			animation: spin 1.5s infinite
		}

		@-webkit-keyframes spin {

			0%,
			100% {
				-webkit-transform: rotate(0deg) scale(1)
			}

			50% {
				-webkit-transform: rotate(720deg) scale(0.6)
			}
		}

		@-moz-keyframes spin {

			0%,
			100% {
				-moz-transform: rotate(0deg) scale(1)
			}

			50% {
				-moz-transform: rotate(720deg) scale(0.6)
			}
		}

		@-o-keyframes spin {

			0%,
			100% {
				-o-transform: rotate(0deg) scale(1)
			}

			50% {
				-o-transform: rotate(720deg) scale(0.6)
			}
		}

		@keyframes spin {

			0%,
			100% {
				transform: rotate(0deg) scale(1)
			}

			50% {
				transform: rotate(720deg) scale(0.6)
			}
		}
	</style>
</head>

<body>
	<div class="loading">
		<div class="spinner-wrapper">
			<span class="spinner-text"><?php echo htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?></span>
			<span class="spinner"></span>
			<p class="go-loading-message">
				<?php _e('即将跳转到外部链接'); ?>: <br>
				<a href="<?php echo htmlspecialchars($url, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($url, ENT_QUOTES, 'UTF-8'); ?></a>
			</p>
		</div>
	</div>
</body>

</html>