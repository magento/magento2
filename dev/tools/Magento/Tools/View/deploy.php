<?php
/**
 * A script for deploying static view files for Magento system "production mode"
 *
 * The resulting files will be recorded into pub/static directory.
 * They can be used not only by the server where Magento instance is,
 * but also can be copied to a CDN, and the Magento instance may be configured to generate base URL to the CDN.
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
use Magento\Framework\Autoload\AutoloaderRegistry;

$baseName = basename(__FILE__);
$options = getopt('', ['langs::', 'dry-run', 'verbose::', 'help']);
define('USAGE', "USAGE:\n\tphp -f {$baseName} -- [--langs=en_US,de_DE,...] [--verbose=0|1] [--dry-run] [--help]\n");
require __DIR__ . '/../../../../../app/bootstrap.php';

AutoloaderRegistry::getAutoloader()->addPsr4(
    'Magento\\',
    [BP . '/dev/tests/static/framework/Magento/', realpath(__DIR__ . '/../../../Magento/')]
);

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
$filesUtil = new \Magento\Framework\Test\Utility\Files(BP);
$omFactory = \Magento\Framework\App\Bootstrap::createObjectManagerFactory(BP, []);
$objectManager = $omFactory->create(
    [\Magento\Framework\App\State::PARAM_MODE => \Magento\Framework\App\State::MODE_DEFAULT]
);
$logger = new \Magento\Tools\View\Deployer\Log($verbosity);
/** @var \Magento\Tools\View\Deployer $deployer */
$deployer = $objectManager->create(
    'Magento\Tools\View\Deployer',
    ['filesUtil' => $filesUtil, 'logger' => $logger, 'isDryRun' => $isDryRun]
);
$deployer->deploy($omFactory, $langs);
exit(0);
