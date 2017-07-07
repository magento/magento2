<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Asset\MergeStrategy;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Skip merging if the merged file already exists
 */
class FileExists implements \Magento\Framework\View\Asset\MergeStrategyInterface
{
    /**
     * @var \Magento\Framework\View\Asset\MergeStrategyInterface
     */
    protected $strategy;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $filesystem;

    /**
     * @param \Magento\Framework\View\Asset\MergeStrategyInterface $strategy
     * @param \Magento\Framework\Filesystem $filesystem
     */
    public function __construct(
        \Magento\Framework\View\Asset\MergeStrategyInterface $strategy,
        \Magento\Framework\Filesystem $filesystem
    ) {
        $this->strategy = $strategy;
        $this->filesystem = $filesystem;
    }

    /**
     * {@inheritdoc}
     */
    public function merge(array $assetsToMerge, \Magento\Framework\View\Asset\LocalInterface $resultAsset)
    {
        $dir = $this->filesystem->getDirectoryRead(DirectoryList::STATIC_VIEW);
        if (!$dir->isExist($resultAsset->getPath())) {
            $this->strategy->merge($assetsToMerge, $resultAsset);
        }
    }
}
