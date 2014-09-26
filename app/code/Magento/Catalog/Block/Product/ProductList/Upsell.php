<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Catalog\Block\Product\ProductList;

use Magento\Catalog\Model\Resource\Product\Collection;
use Magento\Framework\View\Element\AbstractBlock;

/**
 * Catalog product upsell items block
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class Upsell extends \Magento\Catalog\Block\Product\AbstractProduct implements \Magento\Framework\View\Block\IdentityInterface
{
    /**
     * @var int
     */
    protected $_columnCount = 4;

    /**
     * @var  \Magento\Framework\Object[]
     */
    protected $_items;

    /**
     * @var Collection
     */
    protected $_itemCollection;

    /**
     * @var array
     */
    protected $_itemLimits = array();

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
     * @var \Magento\Checkout\Model\Resource\Cart
     */
    protected $_checkoutCart;

    /**
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param \Magento\Checkout\Model\Resource\Cart $checkoutCart
     * @param \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Checkout\Model\Resource\Cart $checkoutCart,
        \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility,
        \Magento\Checkout\Model\Session $checkoutSession,
        array $data = array()
    ) {
        $this->_checkoutCart = $checkoutCart;
        $this->_catalogProductVisibility = $catalogProductVisibility;
        $this->_checkoutSession = $checkoutSession;
        parent::__construct(
            $context,
            $data
        );
    }

    /**
     * @return $this
     */
    protected function _prepareData()
    {
        $product = $this->_coreRegistry->registry('product');
        /* @var $product \Magento\Catalog\Model\Product */
        $this->_itemCollection = $product->getUpSellProductCollection()->setPositionOrder()->addStoreFilter();
        if ($this->_catalogData->isModuleEnabled('Magento_Checkout')) {
            $this->_addProductAttributesAndPrices($this->_itemCollection);
        }
        $this->_itemCollection->setVisibility($this->_catalogProductVisibility->getVisibleInCatalogIds());

        $this->_itemCollection->load();

        /**
         * Updating collection with desired items
         */
        $this->_eventManager->dispatch(
            'catalog_product_upsell',
            array('product' => $product, 'collection' => $this->_itemCollection, 'limit' => null)
        );

        foreach ($this->_itemCollection as $product) {
            $product->setDoNotUseCategoryId(true);
        }

        return $this;
    }

    /**
     * @return $this
     */
    protected function _beforeToHtml()
    {
        $this->_prepareData();
        return parent::_beforeToHtml();
    }

    /**
     * @return Collection
     */
    public function getItemCollection()
    {
        return $this->_itemCollection;
    }

    /**
     * @return \Magento\Framework\Object[]
     */
    public function getItems()
    {
        if (is_null($this->_items)) {
            $this->_items = $this->getItemCollection()->getItems();
        }
        return $this->_items;
    }

    /**
     * @return float
     */
    public function getRowCount()
    {
        return ceil(count($this->getItemCollection()->getItems()) / $this->getColumnCount());
    }

    /**
     * @param string $columns
     * @return $this
     */
    public function setColumnCount($columns)
    {
        if (intval($columns) > 0) {
            $this->_columnCount = intval($columns);
        }
        return $this;
    }

    /**
     * @return int
     */
    public function getColumnCount()
    {
        return $this->_columnCount;
    }

    /**
     * @return void
     */
    public function resetItemsIterator()
    {
        $this->getItems();
        reset($this->_items);
    }

    /**
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
     * Notice: this parameter will be also applied
     *
     * @param string $type
     * @param int $limit
     * @return \Magento\Catalog\Block\Product\ProductList\Upsell
     */
    public function setItemLimit($type, $limit)
    {
        if (intval($limit) > 0) {
            $this->_itemLimits[$type] = intval($limit);
        }
        return $this;
    }

    /**
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
        $identities = array();
        foreach ($this->getItems() as $item) {
            $identities = array_merge($identities, $item->getIdentities());
        }
        return $identities;
    }
}
