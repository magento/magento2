<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Composer;

use Composer\Factory as ComposerFactory;
use Composer\Package\Link;
use Composer\Package\PackageInterface;
use Composer\Package\Version\VersionParser;
use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Class ComposerInformation uses Composer to determine dependency information.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ComposerInformation
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
     * @var \Composer\Composer
     */
    private $composer;

    /**
     * @var \Composer\Package\Locker
     */
    private $locker;

    /**
     * @var \Magento\Framework\Filesystem\Directory\Write
     */
    private $directory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    private $dateTime;

    /**
     * @var string
     */
    private $pathToCacheFile = 'update_composer_packages.json';

    /** @var array */
    private static $availableComponentTypesList = ['magento2-theme', 'magento2-language', 'magento2-module'];

    /** @var array */
    private static $componentTypesForSystemUpgrade = [
        'magento2-theme',
        'magento2-language',
        'magento2-module',
        'magento2-library',
        'magento2-component'
    ];

    /**
     * Constructor
     *
     * @param MagentoComposerApplicationFactory $applicationFactory
     * @param \Magento\Framework\Filesystem $filesystem
     * @param BufferIoFactory $bufferIoFactory
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @throws \Exception
     */
    public function __construct(
        MagentoComposerApplicationFactory $applicationFactory,
        \Magento\Framework\Filesystem $filesystem,
        BufferIoFactory $bufferIoFactory,
        \Magento\Framework\Stdlib\DateTime $dateTime
    ) {
        // composer.json is in same directory as vendor
        $vendorPath = $filesystem->getDirectoryRead(DirectoryList::CONFIG)->getAbsolutePath('vendor_path.php');
        $vendorDir = require "{$vendorPath}";
        $composerJson = $filesystem->getDirectoryRead(DirectoryList::ROOT)->getAbsolutePath()
            . "/{$vendorDir}/../composer.json";

        $composerJsonRealPath = realpath($composerJson);
        if ($composerJsonRealPath === false) {
            throw new \Exception('Composer file not found: ' . $composerJson);
        }

        putenv('COMPOSER_HOME=' . $filesystem->getDirectoryRead(DirectoryList::COMPOSER_HOME)->getAbsolutePath());

        // Create Composer
        $this->application = $applicationFactory->create();
        $this->composer = ComposerFactory::create($bufferIoFactory->create(), $composerJson);
        $this->locker = $this->composer->getLocker();
        $this->directory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        $this->dateTime = $dateTime;
    }

    /**
     * Retrieves required php version
     *
     * @return string
     * @throws \Exception If attributes are missing in composer.lock file.
     */
    public function getRequiredPhpVersion()
    {
        if ($this->isMagentoRoot()) {
            $allPlatformReqs = $this->locker->getPlatformRequirements(true);
            $requiredPhpVersion = $allPlatformReqs['php']->getPrettyConstraint();
        } else {
            $packages = $this->locker->getLockedRepository()->getPackages();
            /** @var PackageInterface $package */
            foreach ($packages as $package) {
                if ($package instanceof PackageInterface) {
                    $packageName = $package->getPrettyName();
                    if ($packageName === 'magento/product-community-edition') {
                        $phpRequirementLink = $package->getRequires()['php'];
                        if ($phpRequirementLink instanceof Link) {
                            $requiredPhpVersion = $phpRequirementLink->getPrettyConstraint();
                        }
                    }
                }
            }
        }

        if (!isset($requiredPhpVersion)) {
            throw new \Exception('Cannot find php version requirement in \'composer.lock\' file');
        }
        return $requiredPhpVersion;
    }

    /**
     * Retrieve list of required extensions
     *
     * Collect required extensions from composer.lock file
     *
     * @return array
     * @throws \Exception If attributes are missing in composer.lock file.
     */
    public function getRequiredExtensions()
    {
        $requiredExtensions = [];
        $allPlatformReqs = array_keys($this->locker->getPlatformRequirements(true));

        if (!$this->isMagentoRoot()) {
            /** @var \Composer\Package\CompletePackage $package */
            foreach ($this->locker->getLockedRepository()->getPackages() as $package) {
                $requires = array_keys($package->getRequires());
                $requires = array_merge($requires, array_keys($package->getDevRequires()));
                $allPlatformReqs = array_merge($allPlatformReqs, $requires);
            }
        }
        foreach ($allPlatformReqs as $reqIndex) {
            if (substr($reqIndex, 0, 4) === 'ext-') {
                $requiredExtensions[] = substr($reqIndex, 4);
            }
        }
        return array_unique($requiredExtensions);
    }

    /**
     * Collect required packages from root composer.lock file
     *
     * @return array
     */
    public function getRootRequiredPackages()
    {
        $packages = [];
        /** @var PackageInterface $package */
        foreach ($this->locker->getLockedRepository()->getPackages() as $package) {
            $packages[] = $package->getName();
        }
        return $packages;
    }

    /**
     * Collect required packages and types from root composer.lock file
     *
     * @return array
     */
    public function getRootRequiredPackageTypesByName()
    {
        $packages = [];
        /** @var PackageInterface $package */
        foreach ($this->locker->getLockedRepository()->getPackages() as $package) {
            $packages[$package->getName()] = $package->getType();
        }
        return $packages;
    }

    /**
     * Collect required packages and types from root composer.lock file
     *
     * @param string $from
     * @return array
     */
    public function getRootRequiredPackageTypesByNameVersion($from = 'updater')
    {
        $packages = [];
        if ($from === 'upgrader') {
            $types = self::$componentTypesForSystemUpgrade;
        } else {
            $types = self::$availableComponentTypesList;
        }
        /** @var PackageInterface $package */
        foreach ($this->locker->getLockedRepository()->getPackages() as $package) {
            if (in_array($package->getType(), $types)) {
                $packages[$package->getName()] = [
                    'name' => $package->getName(),
                    'type' => $package->getType(),
                    'version' => $package->getPrettyVersion()
                ];
            }
        }
        return $packages;
    }

    /**
     * Sync and cache list of available for update versions for packages
     *
     * @return bool
     */
    public function syncPackagesForUpdate()
    {
        $availableVersions = [];
        foreach ($this->getRootRequiredPackageTypesByNameVersion() as $package) {
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
        $availablePackages = $this->getRootRequiredPackageTypesByNameVersion();
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
                return $versionParser->normalize($version);
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
     * Determines if Magento is the root package or it is included as a requirement.
     *
     * @return bool
     */
    private function isMagentoRoot()
    {
        $rootPackage = $this->composer->getPackage();

        return preg_match('/magento\/magento2.e/', $rootPackage->getName());
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
        $syncInfo['lastSyncDate'] = $this->dateTime->formatDate(true);
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
