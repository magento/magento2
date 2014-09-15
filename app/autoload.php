<?php
/**
 * Register basic autoloader that uses include path
 *
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
 * @copyright  Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Shortcut constant for the root directory
 */
define('BP', dirname(__DIR__));

$vendorDir = require BP . '/app/etc/vendor_path.php';
$vendorAutoload = BP . "/{$vendorDir}/autoload.php";
if (file_exists($vendorAutoload)) {
    require_once $vendorAutoload;
}
require_once BP . '/lib/internal/Magento/Framework/Autoload/IncludePath.php';
$includePath = new \Magento\Framework\Autoload\IncludePath();
$includePath->addIncludePath([BP . '/app/code', BP . '/lib/internal']);
spl_autoload_register([$includePath, 'load']);
$classMapPath = BP . '/var/classmap.ser';
if (file_exists($classMapPath)) {
    require_once BP . '/lib/internal/Magento/Framework/Autoload/ClassMap.php';
    $classMap = new \Magento\Framework\Autoload\ClassMap(BP);
    $classMap->addMap(unserialize(file_get_contents($classMapPath)));
    spl_autoload_register(array($classMap, 'load'), true, true);
}
