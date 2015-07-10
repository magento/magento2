<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Asset\Bundle;

use Magento\Framework\View\Asset;
use Magento\Framework\Filesystem;
use Magento\Framework\View\Asset\Bundle;
use Magento\Framework\View\Asset\LocalInterface;

/**
 * BundleService model
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Manager
{
    const BUNDLE_PATH = '/js/bundle/bundle';

    const ASSET_TYPE_JS = 'js';

    const ASSET_TYPE_HTML = 'html';

    /** @var Filesystem */
    protected $filesystem;

    /** @var  Bundle */
    protected $bundle;

    /** @var Bundle\ConfigInterface  */
    protected $bundleConfig;

    /** @var Asset\ConfigInterface  */
    protected $assetConfig;

    /** @var array */
    protected $excluded = [];

    /** @var array */
    public static $availableTypes = [self::ASSET_TYPE_JS, self::ASSET_TYPE_HTML];

    /**
     * @param Filesystem $filesystem
     * @param Bundle $bundle
     * @param Bundle\ConfigInterface $bundleConfig
     * @param Asset\ConfigInterface $assetConfig
     */
    public function __construct(
        Filesystem $filesystem,
        Bundle $bundle,
        Bundle\ConfigInterface $bundleConfig,
        Asset\ConfigInterface $assetConfig
    ) {
        $this->filesystem = $filesystem;
        $this->assetConfig = $assetConfig;
        $this->bundleConfig = $bundleConfig;
        $this->bundle = $bundle;
    }

    /**
     * Check if asset in exclude list
     *
     * @param LocalInterface $asset
     * @return bool
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
     */
    public function addAsset(LocalInterface $asset)
    {
        if (!($this->isValidAsset($asset))) {
            return false;
        }

        $this->bundle->addAsset($asset);
        return true;
    }

    /**
     * @param LocalInterface $asset
     * @return bool
     */
    protected function isAssetMinification(LocalInterface $asset)
    {
        $sourceFile = $asset->getSourceFile();
        if (in_array($asset->getFilePath(), $this->excluded)) {
            return false;
        }
        if ($this->assetConfig->isAssetMinification($asset->getContentType())) {

            if (strpos($sourceFile, '.min.') !== false) {
                $this->excluded[] = str_replace('.min.', '', $sourceFile);
                return true;
            }

            $extension = $asset->getContentType();
            $minAbsolutePath = str_replace($extension, "min.{$extension}", $sourceFile);
            if (file_exists($minAbsolutePath)) {
                return false;
            }

            return true;
        }

        if (strpos($sourceFile, '.min.') !== false) {
            $absolutePath = str_replace('.min.', '', $asset->getFilePath());
            if (file_exists($absolutePath)) {
                return false;
            }
        } else {
            $extension = $asset->getContentType();
            $this->excluded[] = str_replace($extension, "min.{$extension}", $asset->getFilePath());
        }

        return true;
    }

    /**
     * @param LocalInterface $asset
     * @return bool
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
     */
    protected function isValidType(LocalInterface $asset)
    {
        $type = $asset->getContentType();
        if (!in_array($type, self::$availableTypes)) {
            return false;
        }

        if ($type == self::ASSET_TYPE_HTML) {
            return $asset->getModule() !== '';
        }

        return true;
    }


    /**
     * Flush bundle
     *
     * @return void
     */
    public function flush()
    {
        $this->bundle->flush();
    }
}
