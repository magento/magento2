<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\RequireJs\Model;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * A service for handling RequireJS files in the application
 */
class FileManager
{
    /**
     * @var \Magento\Framework\RequireJs\Config
     */
    private $config;

    /**
     * @var \Magento\Framework\Filesystem
     */
    private $filesystem;

    /**
     * @var \Magento\Framework\App\State
     */
    private $appState;

    /**
     * @var \Magento\Framework\View\Asset\Repository
     */
    private $assetRepo;

    /**
     * @param \Magento\Framework\RequireJs\Config $config
     * @param \Magento\Framework\Filesystem $appFilesystem
     * @param \Magento\Framework\App\State $appState
     * @param \Magento\Framework\View\Asset\Repository $assetRepo
     */
    public function __construct(
        \Magento\Framework\RequireJs\Config $config,
        \Magento\Framework\Filesystem $appFilesystem,
        \Magento\Framework\App\State $appState,
        \Magento\Framework\View\Asset\Repository $assetRepo
    ) {
        $this->config = $config;
        $this->filesystem = $appFilesystem;
        $this->appState = $appState;
        $this->assetRepo = $assetRepo;
    }

    /**
     * Create a view asset representing the aggregated configuration file
     *
     * @return \Magento\Framework\View\Asset\File
     */
    public function createRequireJsConfigAsset()
    {
        $relPath = $this->config->getConfigFileRelativePath();
        $this->ensureSourceFile($relPath);
        return $this->assetRepo->createArbitrary($relPath, '');
    }

    /**
     * Create a view asset representing the aggregated configuration file
     *
     * @return \Magento\Framework\View\Asset\File
     */
    public function createRequireJsAsset()
    {
        return $this->assetRepo->createArbitrary($this->config->getRequireJsFileRelativePath(), '');
    }

    /**
     * Make sure the aggregated configuration is materialized
     *
     * By default write the file if it doesn't exist, but in developer mode always do it.
     *
     * @param string $relPath
     * @return void
     */
    private function ensureSourceFile($relPath)
    {
        $dir = $this->filesystem->getDirectoryWrite(DirectoryList::STATIC_VIEW);
        if ($this->appState->getMode() == \Magento\Framework\App\State::MODE_DEVELOPER || !$dir->isExist($relPath)) {
            $dir->writeFile($relPath, $this->config->getConfig());
        }
    }

    /**
     * Create a view asset representing the static js functionality
     *
     * @return \Magento\Framework\View\Asset\File
     */
    public function createStaticJsAsset()
    {
        if ($this->appState->getMode() != \Magento\Framework\App\State::MODE_PRODUCTION) {
            return false;
        }
        $libDir = $this->filesystem->getDirectoryRead(DirectoryList::STATIC_VIEW);
        $relPath = $libDir->getRelativePath(\Magento\Framework\RequireJs\Config::STATIC_FILE_NAME);
        /** @var $context \Magento\Framework\View\Asset\File\FallbackContext */
        $context = $this->assetRepo->getStaticViewFileContext();

        return $this->assetRepo->createArbitrary($relPath, $context->getPath());
    }

    /**
     * Create a view assets representing the bundle js functionality
     *
     * @return \Magento\Framework\View\Asset\File[]
     */
    public function createBundleJsPool()
    {
        $bundles = [];
        if ($this->appState->getMode() == \Magento\Framework\App\State::MODE_PRODUCTION) {
            $libDir = $this->filesystem->getDirectoryRead(DirectoryList::STATIC_VIEW);
            /** @var $context \Magento\Framework\View\Asset\File\FallbackContext */
            $context = $this->assetRepo->getStaticViewFileContext();

            $bundleDir = $context->getPath() . '/' .\Magento\Framework\RequireJs\Config::BUNDLE_JS_DIR;

            if (!$libDir->isExist($bundleDir)) {
                return [];
            }

            foreach ($libDir->read($bundleDir) as $bundleFile) {
                $relPath = $libDir->getRelativePath($bundleFile);
                $bundles[] = $this->assetRepo->createArbitrary($relPath, '');
            }
        }

        return $bundles;
    }
}
