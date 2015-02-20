<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Asset\Bundle;

use Magento\Framework\View\Asset;
use Magento\Framework\Filesystem;
use Magento\Framework\View\Asset\Bundle;
use Magento\Framework\View\ConfigInterface;
use Magento\Framework\View\Asset\BundleFactory;
use Magento\Framework\View\Asset\LocalInterface;
use Magento\Framework\View\Asset\ContextInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\View\Design\Theme\ListInterface;

/**
 * BundleService model
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Service
{
    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var array
     */
    protected $bundles = [];

    /**
     * @var BundleFactory
     */
    protected $bundleFactory;

    /**
     * @var ConfigInterface
     */
    protected $viewConfig;

    /**
     * @var Asset\ConfigInterface
     */
    protected $bundleConfig;

    /**
     * @var LocalInterface
     */
    protected $asset;

    /**
     * @var State
     */
    protected $appState;

    /**
     * @var ListInterface
     */
    protected $themeList;

    /**
     * @var array
     */
    protected $excluded = [];

    /**
     * @param Filesystem $filesystem
     * @param BundleFactory $bundleFactory
     * @param ConfigInterface $config
     * @param ListInterface $themeList
     * @param Asset\ConfigInterface $bundleConfig
     */
    public function __construct(
        Filesystem $filesystem,
        BundleFactory $bundleFactory,
        ConfigInterface $config,
        ListInterface $themeList,
        Asset\ConfigInterface $bundleConfig
    ) {
        $this->filesystem = $filesystem;
        $this->bundleFactory = $bundleFactory;
        $this->viewConfig = $config;
        $this->themeList = $themeList;
        $this->bundleConfig = $bundleConfig;
    }

    /**
     * @param LocalInterface $asset
     * @return $this
     */
    protected function setAsset(LocalInterface $asset)
    {
        $this->asset = $asset;
        return $this;
    }

    /**
     * @return LocalInterface
     */
    protected function getAsset()
    {
        return $this->asset;
    }

    /**
     * @return ContextInterface
     */
    protected function getContext()
    {
        return $this->asset->getContext();
    }

    /**
     * Check if asset in exclude list
     *
     * @return bool
     */
    protected function isExcluded()
    {
        $excludedFiles = array_merge($this->getConfig()->getExcludedFiles(), $this->excluded);
        if (in_array($this->getAsset()->getFilePath(), $excludedFiles)) {
            return true;
        }

        // check if file in excluded directory
        $assetDirectory  = dirname($this->getAsset()->getFilePath());
        foreach ($this->getConfig()->getExcludedDir() as $dir) {
            if (strpos($assetDirectory, $dir) !== false) {
                return true;
            }
        }
        return false;
    }


    /**
     * Collect bundle
     *
     * @param LocalInterface $asset
     * @return bool
     */
    public function collect(LocalInterface $asset)
    {
        $this->setAsset($asset);
        if (!($this->isValidAsset())) {
             return false;
        }

        $this->getBundle()->addAsset($asset);
        return true;
    }

    /**
     * @return bool
     */
    protected function isMinAssetExists()
    {
        $sourceFile = $this->getAsset()->getSourceFile();
        if (strpos($sourceFile, '.min.') !== false) {
            $this->excluded[] = str_replace('.min.', '', $this->getAsset()->getFilePath());
            return false;
        }
        $extension =  pathinfo($sourceFile, PATHINFO_EXTENSION);
        $minAbsolutePath = str_replace($extension, "min.{$extension}", $sourceFile);
        if (file_exists($minAbsolutePath)) {
            return true;
        }
        return false;
    }

    /**
     * @return bool
     */
    protected function isValidAsset()
    {
        if (
            $this->bundleConfig->isMergeJsFiles()
            && Bundle::isValid($this->getAsset())
            && !$this->isMinAssetExists()
            && !$this->isExcluded()
        ) {
            return true;
        }
        return false;
    }


    /**
     * Return bundle
     *
     * @return Bundle
     */
    protected function getBundle()
    {
        $bundlePath = $this->getBundlePath();
        return (isset($this->bundles[$bundlePath])) ? $this->bundles[$bundlePath] : $this->createBundle();
    }

    /**
     * Create bundle
     *
     * @return Bundle
     */
    protected function createBundle()
    {
        $bundlePath = $this->getBundlePath();
        $bundle = $this->bundleFactory->create();
        $bundle->setPath($bundlePath);
        $this->bundles[$bundlePath] = $bundle;
        return $bundle;
    }

    /**
     * Build bundle path
     *
     * @return string
     */
    protected function getBundlePath()
    {
        return $this->getContext()->getPath() . '/js/bundle/bundle';
    }

    /**
     * Save bundle to js file
     *
     * @return bool
     */
    public function save()
    {
        $dir = $this->filesystem->getDirectoryWrite(DirectoryList::STATIC_VIEW);

        foreach ($this->bundles as $bundle) {
            /** @var Bundle $bundle */
            foreach ($bundle->getContent() as $index => $part) {
                $dir->writeFile($bundle->getPath() . "$index.js", $part);
            }
        }

        return true;
    }

    /**
     * @return \Magento\Framework\Config\View
     */
    protected function getConfig()
    {
        return $this->viewConfig->getViewConfig([
            'area' => $this->getContext()->getAreaCode(),
            'themeModel' => $this->themeList->getThemeByFullPath(
                $this->getContext()->getAreaCode() . '/' . $this->getContext()->getThemePath()
            )
        ]);
    }
}
