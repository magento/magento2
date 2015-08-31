<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SampleData\Model;

use Magento\Framework\Composer\ComposerInformation;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Config\Composer\Package;
use Magento\Framework\Config\Composer\PackageFactory;

/**
 * Sample Data dependency
 */
class Dependency
{
    /**
     * Sample data version text
     */
    const SAMPLE_DATA_SUGGEST = 'Sample Data version:';

    /**
     * @var ComposerInformation
     */
    protected $composerInformation;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var PackageFactory
     */
    private $packageFactory;

    /**
     * @param ComposerInformation $composerInformation
     * @param Filesystem $filesystem
     * @param PackageFactory $packageFactory
     */
    public function __construct(
        ComposerInformation $composerInformation,
        Filesystem $filesystem,
        PackageFactory $packageFactory
    ) {
        $this->composerInformation = $composerInformation;
        $this->filesystem = $filesystem;
        $this->packageFactory = $packageFactory;
    }

    /**
     * Retrieve list of sample data packages from suggests
     */
    public function getSampleDataPackages()
    {
        $installExtensions = [];
        $suggests = $this->composerInformation->getSuggestedPackages();
        $suggests = array_merge($suggests, $this->getSuggestsFromModules());
        foreach ($suggests as $name => $version) {
            if (strpos($version, self::SAMPLE_DATA_SUGGEST) === 0) {
                $installExtensions[$name] = substr($version, strlen(self::SAMPLE_DATA_SUGGEST));
            }
        }
        return $installExtensions;
    }

    /**
     * Retrieve suggested sample data packages from modules composer.json
     *
     * @return array
     */
    protected function getSuggestsFromModules()
    {
        $suggests = [];
        $directoryRead = $this->filesystem->getDirectoryRead(DirectoryList::MODULES);
        $path = $directoryRead->getAbsolutePath();
        $modulesJson = $directoryRead->search('*/*/composer.json');
        foreach ($modulesJson as $file) {
            /** @var Package $package */
            $package = $this->getModuleComposerPackage($path . $file);
            $suggest = json_decode(json_encode($package->get('suggest')), true);
            if (!empty($suggest)) {
                $suggests += $suggest;
            }
        }
        return $suggests;
    }

    /**
     * Load package
     *
     * @param string $file
     * @return Package
     */
    protected function getModuleComposerPackage($file)
    {
        return $this->packageFactory->create(['json' => json_decode(file_get_contents($file))]);
    }
}
