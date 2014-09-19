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
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

$autoload = __DIR__ . '/vendor/autoload.php';

if (!file_exists($autoload)) {
    if (PHP_SAPI == 'cli') {
        echo "Dependencies not installed. Please run 'composer install' under /setup directory.\n";
    } else {
        echo <<<HTML
<div style="font:12px/1.35em arial, helvetica, sans-serif;">
    <div style="margin:0 0 25px 0; border-bottom:1px solid #ccc;">
        <h3 style="margin:0;font-size:1.7em;font-weight:normal;text-transform:none;text-align:left;color:#2f2f2f;">
        Whoops, it looks like setup tool dependencies are not installed.</h3>
    </div>
    <p>Please run 'composer install' under /setup directory.</p>
</div>
HTML;
    }
    exit(1);
}

require $autoload;

use Zend\Mvc\Service\ServiceManagerConfig;
use Zend\ServiceManager\ServiceManager;

$configuration = include "config/application.config.php";

$smConfig = new ServiceManagerConfig();
$serviceManager = new ServiceManager($smConfig);
$serviceManager->setService('ApplicationConfig', $configuration);

$serviceManager->setAllowOverride(true);
$serviceManager->get('ModuleManager')->loadModules();
$serviceManager->setAllowOverride(false);

$serviceManager->get('Application')
    ->bootstrap()
    ->run();