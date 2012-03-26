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
 * @category    Mage
 * @package     Mage_ProductAlert
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Product view price and stock alerts
 */
class Mage_ProductAlert_Block_Product_View extends Mage_Core_Block_Template
{
    /**
     * Current product instance
     *
     * @var null|Mage_Catalog_Model_Product
     */
    protected $_product = null;

    /**
     * Helper instance
     *
     * @var null|Mage_ProductAlert_Helper_Data
     */
    protected $_helper = null;

    /**
     * Check whether the stock alert data can be shown and prepare related data
     *
     * @return void
     */
    public function prepareStockAlertData()
    {
        if (!$this->_getHelper()->isStockAlertAllowed() || !$this->_product || $this->_product->isAvailable()) {
            $this->setTemplate('');
            return;
        }
        $this->setSignupUrl($this->_getHelper()->getSaveUrl('stock'));
    }

    /**
     * Check whether the price alert data can be shown and prepare related data
     *
     * @return void
     */
    public function preparePriceAlertData()
    {
        if (!$this->_getHelper()->isPriceAlertAllowed()
            || !$this->_product || false === $this->_product->getCanShowPrice()
        ) {
            $this->setTemplate('');
            return;
        }
        $this->setSignupUrl($this->_getHelper()->getSaveUrl('price'));
    }

    /**
     * Get current product instance
     *
     * @return Mage_ProductAlert_Block_Product_View
     */
    protected function _prepareLayout()
    {
        $product = Mage::registry('current_product');
        if ($product && $product->getId()) {
            $this->_product = $product;
        }

        return parent::_prepareLayout();
    }

    /**
     * Retrieve helper instance
     *
     * @return Mage_ProductAlert_Helper_Data|null
     */
    protected function _getHelper()
    {
        if (is_null($this->_helper)) {
            $this->_helper = Mage::helper('Mage_ProductAlert_Helper_Data');
        }
        return $this->_helper;
    }
}
