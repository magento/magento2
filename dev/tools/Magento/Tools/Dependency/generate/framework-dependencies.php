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

require_once __DIR__ . '/bootstrap.php';
use Magento\TestFramework\Utility\Files;
use Magento\Tools\Dependency\ServiceLocator;

try {
    $console = new \Zend_Console_Getopt(array('directory|d=s' => 'Path to base directory for parsing'));
    $console->parse();

    $directory = $console->getOption('directory') ?: BP;

    Files::setInstance(new \Magento\TestFramework\Utility\Files($directory));
    $filesForParse = Files::init()->getFiles(array(Files::init()->getPathToSource() . '/app/code/Magento'), '*');
    $configFiles = Files::init()->getConfigFiles('module.xml', array(), false);

    ServiceLocator::getFrameworkDependenciesReportBuilder()->build(
        array(
            'parse' => array(
                'files_for_parse' => $filesForParse,
                'config_files' => $configFiles,
                'declared_namespaces' => Files::init()->getNamespaces()
            ),
            'write' => array('report_filename' => 'framework-dependencies.csv')
        )
    );

    fwrite(STDOUT, PHP_EOL . 'Report successfully processed.' . PHP_EOL);
} catch (\Zend_Console_Getopt_Exception $e) {
    fwrite(STDERR, $e->getUsageMessage() . PHP_EOL);
    exit(1);
} catch (\Exception $e) {
    fwrite(STDERR, 'Please, check passed path. Dependencies report generator failed: ' . $e->getMessage() . PHP_EOL);
    exit(1);
}
