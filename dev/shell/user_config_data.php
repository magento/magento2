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
 * @copyright  Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

use Magento\Framework\Shell\ComplexParameter;
use Magento\Store\Model\ScopeInterface;
use Magento\Backend\Model\Config;
use Magento\Framework\App\Bootstrap;

require __DIR__ . '/../../app/bootstrap.php';

$usage = 'Usage: php -f user_config_data.php -- '
    . '--data=<string> - pairs of \'path=value\' separated by \'&\', where '. PHP_EOL
    . '       \'path\' is path of the specified data group, e.g. web/unsecure/base_url, and ' . PHP_EOL
    . '       \'value\' is value for the path specified, e.g. http://127.0.0.1/ ' . PHP_EOL
    . '--bootstrap - add or override parameters of the bootstrap' . PHP_EOL
    . ' NOTE: this tool supports writing data only in global scope ' . PHP_EOL
    . ' Example Usage: php -f user_config_data.php -- '
    . ' --data=' . escapeshellarg('web/seo/use_rewrites=1&web/unsecure/base_url=http://127.0.0.1/') . PHP_EOL;


$opt = getopt('', ['data:']);
if (empty($opt)) {
    echo $usage;
    exit(0);
}

try {
    $dataParam = new ComplexParameter('data');
    $request = $dataParam->mergeFromArgv($_SERVER);
    $bootstrapParam = new ComplexParameter('bootstrap');
    $params = $bootstrapParam->mergeFromArgv($_SERVER, $_SERVER);
    $params[Bootstrap::PARAM_REQUIRE_MAINTENANCE] = null;
    $bootstrap = Bootstrap::create(BP, $params);
    /** @var \Magento\Backend\App\UserConfig $app */
    $app = $bootstrap->createApplication('Magento\Backend\App\UserConfig', ['request' => $request]);
    $bootstrap->run($app);
} catch (\Exception $e) {
    echo $e;
    exit(1);
}
