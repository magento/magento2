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
 * @since 2.0.0
 */
class Publisher
{
    /**
     * @var \Magento\Framework\Filesystem
     * @since 2.0.0
     */
    protected $filesystem;

    /**
     * @var MaterializationStrategy\Factory
     * @since 2.0.0
     */
    private $materializationStrategyFactory;

    /**
     * @var WriteFactory
     * @since 2.1.0
     */
    private $writeFactory;

    /**
     * @param \Magento\Framework\Filesystem $filesystem
     * @param MaterializationStrategy\Factory $materializationStrategyFactory
     * @param WriteFactory $writeFactory
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
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
