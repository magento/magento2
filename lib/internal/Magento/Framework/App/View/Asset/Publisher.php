<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\View\Asset;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Directory\WriteFactory;
use Magento\Framework\View\Asset;

/**
 * A publishing service for view assets
 *
 * @api
 * @since 100.0.2
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
     * @var WriteFactory
     */
    private $writeFactory;

    /**
     * @param \Magento\Framework\Filesystem $filesystem
     * @param MaterializationStrategy\Factory $materializationStrategyFactory
     * @param WriteFactory $writeFactory
     */
    public function __construct(
        \Magento\Framework\Filesystem $filesystem,
        MaterializationStrategy\Factory $materializationStrategyFactory,
        WriteFactory $writeFactory
    ) {
        $this->filesystem = $filesystem;
        $this->materializationStrategyFactory = $materializationStrategyFactory;
        $this->writeFactory = $writeFactory;
    }

    /**
     * @param Asset\LocalInterface $asset
     * @return bool
     */
    public function publish(Asset\LocalInterface $asset)
    {
        $dir = $this->filesystem->getDirectoryRead(DirectoryList::STATIC_VIEW);
        if ($dir->isExist($asset->getPath())) {
            return true;
        }

        return $this->publishAsset($asset);
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
        $fullSource = $asset->getSourceFile();
        $source = basename($fullSource);
        $sourceDir = $this->writeFactory->create(dirname($fullSource));
        $destination = $asset->getPath();
        $strategy = $this->materializationStrategyFactory->create($asset);
        return $strategy->publishFile($sourceDir, $targetDir, $source, $destination);
    }
}
