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
$testModuleLockDir = $testFrameworkDir . '/../tmp';
if (!is_dir($testModuleLockDir)) {
    // phpcs:ignore Magento2.Functions.DiscouragedFunction
    mkdir($testModuleLockDir, 0755, true);
}
// Keep the lock file in place so all processes lock the same inode.
$testModuleLock = fopen($testModuleLockDir . '/test-modules.lock', 'c');
if ($testModuleLock === false) {
    throw new \RuntimeException('Cannot open the test module deployment lock.');
}
if (!flock($testModuleLock, LOCK_EX | LOCK_NB) && !flock($testModuleLock, LOCK_SH)) {
    fclose($testModuleLock);
    throw new \RuntimeException('Cannot lock test module deployment.');
}
$keepTestModules = (int)$settings->get('TESTS_PARALLEL_RUN') === 1;
register_shutdown_function(
    static function () use (
        &$deployedTestModuleRootNames,
        $pathToInstalledMagentoInstanceModules,
        $testModuleLock,
        $keepTestModules
    ): void {
        releaseTestModuleLock(
            $testModuleLock,
            array_keys($deployedTestModuleRootNames),
            $pathToInstalledMagentoInstanceModules,
            $keepTestModules
        );
    }
);

// Ensure app/code/Magento/ exists before vendor-scanning: in Composer builds the directory
// is absent until the committed-modules loop below creates it, causing is_dir() to bail early.
if (!is_dir($pathToInstalledMagentoInstanceModules)) {
    mkdir($pathToInstalledMagentoInstanceModules, 0755, true);
}

$appCodeDir = dirname($pathToInstalledMagentoInstanceModules);
$testModuleSourceDirs = findModuleLevelTestModuleFixtureDirectories($appCodeDir);
$testModuleSourceRoots = array_fill_keys($testModuleSourceDirs, $pathToInstalledMagentoInstanceModules);
if (is_dir($pathToCommittedTestModules)) {
    $testModuleSourceRoots[$pathToCommittedTestModules] = $appCodeDir;
}
$testModuleDeploymentRequired = lockTestModuleDeploymentForWriting($testModuleSourceRoots, $testModuleLock);
foreach ($testModuleSourceDirs as $testModuleSourceDir) {
    if ($testModuleDeploymentRequired) {
        copyTestModuleTreeIntoMagentoCode($testModuleSourceDir, $pathToInstalledMagentoInstanceModules);
    }
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
            if ($testModuleDeploymentRequired) {
                copyTestModuleFile($source, $destination);
            }
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

if (!flock($testModuleLock, LOCK_SH)) {
    throw new \RuntimeException('Cannot retain the test module deployment lock.');
}
unset(
    $testModuleLock,
    $testModuleLockDir,
    $keepTestModules,
    $testModuleSourceDirs,
    $testModuleSourceRoots,
    $testModuleDeploymentRequired
);

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
            copyTestModuleFile($source, $destination);
        }
    }
    unset($iterator, $file);
}

/**
 * Acquire exclusive access before copying if any deployed file differs.
 *
 * @param array $sourceRoots Source directories mapped to destination parent directories
 * @param resource $testModuleLock Deployment lock handle
 * @return bool
 */
function lockTestModuleDeploymentForWriting(array $sourceRoots, $testModuleLock): bool
{
    foreach ($sourceRoots as $sourceRoot => $destinationRoot) {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($sourceRoot, RecursiveDirectoryIterator::FOLLOW_SYMLINKS)
        );
        foreach ($iterator as $file) {
            if ($file->isDir()) {
                continue;
            }
            $source = $file->getPathname();
            $destination = $destinationRoot . substr($source, strlen(dirname($sourceRoot)));
            if (!is_file($destination) || hash_file('sha256', $source) !== hash_file('sha256', $destination)) {
                // Check before copying: upgrading may release the shared lock and allow cleanup by another process.
                if (!flock($testModuleLock, LOCK_EX)) {
                    throw new \RuntimeException('Cannot lock test module deployment for writing.');
                }
                clearstatcache();
                return true;
            }
        }
    }
    return false;
}

/**
 * Publish changed files atomically while holding the deployment lock exclusively.
 *
 * @param string $source
 * @param string $destination
 */
function copyTestModuleFile(string $source, string $destination): void
{
    if (is_file($destination) && hash_file('sha256', $source) === hash_file('sha256', $destination)) {
        return;
    }
    // phpcs:ignore Magento2.Functions.DiscouragedFunction
    $targetDir = dirname($destination);
    // phpcs:ignore Magento2.Functions.DiscouragedFunction
    if (!is_dir($targetDir)) {
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        mkdir($targetDir, 0755, true);
    }
    $permissions = is_file($destination) ? fileperms($destination) & 0777 : 0666 & ~umask();
    $temporaryFile = tempnam($targetDir, '.test-module-');
    if ($temporaryFile === false) {
        throw new \RuntimeException('Cannot create a temporary test module file.');
    }
    publishTestModuleFile($source, $temporaryFile, $destination, $permissions);
}

/**
 * Copy, set permissions and atomically publish a temporary test module file.
 *
 * @param string $source Source test module file
 * @param string $temporaryFile Temporary file in the destination directory
 * @param string $destination Destination test module file
 * @param int $permissions File permissions to preserve
 * @return void
 */
function publishTestModuleFile(string $source, string $temporaryFile, string $destination, int $permissions): void
{
    try {
        if (!copy($source, $temporaryFile)
            || !chmod($temporaryFile, $permissions)
            || !rename($temporaryFile, $destination)
        ) {
            throw new \RuntimeException('Cannot deploy test module file: ' . $destination);
        }
    } finally {
        if (is_file($temporaryFile)) {
            unlink($temporaryFile);
        }
    }
}

/**
 * Remove deployed modules only after the last reader releases its lock.
 *
 * @param resource $testModuleLock
 * @param array $rootDirNames Top-level directory names under app/code/Magento to remove
 * @param string $pathToInstalledMagentoInstanceModules Absolute path to app/code/Magento
 * @param bool $keepTestModules
 * @return void
 */
function releaseTestModuleLock(
    $testModuleLock,
    array $rootDirNames,
    string $pathToInstalledMagentoInstanceModules,
    bool $keepTestModules
): void {
    try {
        flock($testModuleLock, LOCK_UN);
        if (!$keepTestModules && flock($testModuleLock, LOCK_EX | LOCK_NB)) {
            deleteTestModules($rootDirNames, $pathToInstalledMagentoInstanceModules);
        }
    } finally {
        fclose($testModuleLock);
    }
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
