<?php

header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");
header('Content-type: application/json; Charset: utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate');
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");

function forbid() {
    header('HTTP/1.1 403 Forbidden');
    exit;
}

// Get the admin dir from the referer
list($void, $adminDir, $script) = explode('/', parse_url($_SERVER['HTTP_REFERER'], PHP_URL_PATH));
define('_PS_ADMIN_DIR_', $_SERVER['DOCUMENT_ROOT'].'/'.$adminDir.'/');

if (!defined('PS_ADMIN_DIR')) {
    define('PS_ADMIN_DIR', _PS_ADMIN_DIR_);
}

require __DIR__ .'/../../config/config.inc.php';
require _PS_ADMIN_DIR_.'functions.php';
require _PS_ADMIN_DIR_.'init.php';

// Prevent remote calls from other hosts or direct calls (no host)
if (empty($_SERVER['HTTP_REFERER']) || $_SERVER['HTTP_HOST'] != parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST)) {
    forbid();
}

require_once __DIR__ . '/vendor/autoload.php';

if (Tools::getValue('ajax') == 1 && ($action = Tools::getValue('action')) != false) {
    $action = Tools::toCamelCase('ajax_admin_'.$action);
    $module = Module::getInstanceByName('loqate');
    if (method_exists($module, $action)) {
        echo json_encode($module->{$action}());
        exit;
    }
}

forbid();
