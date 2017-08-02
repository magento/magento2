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
 * @since 2.0.0
 */
class Dependency
{
    /**
     * Sample data version text
     */
    const SAMPLE_DATA_SUGGEST = 'Sample Data version:';

    /**
     * @var ComposerInformation
     * @since 2.0.0
     */
    protected $composerInformation;

    /**
     * @var Filesystem
     * @since 2.0.0
     */
    private $filesystem;

    /**
     * @var PackageFactory
     * @since 2.0.0
     */
    private $packageFactory;

    /**
     * @var ComponentRegistrar
     * @since 2.0.0
     */
    private $componentRegistrar;

    /**
     * @param ComposerInformation $composerInformation
     * @param Filesystem $filesystem
     * @param PackageFactory $packageFactory
     * @param ComponentRegistrar $componentRegistrar
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
     */
    protected function getSuggestsFromModules()
    {
        $suggests = [];
        foreach ($this->componentRegistrar->getPaths(ComponentRegistrar::MODULE) as $moduleDir) {
            $file = $moduleDir . '/composer.json';

            if (!file_exists($file) || !is_readable($file)) {
                continue;
            }

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
     * @since 2.0.0
     */
    protected function getModuleComposerPackage($file)
    {
        return $this->packageFactory->create(['json' => json_decode(file_get_contents($file))]);
    }
}
