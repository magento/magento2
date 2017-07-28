<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Asset\Bundle;

use Magento\Framework\View\Asset;
use Magento\Framework\Filesystem;
use Magento\Framework\View\Asset\Bundle;
use Magento\Framework\View\Asset\LocalInterface;
use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * BundleService model
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @deprecated 2.2.0 since 2.2.0
 * @see \Magento\Deploy\Service\Bundle
 * @since 2.0.0
 */
class Manager
{
    const BUNDLE_JS_DIR = 'js/bundle';

    const BUNDLE_PATH = '/js/bundle/bundle';

    const ASSET_TYPE_JS = 'js';

    const ASSET_TYPE_HTML = 'html';

    /**
     * @var \Magento\Framework\Filesystem
     * @since 2.0.0
     */
    protected $filesystem;

    /**
     * @var \Magento\Framework\View\Asset\Bundle
     * @since 2.0.0
     */
    protected $bundle;

    /**
     * @var \Magento\Framework\View\Asset\Bundle\ConfigInterface
     * @since 2.0.0
     */
    protected $bundleConfig;

    /**
     * @var \Magento\Framework\View\Asset\ConfigInterface
     * @since 2.0.0
     */
    protected $assetConfig;

    /**
     * @var array
     * @since 2.0.0
     */
    protected $excluded = [];

    /**
     * @var array
     * @since 2.0.0
     */
    public static $availableTypes = [self::ASSET_TYPE_JS, self::ASSET_TYPE_HTML];

    /**
     * @var Asset\Minification
     * @since 2.0.0
     */
    private $minification;

    /**
     * @param Filesystem $filesystem
     * @param Bundle $bundle
     * @param Bundle\ConfigInterface $bundleConfig
     * @param Asset\ConfigInterface $assetConfig
     * @param Asset\Minification $minification
     * @since 2.0.0
     */
    public function __construct(
        Filesystem $filesystem,
        Bundle $bundle,
        Bundle\ConfigInterface $bundleConfig,
        Asset\ConfigInterface $assetConfig,
        Asset\Minification $minification
    ) {
        $this->filesystem = $filesystem;
        $this->assetConfig = $assetConfig;
        $this->bundleConfig = $bundleConfig;
        $this->bundle = $bundle;
        $this->minification = $minification;
    }

    /**
     * Check if asset in exclude list
     *
     * @param LocalInterface $asset
     * @return bool
     * @since 2.0.0
     */
    protected function isExcluded(LocalInterface $asset)
    {
        $excludedFiles = array_merge(
            $this->bundleConfig->getConfig($asset->getContext())->getExcludedFiles(),
            $this->excluded
        );
        foreach ($excludedFiles as $file) {
            if ($this->isExcludedFile($file, $asset)) {
                return true;
            }
        }

        foreach ($this->bundleConfig->getConfig($asset->getContext())->getExcludedDir() as $directory) {
            if ($this->isExcludedDirectory($directory, $asset)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if asset file in excluded directory
     *
     * @param string $directoryPath
     * @param LocalInterface $asset
     * @return bool
     * @since 2.0.0
     */
    protected function isExcludedDirectory($directoryPath, $asset)
    {
        /** @var $asset LocalInterface */
        $assetDirectory = dirname($asset->getFilePath());
        $assetDirectory .= substr($assetDirectory, -1) != '/' ? '/' : '';
        $directoryPath .= substr($directoryPath, -1) != '/' ? '/' : '';

        /** @var $asset LocalInterface */
        $directoryPathInfo = $this->splitPath($directoryPath);
        if ($directoryPathInfo && $this->compareModules($directoryPathInfo, $asset)) {
            return strpos($assetDirectory, $directoryPathInfo['excludedPath']) === 0;
        }
        return false;
    }

    /**
     * Check if asset file is excluded
     *
     * @param string $filePath
     * @param LocalInterface $asset
     * @return bool
     * @since 2.0.0
     */
    protected function isExcludedFile($filePath, $asset)
    {
        /** @var $asset LocalInterface */
        $filePathInfo = $this->splitPath($filePath);
        if ($filePathInfo && $this->compareModules($filePathInfo, $asset)) {
            return $asset->getFilePath() == $filePathInfo['excludedPath'];
        }
        return false;
    }

    /**
     * Compare asset module with excluded module
     *
     * @param array $filePathInfo
     * @param LocalInterface $asset
     * @return bool
     * @since 2.0.0
     */
    protected function compareModules($filePathInfo, $asset)
    {
        /** @var $asset LocalInterface */
        if (($filePathInfo['excludedModule'] == 'Lib' && $asset->getModule() == '')
            || ($filePathInfo['excludedModule'] == $asset->getModule())
        ) {
            return true;
        }
        return false;
    }

    /**
     * Get excluded module and path from complex string
     *
     * @param string $path
     * @return array|bool
     * @since 2.0.0
     */
    protected function splitPath($path)
    {
        if (strpos($path, '::') > 0) {
            list($excludedModule, $excludedPath) = explode('::', $path);
            return [
                'excludedModule' => $excludedModule,
                'excludedPath' => $excludedPath,
            ];
        }
        return false;
    }

    /**
     * Add asset to the bundle
     *
     * @param LocalInterface $asset
     * @return bool
     * @since 2.0.0
     */
    public function addAsset(LocalInterface $asset)
    {
        if (!$this->isValidAsset($asset)) {
            return false;
        }

        $this->bundle->addAsset($asset);
        return true;
    }

    /**
     * @param LocalInterface $asset
     * @return bool
     * @since 2.0.0
     */
    protected function isAssetMinification(LocalInterface $asset)
    {
        $sourceFile = $asset->getSourceFile();
        $extension = $asset->getContentType();
        if (in_array($sourceFile, $this->excluded)) {
            return false;
        }

        if (strpos($sourceFile, '.min.') === false) {
            $info = pathinfo($asset->getPath());
            $assetMinifiedPath = $info['dirname'] . '/' . $info['filename'] . '.min.' . $info['extension'];
            if ($this->filesystem->getDirectoryRead(DirectoryList::APP)->isExist($assetMinifiedPath)) {
                $this->excluded[] = $sourceFile;
                return false;
            }
        } else {
            $this->excluded[] = $this->filesystem->getDirectoryRead(DirectoryList::APP)
                ->getAbsolutePath(str_replace(".min.$extension", ".$extension", $asset->getPath()));
        }

        return true;
    }

    /**
     * @param LocalInterface $asset
     * @return bool
     * @since 2.0.0
     */
    protected function isValidAsset(LocalInterface $asset)
    {
        if ($this->isValidType($asset)
            && $this->isAssetMinification($asset)
            && !$this->isExcluded($asset)
        ) {
            return true;
        }
        return false;
    }

    /**
     * @param LocalInterface $asset
     * @return bool
     * @since 2.0.0
     */
    protected function isValidType(LocalInterface $asset)
    {
        $type = $asset->getContentType();
        if (!in_array($type, self::$availableTypes)) {
            return false;
        }

        return true;
    }

    /**
     * Flush bundle
     *
     * @return void
     * @since 2.0.0
     */
    public function flush()
    {
        $this->bundle->flush();
    }
}
