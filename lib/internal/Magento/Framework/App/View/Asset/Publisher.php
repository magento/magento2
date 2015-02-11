<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\View\Asset;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\View\Asset;
use Magento\Framework\Filesystem\Directory\WriteInterface;

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
        $dir = $this->filesystem->getDirectoryRead(DirectoryList::STATIC_VIEW);
        if ($dir->isExist($asset->getPath())) {
            return true;
        }

        $rootDir = $this->filesystem->getDirectoryWrite(DirectoryList::ROOT);
        $source = $rootDir->getRelativePath($asset->getSourceFile());

        return $this->publishFile(
            $rootDir,
            $source,
            $asset->getPath(),
            $this->filesystem->getDirectoryWrite(DirectoryList::STATIC_VIEW)
        );
    }

    /**
     * Publish file
     *
     * @param WriteInterface $rootDir
     * @param string $source
     * @param string $destination
     * @param WriteInterface $dir
     * @return bool
     */
    protected function publishFile($rootDir, $source, $destination, $dir)
    {
        return $rootDir->copyFile($source, $destination, $dir);
    }
}
