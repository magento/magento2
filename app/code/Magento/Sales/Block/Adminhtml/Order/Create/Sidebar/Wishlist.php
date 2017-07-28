<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Block\Adminhtml\Order\Create\Sidebar;

/**
 * Adminhtml sales order create sidebar wishlist block
 *
 * @api
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 2.0.0
 */
class Wishlist extends \Magento\Sales\Block\Adminhtml\Order\Create\Sidebar\AbstractSidebar
{
    /**
     * Storage action on selected item
     *
     * @var string
     * @since 2.0.0
     */
    protected $_sidebarStorageAction = 'add_wishlist_item';

    /**
     * Constructor
     *
     * @return void
     * @since 2.0.0
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
     * @return \Magento\Framework\Phrase
     * @since 2.0.0
     */
    public function getHeaderText()
    {
        return __('Wish List');
    }

    /**
     * Retrieve item collection
     *
     * @return mixed
     * @since 2.0.0
     */
    public function getItemCollection()
    {
        $collection = $this->getData('item_collection');
        if ($collection === null) {
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
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function getProductId($item)
    {
        return $item->getProduct()->getId();
    }

    /**
     * Retrieve identifier of block item
     *
     * @param \Magento\Framework\DataObject $item
     * @return int
     * @since 2.0.0
     */
    public function getIdentifierId($item)
    {
        return $item->getId();
    }

    /**
     * Retrieve possibility to display quantity column in grid of wishlist block
     *
     * @return true
     * @since 2.0.0
     */
    public function canDisplayItemQty()
    {
        return true;
    }
}
