<?php
/**
 * A CLI tool for managing Magento application caches
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

namespace Magento\Framework\App;

use Magento\Framework\App\Cache\ManagerApp;
use Magento\Framework\Shell\ComplexParameter;

require __DIR__ . '/../../app/bootstrap.php';

$usage = 'Usage: php -f cache.php -- [--' . ManagerApp::KEY_SET . '=1|0]'
    . ' [--' . ManagerApp::KEY_CLEAN . ']'
    . ' [--' . ManagerApp::KEY_FLUSH . ']'
    . ' [--' . ManagerApp::KEY_TYPES . '=<type1>,<type2>,...]'
    . ' [--bootstrap='. escapeshellarg('INIT_PARAM=foo&ANOTHER_PARAM[key]=bar') . ']
    --' . ManagerApp::KEY_TYPES . ' - list of cache types, comma-separated. If omitted, all caches will be affected
    --' . ManagerApp::KEY_SET . ' - enable or disable the specified cache types
    --' . ManagerApp::KEY_CLEAN . ' - clean data of the specified cache types
    --' . ManagerApp::KEY_FLUSH . ' - destroy all data in storage that the specified cache types reside on
    --bootstrap - add or override parameters of the bootstrap' . PHP_EOL;
$longOpts = [
    ManagerApp::KEY_SET . '::',
    ManagerApp::KEY_CLEAN,
    ManagerApp::KEY_FLUSH,
    ManagerApp::KEY_TYPES . '::',
    'bootstrap::'
];
$opt = getopt('', $longOpts);
if (empty($opt)) {
    echo $usage;
}

try {
    $bootstrapParam = new ComplexParameter('bootstrap');
    $params = $bootstrapParam->mergeFromArgv($_SERVER, $_SERVER);
    $params[Bootstrap::PARAM_REQUIRE_MAINTENANCE] = null;
    $bootstrap = Bootstrap::create(BP, $params);
    /** @var ManagerApp $app */
    $app = $bootstrap->createApplication('Magento\Framework\App\Cache\ManagerApp', ['request' => $opt]);
    $bootstrap->run($app);
    echo "Current status:\n";
    var_export($app->getStatusSummary());
    echo "\n";
} catch (\Exception $e) {
    echo $e;
    exit(1);
}
