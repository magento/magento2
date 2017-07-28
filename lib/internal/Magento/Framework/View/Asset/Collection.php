<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Asset;

/**
 * List of page asset instances associated with unique identifiers
 * @since 2.0.0
 */
class Collection
{
    /**
     * Assets
     *
     * @var AssetInterface[]
     * @since 2.0.0
     */
    protected $assets = [];

    /**
     * Add an instance, identified by a unique identifier, to the list
     *
     * @param string $identifier
     * @param AssetInterface $asset
     * @return void
     * @since 2.0.0
     */
    public function add($identifier, AssetInterface $asset)
    {
        $this->assets[$identifier] = $asset;
    }

    /**
     * @param string $identifier
     * @param AssetInterface $asset
     * @param string $key
     * @return void
     * @since 2.0.0
     */
    public function insert($identifier, AssetInterface $asset, $key)
    {
        $result = [];
        foreach ($this->assets as $assetKey => $assetVal) {
            if ($assetKey == $key) {
                $result[$key] = $assetVal;
                $result[$identifier] = $asset;
            } else {
                $result[$assetKey] = $assetVal;
            }
        }

        $this->assets = $result;
    }

    /**
     * Whether an item belongs to a collection or not
     *
     * @param string $identifier
     * @return bool
     * @since 2.0.0
     */
    public function has($identifier)
    {
        return isset($this->assets[$identifier]);
    }

    /**
     * Remove an item from the list
     *
     * @param string $identifier
     * @return void
     * @since 2.0.0
     */
    public function remove($identifier)
    {
        unset($this->assets[$identifier]);
    }

    /**
     * Retrieve all items in the collection
     *
     * @return AssetInterface[]
     * @since 2.0.0
     */
    public function getAll()
    {
        return $this->assets;
    }
}
