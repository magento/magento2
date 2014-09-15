<?php
/**
 * Uninstall utility
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

define('USAGE', "Usage: php -f uninstall.php -- [--bootstrap=<json>]\n");
$opt = getopt('', ['bootstrap::']);

require __DIR__ . '/../../app/bootstrap.php';
try {
    $params = $_SERVER;
    if (isset($opt['bootstrap'])) {
        $extra = json_decode($opt['bootstrap'], true);
        if (!is_array($extra)) {
            throw new \Exception("Unable to decode JSON in the parameter 'bootstrap'");
        }
        $params = array_replace_recursive($params, $extra);
    }
    $bootstrap = \Magento\Framework\App\Bootstrap::create(BP, $params);
    $log = new  \Zend_Log(new \Zend_Log_Writer_Stream('php://stdout'));
    /** @var \Magento\Install\Model\Uninstaller $uninstall */
    $uninstall = $bootstrap->getObjectManager()->create('\Magento\Install\Model\Uninstaller', ['log' => $log]);
    $uninstall->uninstall();
    exit(0);
} catch (\Exception $e) {
    echo $e;
    exit(1);
}
