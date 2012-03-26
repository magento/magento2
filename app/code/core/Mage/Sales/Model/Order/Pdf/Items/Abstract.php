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
 * @package     Mage_Sales
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Sales Order Pdf Items renderer Abstract
 *
 * @category   Mage
 * @package    Mage_Sales
 * @author     Magento Core Team <core@magentocommerce.com>
 */
abstract class Mage_Sales_Model_Order_Pdf_Items_Abstract extends Mage_Core_Model_Abstract
{
    /**
     * Order model
     *
     * @var Mage_Sales_Model_Order
     */
    protected $_order;

    /**
     * Source model (invoice, shipment, creditmemo)
     *
     * @var Mage_Core_Model_Abstract
     */
    protected $_source;

    /**
     * Item object
     *
     * @var Varien_Object
     */
    protected $_item;

    /**
     * Pdf object
     *
     * @var Mage_Sales_Model_Order_Pdf_Abstract
     */
    protected $_pdf;

    /**
     * Pdf current page
     *
     * @var Zend_Pdf_Page
     */
    protected $_pdfPage;

    /**
     * Set order model
     *
     * @param  Mage_Sales_Model_Order $order
     * @return Mage_Sales_Model_Order_Pdf_Items_Abstract
     */
    public function setOrder(Mage_Sales_Model_Order $order)
    {
        $this->_order = $order;
        return $this;
    }

    /**
     * Set Source model
     *
     * @param  Mage_Core_Model_Abstract $source
     * @return Mage_Sales_Model_Order_Pdf_Items_Abstract
     */
    public function setSource(Mage_Core_Model_Abstract $source)
    {
        $this->_source = $source;
        return $this;
    }

    /**
     * Set item object
     *
     * @param  Varien_Object $item
     * @return Mage_Sales_Model_Order_Pdf_Items_Abstract
     */
    public function setItem(Varien_Object $item)
    {
        $this->_item = $item;
        return $this;
    }

    /**
     * Set Pdf model
     *
     * @param  Mage_Sales_Model_Order_Pdf_Abstract $pdf
     * @return Mage_Sales_Model_Order_Pdf_Items_Abstract
     */
    public function setPdf(Mage_Sales_Model_Order_Pdf_Abstract $pdf)
    {
        $this->_pdf = $pdf;
        return $this;
    }

    /**
     * Set current page
     *
     * @param  Zend_Pdf_Page $page
     * @return Mage_Sales_Model_Order_Pdf_Items_Abstract
     */
    public function setPage(Zend_Pdf_Page $page)
    {
        $this->_pdfPage = $page;
        return $this;
    }

    /**
     * Retrieve order object
     *
     * @throws Mage_Core_Exception
     * @return Mage_Sales_Model_Order
     */
    public function getOrder()
    {
        if (is_null($this->_order)) {
            Mage::throwException(Mage::helper('Mage_Sales_Helper_Data')->__('Order object is not specified.'));
        }
        return $this->_order;
    }

    /**
     * Retrieve source object
     *
     * @throws Mage_Core_Exception
     * @return Mage_Core_Model_Abstract
     */
    public function getSource()
    {
        if (is_null($this->_source)) {
            Mage::throwException(Mage::helper('Mage_Sales_Helper_Data')->__('Source object is not specified.'));
        }
        return $this->_source;
    }

    /**
     * Retrieve item object
     *
     * @throws Mage_Core_Exception
     * @return Varien_Object
     */
    public function getItem()
    {
        if (is_null($this->_item)) {
            Mage::throwException(Mage::helper('Mage_Sales_Helper_Data')->__('Item object is not specified.'));
        }
        return $this->_item;
    }

    /**
     * Retrieve Pdf model
     *
     * @throws Mage_Core_Exception
     * @return Mage_Sales_Model_Order_Pdf_Abstract
     */
    public function getPdf()
    {
        if (is_null($this->_pdf)) {
            Mage::throwException(Mage::helper('Mage_Sales_Helper_Data')->__('PDF object is not specified.'));
        }
        return $this->_pdf;
    }

    /**
     * Retrieve Pdf page object
     *
     * @throws Mage_Core_Exception
     * @return Zend_Pdf_Page
     */
    public function getPage()
    {
        if (is_null($this->_pdfPage)) {
            Mage::throwException(Mage::helper('Mage_Sales_Helper_Data')->__('PDF page object is not specified.'));
        }
        return $this->_pdfPage;
    }

