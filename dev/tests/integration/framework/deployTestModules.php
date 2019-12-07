<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * phpcs:disable PSR1.Files.SideEffects
 * phpcs:disable Squiz.Functions.GlobalFunction
 * @var string $testFrameworkDir - Must be defined in parent script.
 * @var \Magento\TestFramework\Bootstrap\Settings $settings - Must be defined in parent script.
 */

/** Copy test modules to app/code/Magento to make them visible for Magento instance */
$pathToCommittedTestModules = $testFrameworkDir . '/../_files/Magento';
$pathToInstalledMagentoInstanceModules = $testFrameworkDir . '/../../../../app/code/Magento';
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($pathToCommittedTestModules, RecursiveDirectoryIterator::FOLLOW_SYMLINKS)
);
/** @var SplFileInfo $file */
foreach ($iterator as $file) {
    if (!$file->isDir()) {
        $source = $file->getPathname();
        $relativePath = substr($source, strlen($pathToCommittedTestModules));
        $destination = $pathToInstalledMagentoInstanceModules . $relativePath;
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        $targetDir = dirname($destination);
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        if (!is_dir($targetDir)) {
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            mkdir($targetDir, 0755, true);
        }
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        copy($source, $destination);
    }
}
unset($iterator, $file);

// Register the modules under '_files/'
$pathPattern = $pathToInstalledMagentoInstanceModules . '/TestModule*/registration.php';
// phpcs:ignore Magento2.Functions.DiscouragedFunction
$files = glob($pathPattern, GLOB_NOSORT);
if ($files === false) {
    throw new \RuntimeException('glob() returned error while searching in \'' . $pathPattern . '\'');
}
foreach ($files as $file) {
    // phpcs:ignore Magento2.Security.IncludeFile
    include $file;
}

if ((int)$settings->get('TESTS_PARALLEL_RUN') !== 1) {
    // Only delete modules if we are not using parallel executions
    // phpcs:ignore Magento2.Functions.DiscouragedFunction
    register_shutdown_function(
        'deleteTestModules',
        $pathToCommittedTestModules,
        $pathToInstalledMagentoInstanceModules
    );
}

/**
 * Delete all test module directories which have been created before
 *
 * @param string $pathToCommittedTestModules
 * @param string $pathToInstalledMagentoInstanceModules
 */
function deleteTestModules($pathToCommittedTestModules, $pathToInstalledMagentoInstanceModules)
{
    $filesystem = new \Symfony\Component\Filesystem\Filesystem();
    $iterator = new DirectoryIterator($pathToCommittedTestModules);
    /** @var SplFileInfo $file */
    foreach ($iterator as $file) {
        if ($file->isDir() && !in_array($file->getFilename(), ['.', '..'])) {
            $targetDirPath = $pathToInstalledMagentoInstanceModules . '/' . $file->getFilename();
            $filesystem->remove($targetDirPath);
        }
    }
    unset($iterator, $file);
}
