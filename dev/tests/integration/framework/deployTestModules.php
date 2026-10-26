<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
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
$deployedTestModuleRootNames = [];

// Ensure app/code/Magento/ exists before vendor-scanning: in Composer builds the directory
// is absent until the committed-modules loop below creates it, causing is_dir() to bail early.
if (!is_dir($pathToInstalledMagentoInstanceModules)) {
    mkdir($pathToInstalledMagentoInstanceModules, 0755, true);
}

$appCodeDir = dirname($pathToInstalledMagentoInstanceModules);
foreach (findModuleLevelTestModuleFixtureDirectories($appCodeDir) as $testModuleSourceDir) {
    copyTestModuleTreeIntoMagentoCode($testModuleSourceDir, $pathToInstalledMagentoInstanceModules);
    $deployedTestModuleRootNames[basename($testModuleSourceDir)] = true;
}

if (is_dir($pathToCommittedTestModules)) {
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator(
            $pathToCommittedTestModules,
            RecursiveDirectoryIterator::FOLLOW_SYMLINKS
        )
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
            $trimmedRelative = ltrim(str_replace('\\', '/', $relativePath), '/');
            $firstSlashPos = strpos($trimmedRelative, '/');
            $firstSegment = $firstSlashPos === false
                ? $trimmedRelative
                : substr($trimmedRelative, 0, $firstSlashPos);
            if ($firstSegment !== '') {
                $deployedTestModuleRootNames[$firstSegment] = true;
            }
        }
    }
    unset($iterator, $file);
}

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
        array_keys($deployedTestModuleRootNames),
        $pathToInstalledMagentoInstanceModules
    );
}

/**
 * Discover module-level test fixture dirs: .../Test/_files/Magento/<Name> (direct child only).
 *
 * Uses shallow glob patterns under app/code (Magento packages and Vendor/Module) and under
 * vendor/magento (Composer path packages) so test fixtures are found when modules are not
 * copied into app/code.
 *
 * @param string $appCodeDir Absolute path to app/code
 * @return string[] List of absolute paths to each test module root directory
 */
function findModuleLevelTestModuleFixtureDirectories(string $appCodeDir): array
{
    if (!is_dir($appCodeDir)) {
        return [];
    }

    $found = [];
    $patterns = [
        $appCodeDir . '/Magento/*/Test/_files/Magento/*',
        $appCodeDir . '/*/*/Test/_files/Magento/*',
    ];
    $vendorMagentoDir = dirname($appCodeDir, 2) . '/vendor/magento';
    if (is_dir($vendorMagentoDir)) {
        $patterns[] = $vendorMagentoDir . '/module-*/Test/_files/Magento/*';
    }
    foreach ($patterns as $pattern) {
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        $matches = glob($pattern, GLOB_NOSORT | GLOB_ONLYDIR);
        if ($matches === false) {
            throw new \RuntimeException('glob() returned error while searching in \'' . $pattern . '\'');
        }
        foreach ($matches as $dir) {
            $found[$dir] = true;
        }
    }

    return array_keys($found);
}

/**
 * Copy a single test module directory into app/code/Magento/<moduleName>/...
 *
 * @param string $testModuleSourceDir Absolute path to .../Test/_files/Magento/<ModuleName>
 * @param string $pathToInstalledMagentoInstanceModules Absolute path to app/code/Magento
 * @return void
 */
function copyTestModuleTreeIntoMagentoCode(
    string $testModuleSourceDir,
    string $pathToInstalledMagentoInstanceModules
): void {
    $committedBase = dirname($testModuleSourceDir);
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator(
            $testModuleSourceDir,
            RecursiveDirectoryIterator::FOLLOW_SYMLINKS
        )
    );
    /** @var SplFileInfo $file */
    foreach ($iterator as $file) {
        if (!$file->isDir()) {
            $source = $file->getPathname();
            $relativePath = substr($source, strlen($committedBase));
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
}

/**
 * Delete all test module directories which have been deployed into app/code/Magento
 *
 * @param array $rootDirNames Top-level directory names under app/code/Magento to remove
 * @param string $pathToInstalledMagentoInstanceModules Absolute path to app/code/Magento
 * @return void
 */
function deleteTestModules(array $rootDirNames, string $pathToInstalledMagentoInstanceModules)
{
    $filesystem = new \Symfony\Component\Filesystem\Filesystem();
    foreach ($rootDirNames as $name) {
        if ($name === '.' || $name === '..') {
            continue;
        }
        $targetDirPath = $pathToInstalledMagentoInstanceModules . '/' . $name;
        $filesystem->remove($targetDirPath);
    }
}
