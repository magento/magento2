<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Composer;

use Composer\Package\Link;
use Composer\Package\CompletePackageInterface;
use Magento\Framework\App\ObjectManager;

/**
 * Class ComposerInformation uses Composer to determine dependency information.
 * @since 2.0.0
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

    /**
     * Default composer repository key
     */
    const COMPOSER_DEFAULT_REPO_KEY = 'packagist.org';

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
     * @var \Composer\Composer
     */
    private $composer;

    /**
     * @var \Composer\Package\Locker
     * @since 2.0.0
     */
    private $locker;

    /**
     * @var array
     * @since 2.0.0
     */
    private static $packageTypes = [
        self::THEME_PACKAGE_TYPE,
        self::LANGUAGE_PACKAGE_TYPE,
        self::MODULE_PACKAGE_TYPE,
        self::LIBRARY_PACKAGE_TYPE,
        self::COMPONENT_PACKAGE_TYPE,
        self::METAPACKAGE_PACKAGE_TYPE
    ];

    /**
     * @var ComposerFactory
     * @since 2.1.0
     */
    private $composerFactory;

    /**
     * @param ComposerFactory $composerFactory
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.0.0
     */
    public function __construct(ComposerFactory $composerFactory)
    {
        $this->composerFactory = $composerFactory;
    }

    /**
     * Retrieves required php version
     *
     * @return string
     * @throws \Exception If attributes are missing in composer.lock file.
     * @since 2.0.0
     */
    public function getRequiredPhpVersion()
    {
        if ($this->isMagentoRoot()) {
            $allPlatformReqs = $this->getLocker()->getPlatformRequirements(true);
            $requiredPhpVersion = $allPlatformReqs['php']->getPrettyConstraint();
        } else {
            $packages = $this->getLocker()->getLockedRepository()->getPackages();
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
     * @since 2.0.0
     */
    public function getRequiredExtensions()
    {
        $requiredExtensions = [];
        $allPlatformReqs = array_keys($this->getLocker()->getPlatformRequirements(true));

        if (!$this->isMagentoRoot()) {
            /** @var CompletePackageInterface $package */
            foreach ($this->getLocker()->getLockedRepository()->getPackages() as $package) {
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
     * @since 2.0.0
     */
    public function getSuggestedPackages()
    {
        $suggests = [];
        /** @var \Composer\Package\CompletePackage $package */
        foreach ($this->getLocker()->getLockedRepository()->getPackages() as $package) {
            $suggests += $package->getSuggests();
        }

        return array_unique($suggests);
    }

    /**
     * Collect required packages from root composer.lock file
     *
     * @return array
     * @since 2.0.0
     */
    public function getRootRequiredPackages()
    {
        $packages = [];
        /** @var CompletePackageInterface $package */
        foreach ($this->getLocker()->getLockedRepository()->getPackages() as $package) {
            $packages[] = $package->getName();
        }
        return $packages;
    }

    /**
     * Collect required packages and types from root composer.lock file
     *
     * @return array
     * @since 2.0.0
     */
    public function getRootRequiredPackageTypesByName()
    {
        $packages = [];
        /** @var CompletePackageInterface $package */
        foreach ($this->getLocker()->getLockedRepository()->getPackages() as $package) {
            $packages[$package->getName()] = $package->getType();
        }
        return $packages;
    }

    /**
     * Collect all installed Magento packages from composer.lock
     *
     * @return array
     * @since 2.0.0
     */
    public function getInstalledMagentoPackages()
    {
        $packages = [];
        /** @var CompletePackageInterface $package */
        foreach ($this->getLocker()->getLockedRepository()->getPackages() as $package) {
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
     * Collect all system packages from composer.lock
     *
     * @return array
     * @since 2.1.0
     */
    public function getSystemPackages()
    {
        $packages = [];
        /** @var CompletePackageInterface $package */
        foreach ($this->getLocker()->getLockedRepository()->getPackages() as $package) {
            if ($this->isSystemPackage($package->getName())) {
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
     * @since 2.0.0
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
     * @return boolean
     * @since 2.0.0
     */
    public function isMagentoRoot()
    {
        $rootPackage = $this->getComposer()->getPackage();

        return (boolean)preg_match('/magento\/magento2...?/', $rootPackage->getName());
    }

    /**
     * Get root package
     *
     * @return \Composer\Package\RootPackageInterface
     * @since 2.1.0
     */
    public function getRootPackage()
    {
        return $this->getComposer()->getPackage();
    }

    /**
     * Check if a package is inside the root composer or not
     *
     * @param string $packageName
     * @return bool
     * @since 2.0.0
     */
    public function isPackageInComposerJson($packageName)
    {
        return (in_array($packageName, array_keys($this->getComposer()->getPackage()->getRequires()))
            || in_array($packageName, array_keys($this->getComposer()->getPackage()->getDevRequires()))
        );
    }

    /**
     * @return array
     * @since 2.0.0
     */
    public function getPackagesTypes()
    {
        return self::$packageTypes;
    }

    /**
     * @param string $name
     * @param string $version
     * @return array
     * @since 2.0.0
     */
    public function getPackageRequirements($name, $version)
    {
        $package = $this->getComposer()->getRepositoryManager()->findPackage($name, $version);
        return $package->getRequires();
    }

    /**
     * Returns all repository URLs, except local and packagists.
     *
     * @return string[]
     * @since 2.1.0
     */
    public function getRootRepositories()
    {
        $repositoryUrls = [];

        foreach ($this->getComposer()->getConfig()->getRepositories() as $key => $repository) {
            if ($key !== self::COMPOSER_DEFAULT_REPO_KEY) {
                $repositoryUrls[] = $repository['url'];
            }
        }

        return $repositoryUrls;
    }

    /**
     * Load composerFactory
     *
     * @return ComposerFactory
     * @deprecated 2.1.0
     * @since 2.1.0
     */
    private function getComposerFactory()
    {
        if (!$this->composerFactory) {
            $this->composerFactory = ObjectManager::getInstance()->get(ComposerFactory::class);
        }
        return $this->composerFactory;
    }

    /**
     * Load composer
     *
     * @return \Composer\Composer
     * @since 2.1.0
     */
    private function getComposer()
    {
        if (!$this->composer) {
            $this->composer = $this->getComposerFactory()->create();
        }
        return $this->composer;
    }

    /**
     * Load locker
     *
     * @return \Composer\Package\Locker
     * @since 2.1.0
     */
    private function getLocker()
    {
        if (!$this->locker) {
            $this->locker = $this->getComposer()->getLocker();
        }
        return $this->locker;
    }
}
