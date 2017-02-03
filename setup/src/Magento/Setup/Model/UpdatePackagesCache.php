<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model;

use Magento\Framework\Composer\ComposerInformation;
use Composer\Package\Version\VersionParser;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Composer\MagentoComposerApplicationFactory;
use Magento\Setup\Model\DateTime\DateTimeProvider;

/**
 * Class UpdatePackagesCache manages information about available for update packages though the cache file.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class UpdatePackagesCache
{
    /**#@+
     * Composer command
     */
    const COMPOSER_SHOW = 'show';
    /**#@-*/

    /**#@+
     * Composer command params and options
     */
    const PARAM_COMMAND = 'command';
    const PARAM_PACKAGE = 'package';
    const PARAM_AVAILABLE = '--available';
    /**#@-*/

    /**
     * @var \Magento\Composer\MagentoComposerApplication
     */
    private $application;

    /**
     * @var \Magento\Framework\Filesystem\Directory\Write
     */
    private $directory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    private $dateTime;

    /**
     * @var string
     */
    private $pathToCacheFile = 'update_composer_packages.json';

    /**
     * Constructor
     *
     * @param MagentoComposerApplicationFactory $applicationFactory
     * @param \Magento\Framework\Filesystem $filesystem
     * @param ComposerInformation $composerInformation
     * @param DateTime\DateTimeProvider $dateTimeProvider
     */
    public function __construct(
        MagentoComposerApplicationFactory $applicationFactory,
        Filesystem $filesystem,
        ComposerInformation $composerInformation,
        DateTimeProvider $dateTimeProvider
    ) {
        $this->application = $applicationFactory->create();
        $this->directory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        $this->composerInformation = $composerInformation;
        $this->dateTime = $dateTimeProvider->get();
    }

    /**
     * Sync and cache list of available for update versions for packages
     *
     * @return bool
     */
    public function syncPackagesForUpdate()
    {
        $availableVersions = [];
        foreach ($this->composerInformation->getInstalledMagentoPackages() as $package) {
            $latestProductVersion = $this->getLatestNonDevVersion($package['name']);
            if ($latestProductVersion && version_compare($latestProductVersion, $package['version'], '>')) {
                $packageName = $package['name'];
                $availableVersions[$packageName] = [
                    'name' => $packageName,
                    'latestVersion' => $latestProductVersion
                ];
            }
        }
        return $this->savePackagesForUpdateToCache($availableVersions) ? true : false;
    }

    /**
     * Sync and cache list of available for update versions for packages
     *
     * @return bool|array
     */
    public function getPackagesForUpdate()
    {
        $actualUpdatePackages = [];
        $updatePackagesInfo = $this->loadPackagesForUpdateFromCache();
        if (!$updatePackagesInfo) {
            return false;
        }
        $updatePackages = $updatePackagesInfo['packages'];
        $availablePackages = $this->composerInformation->getInstalledMagentoPackages();
        foreach ($updatePackages as $package) {
            $packageName = $package['name'];
            if (array_key_exists($packageName, $availablePackages)) {
                if (version_compare($availablePackages[$packageName]['version'], $package['latestVersion'], '<')) {
                    $actualUpdatePackages[$packageName] = $package;
                }
            }
        }
        $updatePackagesInfo['packages'] = $actualUpdatePackages;
        return $updatePackagesInfo;
    }

    /**
     * Retrieve the latest available stable version for a package
     *
     * @param string $package
     * @return string
     */
    private function getLatestNonDevVersion($package)
    {
        $versionParser = new VersionParser();
        foreach ($this->getPackageAvailableVersions($package) as $version) {
            if ($versionParser->parseStability($version) != 'dev') {
                return $version;
            }
        }
        return '';
    }

    /**
     * Retrieve all available versions for a package
     *
     * @param string $package
     * @return array
     * @throws \RuntimeException
     */
    private function getPackageAvailableVersions($package)
    {
        $versionsPattern = '/^versions\s*\:\s(.+)$/m';

        $commandParams = [
            self::PARAM_COMMAND => self::COMPOSER_SHOW,
            self::PARAM_PACKAGE => $package,
            self::PARAM_AVAILABLE => true
        ];
        $result = $this->application->runComposerCommand($commandParams);
        $matches = [];
        preg_match($versionsPattern, $result, $matches);
        if (!isset($matches[1])) {
            throw new \RuntimeException(
                sprintf('Couldn\'t get available versions for package %s', $commandParams[self::PARAM_PACKAGE])
            );
        }
        return explode(', ', $matches[1]);
    }

    /**
     * Save composer packages available for update to cache
     *
     * @param array $availableVersions
     * @return bool|string
     */
    private function savePackagesForUpdateToCache($availableVersions)
    {
        $syncInfo = [];
        $syncInfo['lastSyncDate'] = $this->dateTime->gmtTimestamp();
        $syncInfo['packages'] = $availableVersions;
        $data = json_encode($syncInfo, JSON_UNESCAPED_SLASHES);
        try {
            $this->directory->writeFile($this->pathToCacheFile, $data);
        } catch (\Magento\Framework\Exception\FileSystemException $e) {
            return false;
        }
        return $data;
    }

    /**
     * Load composer packages available for update from cache
     *
     * @return bool|string
     */
    private function loadPackagesForUpdateFromCache()
    {
        if ($this->directory->isExist($this->pathToCacheFile) && $this->directory->isReadable($this->pathToCacheFile)) {
            try {
                $data = $this->directory->readFile($this->pathToCacheFile);
                return json_decode($data, true);
            } catch (\Magento\Framework\Exception\FileSystemException $e) {
            }
        }
        return false;
    }
}
