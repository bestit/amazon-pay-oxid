<?php
$sLocalPath = realpath(__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR);
$sLocalAutoLoader = $sLocalPath.DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.'autoload.php';

if (file_exists($sLocalAutoLoader) === true) {
    require_once $sLocalAutoLoader;
} elseif (defined('VENDOR_PATH') === true) {
    require_once VENDOR_PATH.'autoload.php';
}

include __DIR__.'/phpunitextensions.php';
