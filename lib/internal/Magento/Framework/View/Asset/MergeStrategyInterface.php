<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Asset;

/**
 * Interface for merging multiple assets into one
 */
interface MergeStrategyInterface
{
    /**
     * Merge assets into one
     *
     * The $resultAsset may be used to persist result
     *
     * @param MergeableInterface[] $assetsToMerge
     * @param LocalInterface $resultAsset
     * @return void
     */
    public function merge(array $assetsToMerge, LocalInterface $resultAsset);
}
