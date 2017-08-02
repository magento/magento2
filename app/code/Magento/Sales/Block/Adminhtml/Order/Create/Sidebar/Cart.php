<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Block\Adminhtml\Order\Create\Sidebar;

/**
 * Adminhtml sales order create sidebar cart block
 *
 * @api
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 2.0.0
 */
class Cart extends \Magento\Sales\Block\Adminhtml\Order\Create\Sidebar\AbstractSidebar
{
    /**
     * Storage action on selected item
     *
     * @var string
     * @since 2.0.0
     */
    protected $_sidebarStorageAction = 'add_cart_item';

    /**
     * Constructor
     *
     * @return void
     * @since 2.0.0
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('sales_order_create_sidebar_cart');
        $this->setDataId('cart');
    }

    /**
     * Get header text
     *
     * @return \Magento\Framework\Phrase
     * @since 2.0.0
     */
    public function getHeaderText()
    {
        return __('Shopping Cart');
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
            $collection = $this->getCreateOrderModel()->getCustomerCart()->getAllVisibleItems();
            $this->setData('item_collection', $collection);
        }
        return $collection;
    }

    /**
     * Retrieve display item qty availability
     *
     * @return true
     * @since 2.0.0
     */
    public function canDisplayItemQty()
    {
        return true;
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
     * Retrieve product identifier linked with item
     *
     * @param \Magento\Quote\Model\Quote\Item $item
     * @return int
     * @since 2.0.0
     */
    public function getProductId($item)
    {
        return $item->getProduct()->getId();
    }

    /**
     * Prepare layout
     *
     * Add button that clears customer's shopping cart
     *
     * @return $this
     * @since 2.0.0
     */
    protected function _prepareLayout()
    {
        $deleteAllConfirmString = __('Are you sure you want to delete all items from shopping cart?');
        $this->addChild(
            'empty_customer_cart_button',
            \Magento\Backend\Block\Widget\Button::class,
            [
                'label' => __('Clear Shopping Cart'),
                'onclick' => 'order.clearShoppingCart(\'' . $deleteAllConfirmString . '\')'
            ]
        );

        return parent::_prepareLayout();
    }
}
