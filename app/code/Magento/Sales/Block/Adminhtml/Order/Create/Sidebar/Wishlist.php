<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Block\Adminhtml\Order\Create\Sidebar;

/**
 * Adminhtml sales order create sidebar wishlist block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Wishlist extends \Magento\Sales\Block\Adminhtml\Order\Create\Sidebar\AbstractSidebar
{
    /**
     * Storage action on selected item
     *
     * @var string
     */
    protected $_sidebarStorageAction = 'add_wishlist_item';

    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('sales_order_create_sidebar_wishlist');
        $this->setDataId('wishlist');
    }

    /**
     * Get header text
     *
     * @return string
     */
    public function getHeaderText()
    {
        return __('Wish List');
    }

    /**
     * Retrieve item collection
     *
     * @return mixed
     */
    public function getItemCollection()
    {
        $collection = $this->getData('item_collection');
        if (is_null($collection)) {
            $collection = $this->getCreateOrderModel()->getCustomerWishlist(true);
            if ($collection) {
                $collection = $collection->getItemCollection()->load();
            }
            $this->setData('item_collection', $collection);
        }
        return $collection;
    }

    /**
     * Retrieve all items
     *
     * @return array
     */
    public function getItems()
    {
        $items = parent::getItems();
        foreach ($items as $item) {
            $product = $item->getProduct();
            $item->setName($product->getName());
            $item->setPrice($product->getFinalPrice(1));
            $item->setTypeId($product->getTypeId());
        }
        return $items;
    }

    /**
     * Retrieve product identifier linked with item
     *
     * @param \Magento\Wishlist\Model\Item $item
     * @return int
     */
    public function getProductId($item)
    {
        return $item->getProduct()->getId();
    }

    /**
     * Retrieve identifier of block item
     *
     * @param \Magento\Framework\Object $item
     * @return int
     */
    public function getIdentifierId($item)
    {
        return $item->getId();
    }

    /**
     * Retrieve possibility to display quantity column in grid of wishlist block
     *
     * @return true
     */
    public function canDisplayItemQty()
    {
        return true;
    }
}
