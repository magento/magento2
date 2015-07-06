<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Composer;

use Composer\Package\Link;
use Composer\Package\PackageInterface;
use Composer\Package\Version\VersionParser;

/**
 * Class ComposerInformation uses Composer to determine dependency information.
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
     * Constructor
     *
     * @param MagentoComposerApplicationFactory $applicationFactory
     * @throws \Exception
     */
    public function __construct(
        MagentoComposerApplicationFactory $applicationFactory
    ) {

        // Create Composer
        $this->application = $applicationFactory->create();
        $this->composer = $this->application->createComposer();
        $this->locker = $this->composer->getLocker();
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
            $requiredPhpVersion =  $allPlatformReqs['php']->getPrettyConstraint();
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
     * @return array
     */
    public function getRootRequiredPackageTypesByNameVersion()
    {
        $packages = [];
        /** @var PackageInterface $package */
        foreach ($this->locker->getLockedRepository()->getPackages() as $package) {
            $packages[$package->getName()] = [
                'name' => $package->getName(),
                'type' => $package->getType(),
                'version' => $package->getVersion()
                ];
        }
        return $packages;
    }

    /**
     * Get list of available for update versions for packages
     *
     * @return array
     */
    public function getPackagesForUpdate()
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
        return $availableVersions;
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
}
