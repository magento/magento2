<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\View\Asset\MaterializationStrategy;

use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\View\Asset;

/**
 * Interface \Magento\Framework\App\View\Asset\MaterializationStrategy\StrategyInterface
 *
 */
interface StrategyInterface
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
    );

    /**
     * Whether the strategy can be applied
     *
     * @param Asset\LocalInterface $asset
     * @return bool
     */
    public function isSupported(Asset\LocalInterface $asset);
}
