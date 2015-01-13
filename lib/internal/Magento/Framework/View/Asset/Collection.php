<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Asset;

/**
 * List of page asset instances associated with unique identifiers
 */
class Collection
{
    /**
     * Assets
     *
     * @var AssetInterface[]
     */
    protected $assets = [];

    /**
     * Add an instance, identified by a unique identifier, to the list
     *
     * @param string $identifier
     * @param AssetInterface $asset
     * @return void
     */
    public function add($identifier, AssetInterface $asset)
    {
        $this->assets[$identifier] = $asset;
    }

    /**
     * Whether an item belongs to a collection or not
     *
     * @param string $identifier
     * @return bool
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
     */
    public function remove($identifier)
    {
        unset($this->assets[$identifier]);
    }

    /**
     * Retrieve all items in the collection
     *
     * @return AssetInterface[]
     */
    public function getAll()
    {
        return $this->assets;
    }
}
