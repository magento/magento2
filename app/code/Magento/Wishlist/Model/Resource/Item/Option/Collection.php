<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Wishlist item option collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Wishlist\Model\Resource\Item\Option;

use Magento\Catalog\Model\Product;
use Magento\Wishlist\Model\Item;

class Collection extends \Magento\Framework\Model\Resource\Db\Collection\AbstractCollection
{
    /**
     * Array of option ids grouped by item id
     *
     * @var array
     */
    protected $_optionsByItem = [];

    /**
     * Array of option ids grouped by product id
     *
     * @var array
     */
    protected $_optionsByProduct = [];

    /**
     * Define resource model for collection
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\Wishlist\Model\Item\Option', 'Magento\Wishlist\Model\Resource\Item\Option');
    }

    /**
     * Fill array of options by item and product
     *
     * @return $this
     */
    protected function _afterLoad()
    {
        parent::_afterLoad();

        foreach ($this as $option) {
            $optionId = $option->getId();
            $itemId = $option->getWishlistItemId();
            $productId = $option->getProductId();
            if (isset($this->_optionsByItem[$itemId])) {
                $this->_optionsByItem[$itemId][] = $optionId;
            } else {
                $this->_optionsByItem[$itemId] = [$optionId];
            }
            if (isset($this->_optionsByProduct[$productId])) {
                $this->_optionsByProduct[$productId][] = $optionId;
            } else {
                $this->_optionsByProduct[$productId] = [$optionId];
            }
        }

        return $this;
    }

    /**
     * Apply quote item(s) filter to collection
     *
     * @param  int|array|Item $item
     * @return $this
     */
    public function addItemFilter($item)
    {
        if (empty($item)) {
            $this->_totalRecords = 0;
            $this->_setIsLoaded(true);
        } elseif (is_array($item)) {
            $this->addFieldToFilter('wishlist_item_id', ['in' => $item]);
        } elseif ($item instanceof Item) {
            $this->addFieldToFilter('wishlist_item_id', $item->getId());
        } else {
            $this->addFieldToFilter('wishlist_item_id', $item);
        }

        return $this;
    }

    /**
     * Get array of all product ids
     *
     * @return array
     */
    public function getProductIds()
    {
        $this->load();

        return array_keys($this->_optionsByProduct);
    }

    /**
     * Get all option for item
     *
     * @param Item|int|string $item
     * @return array
     */
    public function getOptionsByItem($item)
    {
        if ($item instanceof Item) {
            $itemId = $item->getId();
        } else {
            $itemId = $item;
        }

        $this->load();

        $options = [];
        if (isset($this->_optionsByItem[$itemId])) {
            foreach ($this->_optionsByItem[$itemId] as $optionId) {
                $options[] = $this->_items[$optionId];
            }
        }

        return $options;
    }

    /**
     * Get all option for item
     *
     * @param  Product|int|string $product
     * @return array
     */
    public function getOptionsByProduct($product)
    {
        if ($product instanceof Product) {
            $productId = $product->getId();
        } else {
            $productId = $product;
        }

        $this->load();

        $options = [];
        if (isset($this->_optionsByProduct[$productId])) {
            foreach ($this->_optionsByProduct[$productId] as $optionId) {
                $options[] = $this->_items[$optionId];
            }
        }

        return $options;
    }
}
