<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SampleData\Model;

use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Composer\ComposerInformation;
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
     * @var ComponentRegistrar
     */
    private $componentRegistrar;

    /**
     * @param ComposerInformation $composerInformation
     * @param Filesystem $filesystem
     * @param PackageFactory $packageFactory
     * @param ComponentRegistrar $componentRegistrar
     */
    public function __construct(
        ComposerInformation $composerInformation,
        Filesystem $filesystem,
        PackageFactory $packageFactory,
        ComponentRegistrar $componentRegistrar
    ) {
        $this->composerInformation = $composerInformation;
        $this->filesystem = $filesystem;
        $this->packageFactory = $packageFactory;
        $this->componentRegistrar = $componentRegistrar;
    }

    /**
     * Retrieve list of sample data packages from suggests
     *
     * @return array
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
        foreach ($this->componentRegistrar->getPaths(ComponentRegistrar::MODULE) as $moduleDir) {
            $file = $moduleDir . '/composer.json';

            /** @var Package $package */
            $package = $this->getModuleComposerPackage($file);
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
