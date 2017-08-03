<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Catalog product related items block
 */
namespace Magento\Catalog\Block\Product\ProductList;

/**
 * Class \Magento\Catalog\Block\Product\ProductList\Crosssell
 *
 * @since 2.0.0
 */
class Crosssell extends \Magento\Catalog\Block\Product\AbstractProduct
{
    /**
     * Crosssell item collection
     *
     * @var \Magento\Catalog\Model\ResourceModel\Product\Link\Product\Collection
     * @since 2.0.0
     */
    protected $_itemCollection;

    /**
     * Prepare crosssell items data
     *
     * @return \Magento\Catalog\Block\Product\ProductList\Crosssell
     * @since 2.0.0
     */
    protected function _prepareData()
    {
        $product = $this->_coreRegistry->registry('product');
        /* @var $product \Magento\Catalog\Model\Product */

        $this->_itemCollection = $product->getCrossSellProductCollection()->addAttributeToSelect(
            $this->_catalogConfig->getProductAttributes()
        )->setPositionOrder()->addStoreFilter();

        $this->_itemCollection->load();

        foreach ($this->_itemCollection as $product) {
            $product->setDoNotUseCategoryId(true);
        }

        return $this;
    }

    /**
     * Before rendering html process
     * Prepare items collection
     *
     * @return \Magento\Catalog\Block\Product\ProductList\Crosssell
     * @since 2.0.0
     */
    protected function _beforeToHtml()
    {
        $this->_prepareData();
        return parent::_beforeToHtml();
    }

    /**
     * Retrieve crosssell items collection
     *
     * @return \Magento\Catalog\Model\ResourceModel\Product\Link\Product\Collection
     * @since 2.0.0
     */
    public function getItems()
    {
        return $this->_itemCollection;
    }
}
