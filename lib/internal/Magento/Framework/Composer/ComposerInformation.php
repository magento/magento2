<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Composer;

use Composer\Factory as ComposerFactory;
use Composer\Package\Link;
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
     * @param BufferIoFactory $bufferIoFactory
     * @throws \Exception
     */
    public function __construct(
        Filesystem $filesystem,
        BufferIoFactory $bufferIoFactory
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
        $this->composer = ComposerFactory::create($bufferIoFactory->create(), $composerJson);
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
