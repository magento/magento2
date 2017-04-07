<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Environment initialization
 */
error_reporting(E_ALL);
#ini_set('display_errors', 1);

/* PHP version validation */
if (!defined('PHP_VERSION_ID') || !(PHP_VERSION_ID >= 50605 && PHP_VERSION_ID < 50700 || PHP_VERSION_ID === 70002 || PHP_VERSION_ID === 70004 || PHP_VERSION_ID >= 70006)) {
    if (PHP_SAPI == 'cli') {
        echo 'Magento supports PHP 5.6.5, 7.0.2, 7.0.4, and 7.0.6 or later. ' .
            'Please read http://devdocs.magento.com/guides/v1.0/install-gde/system-requirements.html';
    } else {
        echo <<<HTML
<div style="font:12px/1.35em arial, helvetica, sans-serif;">
    <p>Magento supports PHP 5.6.5, 7.0.2, 7.0.4, and 7.0.6 or later. Please read
    <a target="_blank" href="http://devdocs.magento.com/guides/v1.0/install-gde/system-requirements.html">
    Magento System Requirements</a>.
</div>
HTML;
    }
    exit(1);
}

require_once __DIR__ . '/autoload.php';
require_once BP . '/app/functions.php';

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

if (!empty($_SERVER['MAGE_PROFILER'])
    && isset($_SERVER['HTTP_ACCEPT'])
    && strpos($_SERVER['HTTP_ACCEPT'], 'text/html') !== false
) {
    \Magento\Framework\Profiler::applyConfig(
        $_SERVER['MAGE_PROFILER'],
        BP,
        !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest'
    );
}

date_default_timezone_set('UTC');

/*  Adjustment of precision value for several versions of PHP */
ini_set('precision', 17);
ini_set('serialize_precision', 17);

register_shutdown_function(function (){
    if (strpos(@$_SERVER['HTTP_ACCEPT'], 'text/html') !== false) {
        /** @var \Magento\Framework\App\Resource $adapter */
        $adapter =  \Magento\Framework\App\ObjectManager::getInstance()
            ->get('Magento\Framework\App\ResourceConnection');
        // composer.phar  require "jdorn/sql-formatter:1.3.*@dev"
        // require_once '/home/user/.composer/vendor/jdorn/sql-formatter/lib/SqlFormatter.php';
        /** @var Zend_Db_Profiler $profiler */
        $profiler = $adapter->getConnection('read')->getProfiler();
        if ($profiler->getEnabled()) {
            echo "<table cellpadding='0' cellspacing='0' border='0'>";
            echo '<tr><th>', $profiler->getTotalElapsedSecs(), 's ','</th><th>', $profiler->getTotalNumQueries() , 'queries', '</th><th>', microtime(1) - $_SERVER['REQUEST_TIME_FLOAT'], '</th></tr>';
            foreach ($profiler->getQueryProfiles() as $query) {
                /** @var Zend_Db_Profiler_Query $query*/
                echo '<tr>';
                echo '<td>', number_format(1000 * $query->getElapsedSecs(), 2), 'ms', '</td>';
                echo '<td>', $query->getQuery(), '</td>';
                echo '</tr>';
            }
        }
    }
});
