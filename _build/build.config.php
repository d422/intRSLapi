<?php

/* define package */
define('PKG_NAME', 'infoDBapi');
define('PKG_NAME_LOWER', strtolower(PKG_NAME));

define('PKG_VERSION', '1.0.0');
define('PKG_RELEASE', 'beta');
define('PKG_AUTO_INSTALL', true);
define('PKG_NAMESPACE_PATH', '{core_path}components/' . PKG_NAME_LOWER . '/');

/* define paths */
if (isset($_SERVER['MODX_BASE_PATH'])) {
    define('MODX_BASE_PATH', $_SERVER['MODX_BASE_PATH']);
} else {
    define('MODX_BASE_PATH', str_replace($_SERVER['SCRIPT_NAME'], '', $script_name) . '/');
}

/* include custom core config and define core path */
include(MODX_BASE_PATH . 'config.core.php');

if (!defined('MODX_CORE_PATH')) {
    define('MODX_CORE_PATH', MODX_BASE_PATH . 'core/');
}
define('MODX_MANAGER_PATH', MODX_BASE_PATH . 'manager/');
define('MODX_CONNECTORS_PATH', MODX_BASE_PATH . 'connectors/');
define('MODX_ASSETS_PATH', MODX_BASE_PATH . 'assets/');

/* define urls */
define('MODX_BASE_URL', '/');
define('MODX_CORE_URL', MODX_BASE_URL . 'core/');
define('MODX_MANAGER_URL', MODX_BASE_URL . 'manager/');
define('MODX_CONNECTORS_URL', MODX_BASE_URL . 'connectors/');
define('MODX_ASSETS_URL', MODX_BASE_URL . 'assets/');

$BUILD_RESOLVERS = array(
    'pkg',
);
