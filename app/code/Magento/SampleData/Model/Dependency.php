<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SampleData\Model;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Component\ComponentRegistrarInterface;
use Magento\Framework\Composer\ComposerInformation;
use Magento\Framework\Config\Composer\Package;
use Magento\Framework\Config\Composer\PackageFactory;
use Magento\Framework\Filesystem;

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
     * @var PackageFactory
     */
    private $packageFactory;

    /**
     * @var ComponentRegistrarInterface
     */
    private $componentRegistrar;

    /**
     * @var Filesystem\Directory\ReadInterfaceFactory
     */
    private $directoryReadFactory;

    /**
     * @param ComposerInformation $composerInformation
     * @param Filesystem $filesystem
     * @param PackageFactory $packageFactory
     * @param ComponentRegistrarInterface $componentRegistrar
     */
    public function __construct(
        ComposerInformation $composerInformation,
        Filesystem $filesystem,
        PackageFactory $packageFactory,
        ComponentRegistrarInterface $componentRegistrar,
        \Magento\Framework\Filesystem\Directory\ReadInterfaceFactory $directoryReadFactory = null
    ) {
        $this->composerInformation = $composerInformation;
        $this->packageFactory = $packageFactory;
        $this->componentRegistrar = $componentRegistrar;
        if ($directoryReadFactory === null) {
            $directoryReadFactory = ObjectManager::getInstance()->get(Filesystem\Directory\ReadInterfaceFactory::class);
        }
        $this->directoryReadFactory = $directoryReadFactory;
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
            /** @var Filesystem\Directory\ReadInterface $directory */
            $directory = $this->directoryReadFactory->create(['path' => $moduleDir]);
            echo "path=$moduleDir\n";
            if (!$directory->isExist('composer.json') || !$directory->isReadable('composer.json')) {
                continue;
            }

            /** @var Package $package */
            $package = $this->getModuleComposerPackage($directory);
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
     * @param Filesystem\Directory\ReadInterface $directory
     * @return Package
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    protected function getModuleComposerPackage(Filesystem\Directory\ReadInterface $directory)
    {
        return $this->packageFactory->create(['json' => json_decode($directory->readFile('composer.json'))]);
    }
}
