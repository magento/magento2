<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Composer;

use Composer\Factory as ComposerFactory;
use Composer\Package\Link;
use Composer\Package\CompletePackageInterface;
use Composer\Package\Version\VersionParser;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Stdlib\DateTime;

/**
 * Class ComposerInformation uses Composer to determine dependency information.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ComposerInformation
{
    /**
     * Magento2 theme type
     */
    const THEME_PACKAGE_TYPE = 'magento2-theme';

    /**
     * Magento2 module type
     */
    const MODULE_PACKAGE_TYPE = 'magento2-module';

    /**
     * Magento2 language type
     */
    const LANGUAGE_PACKAGE_TYPE = 'magento2-language';

    /**
     * Magento2 metapackage type
     */
    const METAPACKAGE_PACKAGE_TYPE = 'metapackage';

    /**
     * Magento2 library type
     */
    const LIBRARY_PACKAGE_TYPE = 'magento2-library';

    /**
     * Magento2 component type
     */
    const COMPONENT_PACKAGE_TYPE = 'magento2-component';

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
    private static $packageTypes = [
        self::THEME_PACKAGE_TYPE,
        self::LANGUAGE_PACKAGE_TYPE,
        self::MODULE_PACKAGE_TYPE,
        self::LIBRARY_PACKAGE_TYPE,
        self::COMPONENT_PACKAGE_TYPE,
        self::METAPACKAGE_PACKAGE_TYPE
    ];

    /**
     * Constructor
     *
     * @param MagentoComposerApplicationFactory $applicationFactory
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @throws \Exception
     */
    public function __construct(
        MagentoComposerApplicationFactory $applicationFactory,
        Filesystem $filesystem,
        DateTime $dateTime
    ) {
        $this->application = $applicationFactory->create();
        $this->composer = $this->application->createComposer();
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
            /** @var CompletePackageInterface $package */
            foreach ($packages as $package) {
                if ($package instanceof CompletePackageInterface) {
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
            /** @var CompletePackageInterface $package */
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
     * Retrieve list of suggested extensions
     *
     * Collect suggests from composer.lock file and modules composer.json files
     *
     * @return array
     */
    public function getSuggestedPackages()
    {
        $suggests = [];
        /** @var \Composer\Package\CompletePackage $package */
        foreach ($this->locker->getLockedRepository()->getPackages() as $package) {
            $suggests += $package->getSuggests();
        }

        return array_unique($suggests);
    }

    /**
     * Collect required packages from root composer.lock file
     *
     * @return array
     */
    public function getRootRequiredPackages()
    {
        $packages = [];
        /** @var CompletePackageInterface $package */
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
        /** @var CompletePackageInterface $package */
        foreach ($this->locker->getLockedRepository()->getPackages() as $package) {
            $packages[$package->getName()] = $package->getType();
        }
        return $packages;
    }

    /**
     * Collect all installed Magento packages from composer.lock
     *
     * @return array
     */
    public function getInstalledMagentoPackages()
    {
        $packages = [];
        /** @var CompletePackageInterface $package */
        foreach ($this->locker->getLockedRepository()->getPackages() as $package) {
            if ((in_array($package->getType(), self::$packageTypes))
                && (!$this->isSystemPackage($package->getPrettyName()))) {
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
        foreach ($this->getInstalledMagentoPackages() as $package) {
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
        $availablePackages = $this->getInstalledMagentoPackages();
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
     * Checks if the passed packaged is system package
     *
     * @param string $packageName
     * @return bool
     */
    public function isSystemPackage($packageName = '')
    {
        if (preg_match('/magento\/product-*/', $packageName) == 1) {
            return true;
        }
        return false;
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
        $syncInfo['lastSyncDate'] = str_replace('-', '/', $this->dateTime->formatDate(true));
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

    /**
     * Check if a package is inside the root composer or not
     *
     * @param string $packageName
     * @return bool
     */
    public function isPackageInComposerJson($packageName)
    {
        return (in_array($packageName, array_keys($this->composer->getPackage()->getRequires()))
            || in_array($packageName, array_keys($this->composer->getPackage()->getDevRequires()))
        );
    }
}
