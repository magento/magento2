<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Asset;

use Magento\Framework\App;
use Magento\Framework\View;
use Magento\Framework\View\Asset;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\View\Design\Theme\ListInterface;

/**
 * BundleService model
 */
class BundleService
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
     * @var View\ConfigInterface
     */
    protected $viewConfig;

    /**
     * @var LocalInterface
     */
    protected $asset;

    /**
     * @var App\State
     */
    protected $appState;

    /**
     * @var ListInterface
     */
    protected $themeList;

    /**
     * @param Filesystem $filesystem
     * @param BundleFactory $bundleFactory
     * @param View\ConfigInterface $config
     * @param ListInterface $themeList
     */
    public function __construct(
        Filesystem $filesystem,
        BundleFactory $bundleFactory,
        View\ConfigInterface $config,
        ListInterface $themeList
    ) {
        $this->filesystem = $filesystem;
        $this->bundleFactory = $bundleFactory;
        $this->viewConfig = $config;
        $this->themeList = $themeList;
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
        if (in_array($this->getAsset()->getFilePath(), $this->getConfig()->getExcludedFiles())) {
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
    protected function isValidAsset()
    {
        if (Bundle::isValidType($this->getAsset()->getContentType()) && !$this->isExcluded()) {
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
        $bundle->setType($this->getAsset()->getContentType());
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
        $path = $this->getContext()->getPath();
        if ($this->getAsset()->getModule() != '') {
            $bundleName = 'bundle';
            if ($this->getAsset()->getContentType() == 'html') {
                $bundleName = 'bundle-html';
            }
        } else {
            $bundleName = 'lib-bundle';
        }
        $path .= '/' . $bundleName;
        return $path;
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
