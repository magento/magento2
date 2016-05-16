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
        $source = $asset->getSourceFile();
        $destination = $asset->getPath();

        if ($this->isFilesEqual($source, $destination)) {
            return true;
        }

        return $this->publishAsset($asset);
    }

    /**
     * @param string $source
     * @param string $destination
     * @return bool
     */
    public function isFilesEqual($source, $destination)
    {
        $destinationDir = $this->filesystem->getDirectoryRead(DirectoryList::STATIC_VIEW);

        if ($this->isSourceFileExists($source) == false) {
            return false;
        }
        
        if ($this->isDestinationFileExists($destination) == false) {
            return false;
        }

        $sourceSum = $this->getMd5FileSum($source);
        $destination = $destinationDir->getAbsolutePath($destination);
        $destinationSum = $this->getMd5FileSum($destination);

        if ($sourceSum !== $destinationSum) {
            return false;
        }

        return true;
    }

    /**
     * @param $destination
     *
     * @return bool
     */
    protected function isDestinationFileExists($destination)
    {
        $destinationDir = $this->filesystem->getDirectoryRead(DirectoryList::STATIC_VIEW);
        $destination = $destinationDir->getRelativePath($destination);

        if (empty($destination) || $destinationDir->isExist($destination) == false) {
            return false;
        }

        return true;
    }

    /**
     * @param $source
     *
     * @return bool
     */
    protected function isSourceFileExists($source)
    {
        $sourceDir = $this->filesystem->getDirectoryRead(DirectoryList::ROOT);
        $source = $sourceDir->getRelativePath($source);

        if (empty($source)) {
            return false;
        }

        if ($sourceDir->isExist($source) === false) {
            return false;
        }

        return true;
    }

    /**
     * Calculate the MD5 sum for a file
     *
     * @param $file
     *
     * @return string
     */
    public function getMd5FileSum($file)
    {
        return md5_file($file);
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
