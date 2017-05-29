<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Asset\MergeStrategy;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\View\Asset\Source;

/**
 * Skip merging if all of the files that need to be merged were not modified
 *
 * Each file will be resolved and its mtime will be checked.
 * Then combination of all mtimes will be compared to a special .dat file that contains mtimes from previous merging
 */
class Checksum implements \Magento\Framework\View\Asset\MergeStrategyInterface
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
     * @var Source
     */
    private $assetSource;

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
     * @deprecated
     * @return Source
     */
    private function getAssetSource()
    {
        if (!$this->assetSource) {
            $this->assetSource = ObjectManager::getInstance()->get(Source::class);
        }
        return $this->assetSource;
    }

    /**
     * {@inheritdoc}
     */
    public function merge(array $assetsToMerge, \Magento\Framework\View\Asset\LocalInterface $resultAsset)
    {
        $rootDir = $this->filesystem->getDirectoryRead(DirectoryList::ROOT);
        $mTime = null;
        /** @var \Magento\Framework\View\Asset\MergeableInterface $asset */
        foreach ($assetsToMerge as $asset) {
            $sourceFile = $this->getAssetSource()->findSource($asset);
            $mTime .= $rootDir->stat($rootDir->getRelativePath($sourceFile))['mtime'];
        }

        if (null === $mTime) {
            return; // nothing to merge
        }

        $dat = $resultAsset->getPath() . '.dat';
        $targetDir = $this->filesystem->getDirectoryWrite(DirectoryList::STATIC_VIEW);
        if (!$targetDir->isExist($dat) || strcmp($mTime, $targetDir->readFile($dat)) !== 0) {
            $this->strategy->merge($assetsToMerge, $resultAsset);
            $targetDir->writeFile($dat, $mTime);
        }
    }
}
