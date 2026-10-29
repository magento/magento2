<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\View\Asset;

/**
 * Interface for merging multiple assets into one
 *
 * @api
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
