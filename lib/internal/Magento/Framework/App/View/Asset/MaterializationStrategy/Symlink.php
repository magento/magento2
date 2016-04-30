<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\View\Asset\MaterializationStrategy;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\View\Asset;

class Symlink implements StrategyInterface
{
    /**
     * Publish file
     *
     * @param WriteInterface $sourceDir
     * @param WriteInterface $targetDir
     * @param string $sourcePath
     * @param string $destinationPath
     * @return bool
     */
    public function publishFile(
        WriteInterface $sourceDir,
        WriteInterface $targetDir,
        $sourcePath,
        $destinationPath
    ) {
        return $sourceDir->createSymlink($sourcePath, $destinationPath, $targetDir);
    }

    /**
     * Whether the strategy can be applied
     *
     * @param Asset\LocalInterface $asset
     * @return bool
     */
    public function isSupported(Asset\LocalInterface $asset)
    {
        $sourceParts = explode('/', $asset->getSourceFile());
        if (in_array(DirectoryList::TMP_MATERIALIZATION_DIR, $sourceParts)) {
            return false;
        }

        return true;
    }
}
