<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Deploy\Collector;

use Magento\Deploy\Source\SourcePool;
use Magento\Deploy\Package\Package;
use Magento\Deploy\Package\PackageFactory;
use Magento\Deploy\Package\PackageFile;
use Magento\Framework\Module\Manager;
use Magento\Framework\View\Asset\PreProcessor\FileNameResolver;

/**
 * Deployable files collector
 *
 * Default implementation uses Source Pool object (@see SourcePool)
 */
class Collector implements CollectorInterface
{
    /**
     * Source Pool object
     *
     * Provides the list of source objects
     *
     * @var SourcePool
     */
    private $sourcePool;

    /**
     * Resolver for deployed static file name
     *
     * A given file could be an alternative source for the real static file which needs to be deployed. In such case
     * resolver provides the final static file name
     *
     * @var FileNameResolver
     */
    private $fileNameResolver;

    /**
     * Factory class for Package object
     *
     * @see Package
     * @var PackageFactory
     */
    private $packageFactory;

    /** @var \Magento\Framework\Module\Manager */
    private $moduleManager;

    /**
     * Default values for package primary identifiers
     *
     * @var array
     */
    private $packageDefaultValues = [
        'area' => Package::BASE_AREA,
        'theme' => Package::BASE_THEME,
        'locale' => Package::BASE_LOCALE
    ];

    /**
     * Collector constructor
     *
     * @param SourcePool $sourcePool
     * @param FileNameResolver $fileNameResolver
     * @param PackageFactory $packageFactory
     * @param Manager|null $moduleManager
     */
    public function __construct(
        SourcePool $sourcePool,
        FileNameResolver $fileNameResolver,
        PackageFactory $packageFactory,
        ?Manager $moduleManager = null
    ) {
        $this->sourcePool = $sourcePool;
        $this->fileNameResolver = $fileNameResolver;
        $this->packageFactory = $packageFactory;
        $this->moduleManager = $moduleManager ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Framework\Module\Manager::class);
    }

    /**
     * @inheritdoc
     */
    public function collect()
    {
        $packages = [];
        foreach ($this->sourcePool->getAll() as $source) {
            $files = $source->get();
            foreach ($files as $file) {
                if ($file->getModule() && !$this->moduleManager->isEnabled($file->getModule())) {
                    continue;
                }
                if (!$file->getFileName()) {
                    continue;
                }
                $file->setDeployedFileName($this->fileNameResolver->resolve($file->getFileName()));
                $params = $this->getParams($file);
                $packagePath = "{$params['area']}/{$params['theme']}/{$params['locale']}";
                if (!isset($packages[$packagePath])) {
                    $packages[$packagePath] = $this->packageFactory->create($params);
                }
                if ($file->getFilePath()) {
                    $file->setPackage($packages[$packagePath]);
                }
            }
        }
        return $packages;
    }

    /**
     * Retrieve package params
     *
     * @param PackageFile $file
     * @return array
     */
    private function getParams(PackageFile $file)
    {
        $params = [
            'area' => $file->getArea(),
            'theme' => $file->getTheme(),
            'locale' => $file->getLocale(),
            'module' => $file->getModule(),
            'isVirtual' => (!$file->getLocale() || !$file->getTheme() || !$file->getArea())
        ];
        foreach ($this->packageDefaultValues as $name => $value) {
            if (!isset($params[$name])) {
                $params[$name] = $value;
            }
        }
        return $params;
    }
}
