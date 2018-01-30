<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @var $testFrameworkDir string - Must be defined in parent script.
 */

/**
 * Copy test modules to app/code/Magento to make them visible for Magento instance.
 */
$pathToCommittedTestModules = $testFrameworkDir . '/../_files/Magento';
$pathToInstalledMagentoInstanceModules = $testFrameworkDir . '/../../../../app/code/Magento';
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($pathToCommittedTestModules, RecursiveDirectoryIterator::FOLLOW_SYMLINKS)
);

//collect test modules dirs name
$testModuleNames = array_diff(scandir($pathToCommittedTestModules), ['..', '.']);

//remove test modules from magento codebase
foreach ($testModuleNames as $name) {
    $folder = $pathToInstalledMagentoInstanceModules . '/' . $name;
    if (is_dir($folder)) {
        \Magento\Framework\Filesystem\Io\File::rmdirRecursive($folder);
    }
}
