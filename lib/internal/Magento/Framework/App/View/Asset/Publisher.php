<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\View\Asset;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\View\Asset;

/**
 * A publishing service for view assets
 */
class Publisher
{
    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $filesystem;

    /**
     * @var MaterializationStrategy\Factory
     */
    private $materializationStrategyFactory;

    /**
     * @param \Magento\Framework\Filesystem $filesystem
     * @param MaterializationStrategy\Factory $materializationStrategyFactory
     */
    public function __construct(
        \Magento\Framework\Filesystem $filesystem,
        MaterializationStrategy\Factory $materializationStrategyFactory
    ) {
        $this->filesystem = $filesystem;
        $this->materializationStrategyFactory = $materializationStrategyFactory;
    }

    /**
     * @param Asset\LocalInterface $asset
     * @return bool
     */
    public function publish(Asset\LocalInterface $asset)
    {
        if ($this->isFileEquals($asset)) {
            return true;
        }

        return $this->publishAsset($asset);
    }

    /**
     * @param Asset\LocalInterface $asset
     * @return bool
     */
    public function isFileEquals(Asset\LocalInterface $asset)
    {
        $dir = $this->filesystem->getDirectoryRead(DirectoryList::STATIC_VIEW);
        $source = $asset->getSourceFile();
        $destination = $dir->getAbsolutePath($asset->getPath());

        if ($dir->isExist($source) === false) {
            return false;
        }

        if ($dir->isExist($destination) === false) {
            return false;
        }

        $sourceSum = md5_file($source);
        $destinationSum = md5_file($destination);

        if ($sourceSum !== $destinationSum) {
            return false;
        }

        return true;
    }

    /**
     * Publish the asset
     *
     * @param Asset\LocalInterface $asset
     * @return bool
     */
    private function publishAsset(Asset\LocalInterface $asset)
    {
        $targetDir = $this->filesystem->getDirectoryWrite(DirectoryList::STATIC_VIEW);
        $rootDir = $this->filesystem->getDirectoryWrite(DirectoryList::ROOT);
        $source = $rootDir->getRelativePath($asset->getSourceFile());
        $destination = $asset->getPath();
        $strategy = $this->materializationStrategyFactory->create($asset);
        return $strategy->publishFile($rootDir, $targetDir, $source, $destination);
    }
}
