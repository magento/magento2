<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Block\Adminhtml\Order\Create\Sidebar;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Pricing\Price\FinalPrice;
use Magento\Store\Model\ScopeInterface;

/**
 * Adminhtml sales order create sidebar cart block
 *
 * @api
 * @since 100.0.2
 */
class Cart extends \Magento\Sales\Block\Adminhtml\Order\Create\Sidebar\AbstractSidebar
{
    /**
     * Storage action on selected item
     *
     * @var string
     */
    protected $_sidebarStorageAction = 'add_cart_item';

    /**
     * Constructor
     *
     * @return void
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
     */
    public function getHeaderText()
    {
        return __('Shopping Cart');
    }

    /**
     * Retrieve item collection
     *
     * @return mixed
     */
    public function getItemCollection()
    {
        $collection = $this->getData('item_collection');
        if ($collection === null) {
            $collection = $this->getCreateOrderModel()->getCustomerCart()->getAllVisibleItems();
            $transferredItems = $this->getCreateOrderModel()->getSession()->getTransferredItems() ?? [];
            $transferredItems = $transferredItems[$this->getDataId()] ?? [];
            if (!empty($transferredItems)) {
                foreach ($collection as $key => $item) {
                    if (in_array($item->getId(), $transferredItems)) {
                        unset($collection[$key]);
                    }
                }
            }

            $this->setData('item_collection', $collection);
        }
        return $collection;
    }

    /**
     * @inheritdoc
     * @since 102.0.1
     */
    public function getItemPrice(Product $product)
    {
        $customPrice = $this->getCartItemCustomPrice($product);

        return $customPrice !== null
            ? $this->convertPrice($customPrice)
            : $this->priceCurrency->format($product->getPriceInfo()->getPrice(FinalPrice::PRICE_CODE)->getValue());
    }

    /**
     * Retrieve display item qty availability
     *
     * @return true
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

    /**
     * Returns cart item custom price.
     *
     * @param Product $product
     * @return float|null
     */
    private function getCartItemCustomPrice(Product $product): ?float
    {
        $items = $this->getItemCollection();
        foreach ($items as $item) {
            $productItemId = $this->getProduct($item)->getId();
            if ($productItemId === $product->getId() && $item->getCustomPrice()) {
                return (float)$item->getCustomPrice();
            }
        }

        return null;
    }

    /**
     * @inheritdoc
     * @since 102.0.4
     */
    public function getItemCount()
    {
        $count = $this->getData('item_count');
        if ($count === null) {
            $useQty = $this->_scopeConfig->getValue(
                'checkout/cart_link/use_qty',
                ScopeInterface::SCOPE_STORE
            );
            $allItems = $this->getItems();
            if ($useQty) {
                $count = 0;
                foreach ($allItems as $item) {
                    $count += $item->getQty();
                }
            } else {
                $count = count($allItems);
            }
            $this->setData('item_count', $count);
        }

        return $count;
    }
}
