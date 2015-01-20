<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
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
     * @var \Magento\Framework\App\State
     */
    protected $appState;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $filesystem;

    /**
     * @param \Magento\Framework\App\State $appState
     * @param \Magento\Framework\Filesystem $filesystem
     */
    public function __construct(
        \Magento\Framework\App\State $appState,
        \Magento\Framework\Filesystem $filesystem
    ) {
        $this->appState = $appState;
        $this->filesystem = $filesystem;
    }

    /**
     * {@inheritdoc}
     */
    public function publish(Asset\LocalInterface $asset)
    {
        if ($this->appState->getMode() === \Magento\Framework\App\State::MODE_DEVELOPER) {
            return false;
        }
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
        $dir = $this->filesystem->getDirectoryWrite(DirectoryList::STATIC_VIEW);
        $rootDir = $this->filesystem->getDirectoryWrite(DirectoryList::ROOT);
        $source = $rootDir->getRelativePath($asset->getSourceFile());
        $destination = $asset->getPath();
        return $rootDir->copyFile($source, $destination, $dir);
    }
}
