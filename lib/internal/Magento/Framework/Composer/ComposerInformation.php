<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Composer;

use Composer\Factory as ComposerFactory;
use Composer\Package\PackageInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;

/**
 * Class ComposerInformation uses Composer to determine dependency information.
 */
class ComposerInformation
{
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
     * @param Filesystem $filesystem
     */
    public function __construct(
        Filesystem $filesystem
    ) {
        $vendor = $filesystem->getDirectoryRead(DirectoryList::CONFIG)->getAbsolutePath('vendor_path.php');
        $vendorPath = $filesystem->getDirectoryRead(DirectoryList::ROOT)->getAbsolutePath() . include $vendor;
        // Create Composer
        $io = new \Composer\IO\BufferIO();
        $this->composer = ComposerFactory::create($io, $vendorPath . '/../composer.json');
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
            foreach ($packages as $package) {
                /** @var \Composer\Package\CompletePackage $package */
                $packageName = $package->getPrettyName();
                if ($packageName === 'magento/product-community-edition') {
                    $phpRequirementLink = $package->getRequires()['php'];
                    $requiredPhpVersion = $phpRequirementLink->getPrettyConstraint();
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
        if ($this->isMagentoRoot()) {
            $allPlatformReqs = $this->locker->getPlatformRequirements(true);
            foreach ($allPlatformReqs as $reqIndex => $constraint) {
                if (substr($reqIndex, 0, 4) === 'ext-') {
                    $requiredExtensions[] = substr($reqIndex, 4);
                }
            }
        } else {
            $requiredExtensions = [];

            /** @var \Composer\Package\CompletePackage $package */
            foreach ($this->locker->getLockedRepository()->getPackages() as $package) {
                $requires = $package->getRequires();
                $requires = array_merge($requires, $package->getDevRequires());
                foreach ($requires as $reqIndex => $constraint) {
                    if (substr($reqIndex, 0, 4) === 'ext-') {
                        $requiredExtensions[] = substr($reqIndex, 4);
                    }
                }
            }
        }

        if (!isset($requiredExtensions)) {
            throw new \Exception('Cannot find extensions in \'composer.lock\' file');
        }
        return $requiredExtensions;
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
    public function getRootRequiredPackagesAndTypes()
    {
        $packages = [];
        /** @var PackageInterface $package */
        foreach ($this->locker->getLockedRepository()->getPackages() as $package) {
            $packages[$package->getName()] = $package->getType();
        }
        return $packages;
    }

    /**
     * Determines if Magento is the root package or it is included as a requirement.
     *
     * @return bool
     */
    private function isMagentoRoot()
    {
        $rootPackage = $this->composer->getPackage();
        return ('magento/magento2ce' == $rootPackage->getName());
    }
}
