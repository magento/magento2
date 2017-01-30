<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Composer;

use Composer\Package\Link;
use Composer\Package\CompletePackageInterface;

/**
 * Class ComposerInformation uses Composer to determine dependency information.
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
     * @throws \Exception
     */
    public function __construct(
        MagentoComposerApplicationFactory $applicationFactory
    ) {
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
     * Determines if Magento is the root package or it is included as a requirement.
     *
     * @return bool
     */
    private function isMagentoRoot()
    {
        $rootPackage = $this->composer->getPackage();

        return preg_match('/magento\/magento2...?/', $rootPackage->getName());
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

    /**
     * @return array
     */
    public function getPackagesTypes()
    {
        return self::$packageTypes;
    }

    /**
     * @param string $name
     * @param string $version
     * @return array
     */
    public function getPackageRequirements($name, $version)
    {
        $package = $this->composer->getRepositoryManager()->findPackage($name, $version);
        return $package->getRequires();
    }
}
