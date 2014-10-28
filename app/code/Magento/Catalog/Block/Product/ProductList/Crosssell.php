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

/**
 * Catalog product related items block
 */
namespace Magento\Catalog\Block\Product\ProductList;

class Crosssell extends \Magento\Catalog\Block\Product\AbstractProduct
{
    /**
     * Crosssell item collection
     *
     * @var \Magento\Catalog\Model\Resource\Product\Link\Product\Collection
     */
    protected $_itemCollection;

    /**
     * Prepare crosssell items data
     *
     * @return \Magento\Catalog\Block\Product\ProductList\Crosssell
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
     */
    protected function _beforeToHtml()
    {
        $this->_prepareData();
        return parent::_beforeToHtml();
    }

    /**
     * Retrieve crosssell items collection
     *
     * @return \Magento\Catalog\Model\Resource\Product\Link\Product\Collection
     */
    public function getItems()
    {
        return $this->_itemCollection;
    }
}
