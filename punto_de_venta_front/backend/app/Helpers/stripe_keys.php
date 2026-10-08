<?php
// Stripe keys: leer desde variables de entorno para evitar exponer secretos en el repositorio
// Configura en tu entorno (Apache/Nginx o variables del sistema):
//   STRIPE_SECRET_KEY, STRIPE_PUBLISHABLE_KEY
// En desarrollo también puedes cargar desde un archivo no versionado.

// First try environment variables
$__stripeSecret = getenv('STRIPE_SECRET_KEY') ?: '';
$__stripePublishable = getenv('STRIPE_PUBLISHABLE_KEY') ?: '';

// Helper to parse a simple KEY=VALUE .env file (ignores comments and empty lines)
function _pv_parse_env_file($path, &$secret, &$publishable) {
	if (!file_exists($path)) return false;
	$lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
	foreach ($lines as $ln) {
		$ln = trim($ln);
		if ($ln === '' || strpos($ln, '#') === 0) continue;
		if (strpos($ln, '=') !== false) {
			list($k,$v) = array_map('trim', explode('=', $ln, 2));
			$v = trim($v, " \t\"'\r\n");
			if ($k === 'STRIPE_SECRET_KEY' && $secret === '') $secret = $v;
			if ($k === 'STRIPE_PUBLISHABLE_KEY' && $publishable === '') $publishable = $v;
		}
	}
	return true;
}

// If not found in env, try a couple of likely .env file locations:
// 1) backend/.env (project backend root)
// 2) backend/app/.env (older layout)
if (empty($__stripeSecret) || empty($__stripePublishable)) {
	$candidates = [
		dirname(__DIR__, 2) . '/.env', // backend/.env
		dirname(__DIR__, 1) . '/.env', // backend/app/.env
	];
	foreach ($candidates as $envFile) {
		if (!file_exists($envFile)) continue;
		_pv_parse_env_file($envFile, $__stripeSecret, $__stripePublishable);
		if (!empty($__stripeSecret) && !empty($__stripePublishable)) break;
	}
}

define('STRIPE_SECRET_KEY', $__stripeSecret);
define('STRIPE_PUBLISHABLE_KEY', $__stripePublishable);
