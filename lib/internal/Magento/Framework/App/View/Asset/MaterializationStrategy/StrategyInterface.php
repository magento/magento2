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
 * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function isSupported(Asset\LocalInterface $asset);
}
