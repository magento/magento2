<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Environment initialization
 */
if (in_array('phar', \stream_get_wrappers())) {
    stream_wrapper_unregister('phar');
}
#ini_set('display_errors', 1);

/* PHP version validation */
if (!defined('PHP_VERSION_ID') || PHP_VERSION_ID < 80100) {
    if (PHP_SAPI == 'cli') {
        echo 'Magento supports PHP 8.1.0 or later. ' .
            'Please read https://experienceleague.adobe.com/docs/commerce-operations/installation-guide/system-requirements.html';
    } else {
        echo <<<HTML
<div style="font:12px/1.35em arial, helvetica, sans-serif;">
    <p>Magento supports PHP 8.1.0 or later. Please read
    <a target="_blank" href="https://experienceleague.adobe.com/docs/commerce-operations/installation-guide/system-requirements.html">
    Magento System Requirements</a>.
</div>
HTML;
    }
    http_response_code(503);
    exit(1);
}

// PHP 8 compatibility. Define constants that are not present in PHP < 8.0
if (!defined('PHP_VERSION_ID') || PHP_VERSION_ID < 80000) {
    if (!defined('T_NAME_QUALIFIED')) {
        define('T_NAME_QUALIFIED', 24001);
    }
    if (!defined('T_NAME_FULLY_QUALIFIED')) {
        define('T_NAME_FULLY_QUALIFIED', 24002);
    }
}

require_once __DIR__ . '/autoload.php';
// Sets default autoload mappings, may be overridden in Bootstrap::create
\Magento\Framework\App\Bootstrap::populateAutoloader(BP, []);

/* Custom umask value may be provided in optional mage_umask file in root */
$umaskFile = BP . '/magento_umask';
$mask = file_exists($umaskFile) ? octdec(file_get_contents($umaskFile)) : 002;
umask($mask);

if (empty($_SERVER['ENABLE_IIS_REWRITES']) || ($_SERVER['ENABLE_IIS_REWRITES'] != 1)) {
    /*
     * Unset headers used by IIS URL rewrites.
     */
    unset($_SERVER['HTTP_X_REWRITE_URL']);
    unset($_SERVER['HTTP_X_ORIGINAL_URL']);
    unset($_SERVER['IIS_WasUrlRewritten']);
    unset($_SERVER['UNENCODED_URL']);
    unset($_SERVER['ORIG_PATH_INFO']);
}

if (
    (!empty($_SERVER['MAGE_PROFILER']) || file_exists(BP . '/var/profiler.flag'))
    && isset($_SERVER['HTTP_ACCEPT'])
    && strpos($_SERVER['HTTP_ACCEPT'], 'text/html') !== false
) {
    $profilerConfig = isset($_SERVER['MAGE_PROFILER']) && strlen($_SERVER['MAGE_PROFILER'])
        ? $_SERVER['MAGE_PROFILER']
        : trim(file_get_contents(BP . '/var/profiler.flag'));

    if ($profilerConfig) {
        $profilerConfig = json_decode($profilerConfig, true) ?: $profilerConfig;
    }

    Magento\Framework\Profiler::applyConfig(
        $profilerConfig,
        BP,
        !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest'
    );
}

date_default_timezone_set('UTC');

/*  For data consistency between displaying (printing) and serialization a float number */
ini_set('precision', 14);
ini_set('serialize_precision', 14);
