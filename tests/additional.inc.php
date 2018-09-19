<?php
$sLocalPath = realpath(__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR);
$sLocalAutoLoader = $sLocalPath.DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.'autoload.php';

if (file_exists($sLocalAutoLoader) === true) {
    require_once $sLocalAutoLoader;
} elseif (defined('VENDOR_PATH') === true) {
    require_once VENDOR_PATH.'autoload.php';
}

// Get oxid version from env variable
$sOxidVersion = getenv('OXID_VERSION');

if ($sOxidVersion === '5') {
    include __DIR__.'/phpunit_oxid5.php';
} elseif ($sOxidVersion === '6') {
    include __DIR__.'/phpunit_oxid6.php';
}
