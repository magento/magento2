<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

require_once __DIR__ . '/bootstrap.php';
use Magento\Framework\Test\Utility\Files;
use Magento\Tools\Dependency\ServiceLocator;

try {
    $console = new \Zend_Console_Getopt(['directory|d=s' => 'Path to base directory for parsing']);
    $console->parse();

    $directory = $console->getOption('directory') ?: BP;

    Files::setInstance(new \Magento\Framework\Test\Utility\Files($directory));
    $filesForParse = Files::init()->getFiles([Files::init()->getPathToSource() . '/app/code/Magento'], '*');
    $configFiles = Files::init()->getConfigFiles('module.xml', [], false);

    ServiceLocator::getFrameworkDependenciesReportBuilder()->build(
        [
            'parse' => [
                'files_for_parse' => $filesForParse,
                'config_files' => $configFiles,
                'declared_namespaces' => Files::init()->getNamespaces(),
            ],
            'write' => ['report_filename' => 'framework-dependencies.csv'],
        ]
    );

    fwrite(STDOUT, PHP_EOL . 'Report successfully processed.' . PHP_EOL);
} catch (\Zend_Console_Getopt_Exception $e) {
    fwrite(STDERR, $e->getUsageMessage() . PHP_EOL);
    exit(1);
} catch (\Exception $e) {
    fwrite(STDERR, 'Please, check passed path. Dependencies report generator failed: ' . $e->getMessage() . PHP_EOL);
    exit(1);
}
