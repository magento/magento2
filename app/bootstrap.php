<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Environment initialization
 */
error_reporting(E_ALL);
#ini_set('display_errors', 1);
umask(0);

/* PHP version validation */
if (version_compare(phpversion(), '5.4.11', '<') === true) {
    if (PHP_SAPI == 'cli') {
        echo 'Magento supports PHP 5.4.11 or later. ' .
            'Please read http://devdocs.magento.com/guides/v1.0/install-gde/system-requirements.html';
    } else {
        echo <<<HTML
<div style="font:12px/1.35em arial, helvetica, sans-serif;">
    <p>Magento supports PHP 5.4.11 or later. Please read
    <a target="_blank" href="http://devdocs.magento.com/guides/v1.0/install-gde/system-requirements.html">
    Magento System Requirements</a>.
</div>
HTML;
    }
    exit(1);
}

require_once __DIR__ . '/autoload.php';
require_once BP . '/app/functions.php';

if (!empty($_SERVER['MAGE_PROFILER'])) {
    \Magento\Framework\Profiler::applyConfig($_SERVER['MAGE_PROFILER'], BP, !empty($_REQUEST['isAjax']));
}
if (ini_get('date.timezone') == '') {
    date_default_timezone_set(\Magento\Framework\Stdlib\DateTime\TimezoneInterface::DEFAULT_TIMEZONE);
}
