<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Block\Product\Compare;

use Magento\Catalog\Model\Product\Compare\Item as CompareItem;

/**
 * Catalog Compare Products Sidebar Block
 */
class Sidebar extends \Magento\Catalog\Block\Product\Compare\AbstractCompare implements
    \Magento\Framework\View\Block\IdentityInterface
{
    /**
     * The property is used to define content-scope of block. Can be private or public.
     *
     * @var bool
     */
     protected $_isScopePrivate = true;

    /**
     * Compare Products Collection
     *
     * @var null|\Magento\Catalog\Model\Resource\Product\Compare\Item\Collection
     */
    protected $_itemsCollection = null;

    /**
     * Initialize block
     *
     * @return void
     */
    protected function _construct()
    {
        $this->setId('compare');
    }

    /**
     * Retrieve Compare Products Collection
     *
     * @return \Magento\Catalog\Model\Resource\Product\Compare\Item\Collection
     */
    public function getItems()
    {
        if ($this->_itemsCollection) {
            return $this->_itemsCollection;
        }
        return $this->_getHelper()->getItemCollection();
    }

    /**
     * Set Compare Products Collection
     *
     * @param \Magento\Catalog\Model\Resource\Product\Compare\Item\Collection $collection
     * @return \Magento\Catalog\Block\Product\Compare\Sidebar
     */
    public function setItems($collection)
    {
        $this->_itemsCollection = $collection;
        return $this;
    }

    /**
     * Retrieve compare product helper
     *
     * @return \Magento\Catalog\Helper\Product\Compare
     */
    public function getCompareProductHelper()
    {
        return $this->_getHelper();
    }

    /**
     * Retrieve Clean Compared Items URL
     *
     * @return string
     */
    public function getClearUrl()
    {
        return $this->_getHelper()->getClearListUrl();
    }

    /**
     * Retrieve Full Compare page URL
     *
     * @return string
     */
    public function getCompareUrl()
    {
        return $this->_getHelper()->getListUrl();
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
            $product = $item->getProduct();
            if ($product instanceof \Magento\Framework\Object\IdentityInterface) {
                $identities = array_merge($identities, $product->getIdentities());
            }
        }
        if ($this->getCatalogCompareItemId()) {
            $identities[] = CompareItem::CACHE_TAG . '_' . $this->getCatalogCompareItemId();
        }
        return $identities;
    }
}