    /**
     * Draw item line
     *
     */
    abstract public function draw();

    /**
     * Format option value process
     *
     * @param  $value
     * @return string
     */
    protected function _formatOptionValue($value)
    {
        $order = $this->getOrder();

        $resultValue = '';
        if (is_array($value)) {
            if (isset($value['qty'])) {
                $resultValue .= sprintf('%d', $value['qty']) . ' x ';
            }

            $resultValue .= $value['title'];

            if (isset($value['price'])) {
                $resultValue .= " " . $order->formatPrice($value['price']);
            }
            return  $resultValue;
        } else {
            return $value;
        }
    }

    /**
     * Get array of arrays with item prices information for display in PDF
     * array(
     *  $index => array(
     *      'label'    => $label,
     *      'price'    => $price,
     *      'subtotal' => $subtotal
     *  )
     * )
     * @return array
     */
    public function getItemPricesForDisplay()
    {
        $order = $this->getOrder();
        $item  = $this->getItem();
        if (Mage::helper('Mage_Tax_Helper_Data')->displaySalesBothPrices()) {
            $prices = array(
                array(
                    'label'    => Mage::helper('Mage_Tax_Helper_Data')->__('Excl. Tax') . ':',
                    'price'    => $order->formatPriceTxt($item->getPrice()),
                    'subtotal' => $order->formatPriceTxt($item->getRowTotal())
                ),
                array(
                    'label'    => Mage::helper('Mage_Tax_Helper_Data')->__('Incl. Tax') . ':',
                    'price'    => $order->formatPriceTxt($item->getPriceInclTax()),
                    'subtotal' => $order->formatPriceTxt($item->getRowTotalInclTax())
                ),
            );
        } elseif (Mage::helper('Mage_Tax_Helper_Data')->displaySalesPriceInclTax()) {
            $prices = array(array(
                'price' => $order->formatPriceTxt($item->getPriceInclTax()),
                'subtotal' => $order->formatPriceTxt($item->getRowTotalInclTax()),
            ));
        } else {
            $prices = array(array(
                'price' => $order->formatPriceTxt($item->getPrice()),
                'subtotal' => $order->formatPriceTxt($item->getRowTotal()),
            ));
        }
        return $prices;
    }

    /**
     * Retrieve item options
     *
     * @return array
     */
    public function getItemOptions() {
        $result = array();
        if ($options = $this->getItem()->getOrderItem()->getProductOptions()) {
            if (isset($options['options'])) {
                $result = array_merge($result, $options['options']);
            }
            if (isset($options['additional_options'])) {
                $result = array_merge($result, $options['additional_options']);
            }
            if (isset($options['attributes_info'])) {
                $result = array_merge($result, $options['attributes_info']);
            }
        }
        return $result;
    }

    /**
     * Set font as regular
     *
     * @param  int $size
     * @return Zend_Pdf_Resource_Font
     */
    protected function _setFontRegular($size = 7)
    {
        $font = Zend_Pdf_Font::fontWithPath(Mage::getBaseDir() . '/lib/LinLibertineFont/LinLibertine_Re-4.4.1.ttf');
        $this->getPage()->setFont($font, $size);
        return $font;
    }

    /**
     * Set font as bold
     *
     * @param  int $size
     * @return Zend_Pdf_Resource_Font
     */
    protected function _setFontBold($size = 7)
    {
        $font = Zend_Pdf_Font::fontWithPath(Mage::getBaseDir() . '/lib/LinLibertineFont/LinLibertine_Bd-2.8.1.ttf');
        $this->getPage()->setFont($font, $size);
        return $font;
    }

    /**
     * Set font as italic
     *
     * @param  int $size
     * @return Zend_Pdf_Resource_Font
     */
    protected function _setFontItalic($size = 7)
    {
        $font = Zend_Pdf_Font::fontWithPath(Mage::getBaseDir() . '/lib/LinLibertineFont/LinLibertine_It-2.8.2.ttf');
        $this->getPage()->setFont($font, $size);
        return $font;
    }

    /**
     * Return item Sku
     *
     * @param  $item
     * @return mixed
     */
    public function getSku($item)
    {
        if ($item->getOrderItem()->getProductOptionByCode('simple_sku'))
            return $item->getOrderItem()->getProductOptionByCode('simple_sku');
        else
            return $item->getSku();
    }
}
