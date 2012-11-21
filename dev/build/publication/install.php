#!/usr/bin/php
<?php
/**
 * Magento install script
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
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

define('SYNOPSIS', <<<SYNOPSIS
php -f install.php -- --build_properties_file "<path_to_file>"

SYNOPSIS
);

/**
 * Parse command line arguments
 */
$currentArgName = false;
$args = array();
foreach ($_SERVER['argv'] as $argNameOrValue) {
    if (substr($argNameOrValue, 0, 2) == '--') {
        // argument name
        $currentArgName = substr($argNameOrValue, 2);
        // in case if argument doesn't need a value
        $args[$currentArgName] = true;
    } else {
        // argument value
        if ($currentArgName) {
            $args[$currentArgName] = $argNameOrValue;
        }
        $currentArgName = false;
    }
}

if (!isset($args['build_properties_file'])) {
    echo SYNOPSIS;
    exit(1);
}
$baseDir = realpath(__DIR__ . '/../../../');
$configFile = $args['build_properties_file'];
$configFile = file_exists($configFile) ? $configFile : "$configFile.dist";
$config = require($configFile);
$installOptions = isset($config['install_options']) ? $config['install_options'] : array();

$reportDir = __DIR__ . '/' . $config['report_dir'];

/* Install application */
if ($installOptions) {
    $installCmd = sprintf('php -f %s --', escapeshellarg("$baseDir/dev/shell/install.php"));
    foreach ($installOptions as $optionName => $optionValue) {
        $installCmd .= sprintf(' --%s %s', $optionName, escapeshellarg($optionValue));
    }

    passthru($installCmd, $exitCode);
    if ($exitCode) {
        exit($exitCode);
    }
}

/* Initialize Magento application */
require_once __DIR__ . '/../../../app/bootstrap.php';
Mage::app();

/* Clean reports */
Varien_Io_File::rmdirRecursive($reportDir);

/* Run all indexer processes */
/** @var $indexer Mage_Index_Model_Indexer */
$indexer = Mage::getModel('Mage_Index_Model_Indexer');
/** @var $process Mage_Index_Model_Process */
foreach ($indexer->getProcessesCollection() as $process) {
    if ($process->getIndexer()->isVisible()) {
        $process->reindexEverything();
    }
}
