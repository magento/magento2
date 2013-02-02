<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category   Magento
 * @copyright  Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**#@+
 * Shortcut constants
 */
define('DS', DIRECTORY_SEPARATOR);
define('BP', dirname(__DIR__));
/**#@-*/

/**
 * Environment initialization
 */
error_reporting(E_ALL | E_STRICT);
#ini_set('display_errors', 1);
umask(0);

/**
 * Require necessary files
 */
require_once BP . '/app/code/core/Mage/Core/functions.php';
require_once BP . '/app/Mage.php';

require_once __DIR__ . '/autoload.php';
Magento_Autoload_IncludePath::addIncludePath(array(
    BP . DS . 'app' . DS . 'code' . DS . 'local',
    BP . DS . 'app' . DS . 'code' . DS . 'community',
    BP . DS . 'app' . DS . 'code' . DS . 'core',
    BP . DS . 'lib',
    BP . DS . 'var' . DS . 'generation',
));
$classMapPath = BP . DS . 'var/classmap.ser';
if (file_exists($classMapPath)) {
    require_once BP . '/lib/Magento/Autoload/ClassMap.php';
    $classMap = new Magento_Autoload_ClassMap(BP);
    $classMap->addMap(unserialize(file_get_contents($classMapPath)));
    spl_autoload_register(array($classMap, 'load'));
}

if (!defined('BARE_BOOTSTRAP')) {
    /* PHP version validation */
    if (version_compare(phpversion(), '5.3.0', '<') === true) {
        if (PHP_SAPI == 'cli') {
            echo 'Magento supports PHP 5.3.0 or newer. Please read http://www.magento.com/install.';
        } else {
            echo <<<HTML
<div style="font:12px/1.35em arial, helvetica, sans-serif;">
    <div style="margin:0 0 25px 0; border-bottom:1px solid #ccc;">
        <h3 style="margin:0;font-size:1.7em;font-weight:normal;text-transform:none;text-align:left;color:#2f2f2f;">
        Whoops, it looks like you have an invalid PHP version.</h3>
    </div>
    <p>Magento supports PHP 5.3.0 or newer.
    <a href="http://www.magento.com/install" target="">Find out</a>
    how to install Magento using PHP-CGI as a work-around.
    </p>
</div>
HTML;
        }
        exit;
    }
    if (file_exists(BP . '/maintenance.flag')) {
        if (PHP_SAPI == 'cli') {
            echo 'Service temporarily unavailable due to maintenance downtime.';
        } else {
            include_once BP . '/pub/errors/503.php';
        }
        exit;
    }
    if (isset($_SERVER['MAGE_IS_DEVELOPER_MODE'])) {
        Mage::setIsDeveloperMode(true);
    }
    if (!empty($_SERVER['MAGE_PROFILER'])) {
        $profilerConfigData = $_SERVER['MAGE_PROFILER'];

        $profilerConfig = array(
            'baseDir' => dirname(__DIR__),
            'tagFilters' => array()
        );

        if (is_scalar($profilerConfigData)) {
            $profilerConfig['driver'] = array(
                'output' => is_numeric($profilerConfigData) ? 'html' : $profilerConfigData
            );
        } elseif (is_array($profilerConfigData)) {
            $profilerConfig = array_merge($profilerConfig, $profilerConfigData);
        }
        Magento_Profiler::applyConfig($profilerConfig);
    }
}

$definitionsFile = BP . DS . 'var/di/definitions.php';
if (file_exists($definitionsFile)) {
    Mage::initializeObjectManager($definitionsFile);
}
