<?php
/**
 * A script for deploying static view files for Magento system "production mode"
 *
 * The resulting files will be recorded into pub/static directory.
 * They can be used not only by the server where Magento instance is,
 * but also can be copied to a CDN, and the Magento instance may be configured to generate base URL to the CDN.
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
$baseName = basename(__FILE__);
$options = getopt('', array('langs::', 'dry-run', 'verbose::', 'help'));
define('USAGE', "USAGE:\n\tphp -f {$baseName} -- [--langs=en_US,de_DE,...] [--verbose=0|1] [--dry-run] [--help]\n");
require __DIR__ . '/../../../../../app/bootstrap.php';
$autoloader = new \Magento\Framework\Autoload\IncludePath();
$autoloader->addIncludePath([BP . '/dev/tests/static/framework', realpath(__DIR__ . '/../../..')]);

// parse all options
if (isset($options['help'])) {
    echo USAGE;
    exit(0);
}
$langs = ['en_US'];
if (isset($options['langs'])) {
    $langs = explode(',', $options['langs']);
    foreach ($langs as $lang) {
        if (!preg_match('/^[a-z]{2}_[A-Z]{2}$/', $lang)) {
            echo USAGE;
            exit(1);
        }
    }
}
$isDryRun = isset($options['dry-run']);
$verbosity = \Magento\Tools\View\Deployer\Log::ERROR;
if (isset($options['verbose'])) {
    $verbosity = 0 === (int)$options['verbose'] ? \Magento\Tools\View\Deployer\Log::SILENT
        : \Magento\Tools\View\Deployer\Log::ERROR | \Magento\Tools\View\Deployer\Log::DEBUG;
}

// run the deployment logic
$filesUtil = new \Magento\TestFramework\Utility\Files(BP);
$omFactory = new \Magento\Framework\App\ObjectManagerFactory();
$objectManager = $omFactory->create(
    BP,
    [\Magento\Framework\App\State::PARAM_MODE => \Magento\Framework\App\State::MODE_DEFAULT]
);
$logger = new \Magento\Tools\View\Deployer\Log($verbosity);
/** @var \Magento\Tools\View\Deployer $deployer */
$deployer = $objectManager->create(
    'Magento\Tools\View\Deployer',
    ['filesUtil' => $filesUtil, 'logger' => $logger, 'isDryRun' => $isDryRun]
);
$deployer->deploy(BP, $omFactory, $langs);
exit(0);
