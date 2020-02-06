<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Block\Product\ProductList;

use Magento\Catalog\Model\ResourceModel\Product\Collection;

/**
 * Catalog product upsell items block
 *
 * @api
 * @SuppressWarnings(PHPMD.LongVariable)
 * @since 100.0.2
 */
class Upsell extends \Magento\Catalog\Block\Product\AbstractProduct implements
    \Magento\Framework\DataObject\IdentityInterface
{
    /**
     * @var int
     */
    protected $_columnCount = 4;

    /**
     * @var  \Magento\Framework\DataObject[]
     */
    protected $_items;

    /**
     * @var Collection
     */
    protected $_itemCollection;

    /**
     * @var array
     */
    protected $_itemLimits = [];

    /**
     * Checkout session
     *
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * Catalog product visibility
     *
     * @var \Magento\Catalog\Model\Product\Visibility
     */
    protected $_catalogProductVisibility;

    /**
     * Checkout cart
     *
     * @var \Magento\Checkout\Model\ResourceModel\Cart
     */
    protected $_checkoutCart;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param \Magento\Checkout\Model\ResourceModel\Cart $checkoutCart
     * @param \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Checkout\Model\ResourceModel\Cart $checkoutCart,
        \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Module\Manager $moduleManager,
        array $data = []
    ) {
        $this->_checkoutCart = $checkoutCart;
        $this->_catalogProductVisibility = $catalogProductVisibility;
        $this->_checkoutSession = $checkoutSession;
        $this->moduleManager = $moduleManager;
        parent::__construct(
            $context,
            $data
        );
    }

    /**
     * Prepare data
     *
     * @return $this
     */
    protected function _prepareData()
    {
        $product = $this->getProduct();
        /* @var $product \Magento\Catalog\Model\Product */
        $this->_itemCollection = $product->getUpSellProductCollection()->setPositionOrder()->addStoreFilter();
        if ($this->moduleManager->isEnabled('Magento_Checkout')) {
            $this->_addProductAttributesAndPrices($this->_itemCollection);
        }
        $this->_itemCollection->setVisibility($this->_catalogProductVisibility->getVisibleInCatalogIds());

        $this->_itemCollection->load();

        /**
         * Updating collection with desired items
         */
        $this->_eventManager->dispatch(
            'catalog_product_upsell',
            ['product' => $product, 'collection' => $this->_itemCollection, 'limit' => null]
        );

        foreach ($this->_itemCollection as $product) {
            $product->setDoNotUseCategoryId(true);
        }

        return $this;
    }

    /**
     * Before to html handler
     *
     * @return $this
     */
    protected function _beforeToHtml()
    {
        $this->_prepareData();
        return parent::_beforeToHtml();
    }

    /**
     * Get items collection
     *
     * @return Collection
     */
    public function getItemCollection()
    {
        /**
         * getIdentities() depends on _itemCollection populated, but it can be empty if the block is hidden
         * @see https://github.com/magento/magento2/issues/5897
         */
        if ($this->_itemCollection === null) {
            $this->_prepareData();
        }
        return $this->_itemCollection;
    }

    /**
     * Get collection items
     *
     * @return \Magento\Framework\DataObject[]
     */
    public function getItems()
    {
        if ($this->_items === null) {
            $this->_items = $this->getItemCollection()->getItems();
        }
        return $this->_items;
    }

    /**
     * Get row count
     *
     * @return float
     */
    public function getRowCount()
    {
        return ceil(count($this->getItemCollection()->getItems()) / $this->getColumnCount());
    }

    /**
     * Set column count
     *
     * @param string $columns
     * @return $this
     */
    public function setColumnCount($columns)
    {
        if ((int) $columns > 0) {
            $this->_columnCount = (int) $columns;
        }
        return $this;
    }

    /**
     * Get column count
     *
     * @return int
     */
    public function getColumnCount()
    {
        return $this->_columnCount;
    }

    /**
     * Reset items iterator
     *
     * @return void
     */
    public function resetItemsIterator()
    {
        $this->getItems();
        reset($this->_items);
    }

    /**
     * Get iterable item
     *
     * @return mixed
     */
    public function getIterableItem()
    {
        $item = current($this->_items);
        next($this->_items);
        return $item;
    }

    /**
     * Set how many items we need to show in upsell block
     *
     * Notice: this parameter will be also applied
     *
     * @param string $type
     * @param int $limit
     * @return \Magento\Catalog\Block\Product\ProductList\Upsell
     */
    public function setItemLimit($type, $limit)
    {
        if ((int) $limit > 0) {
            $this->_itemLimits[$type] = (int) $limit;
        }
        return $this;
    }

    /**
     * Get item limit
     *
     * @param string $type
     * @return array|int
     */
    public function getItemLimit($type = '')
    {
        if ($type == '') {
            return $this->_itemLimits;
        }
        if (isset($this->_itemLimits[$type])) {
            return $this->_itemLimits[$type];
        } else {
            return 0;
        }
    }

    /**
     * Return identifiers for produced content
     *
     * @return array
     */
    public function getIdentities()
    {
        $identities = [];
        foreach ($this->getItems() as $item) {
            // phpcs:ignore Magento2.Performance.ForeachArrayMerge
            $identities = array_merge($identities, $item->getIdentities());
        }
        return $identities;
    }
}
