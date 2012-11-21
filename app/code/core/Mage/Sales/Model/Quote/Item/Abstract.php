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
 * Quote item abstract model
 *
 * Price attributes:
 *  - price - initial item price, declared during product association
 *  - original_price - product price before any calculations
 *  - calculation_price - prices for item totals calculation
 *  - custom_price - new price that can be declared by user and recalculated during calculation process
 *  - original_custom_price - original defined value of custom price without any convertion
 *
 * @category   Mage
 * @package    Mage_Sales
 * @author      Magento Core Team <core@magentocommerce.com>
 */
abstract class Mage_Sales_Model_Quote_Item_Abstract extends Mage_Core_Model_Abstract
    implements Mage_Catalog_Model_Product_Configuration_Item_Interface
{
    protected $_parentItem  = null;
    protected $_children    = array();
    protected $_messages    = array();

    /**
     * List of custom options
     *
     * @var array
     */
    protected $_optionsByCode;

    /**
     * Retrieve Quote instance
     *
     * @return Mage_Sales_Model_Quote
     */
    abstract function getQuote();

    /**
     * Retrieve address model
     *
     * @return Mage_Sales_Model_Quote_Address
     */
    abstract function getAddress();

    /**
     * Retrieve product model object associated with item
     *
     * @return Mage_Catalog_Model_Product
     */
    public function getProduct()
    {
        $product = $this->_getData('product');
        if (($product === null) && $this->getProductId()) {
            $product = Mage::getModel('Mage_Catalog_Model_Product')
                ->setStoreId($this->getQuote()->getStoreId())
                ->load($this->getProductId());
            $this->setProduct($product);
        }

        /**
         * Reset product final price because it related to custom options
         */
        $product->setFinalPrice(null);
        if (is_array($this->_optionsByCode)) {
            $product->setCustomOptions($this->_optionsByCode);
        }
        return $product;
    }

    /**
     * Returns special download params (if needed) for custom option with type = 'file'
     * Needed to implement Mage_Catalog_Model_Product_Configuration_Item_Interface.
     * Return null, as quote item needs no additional configuration.
     *
     * @return null|Varien_Object
     */
    public function getFileDownloadParams()
    {
        return null;
    }

    /**
     * Specify parent item id before saving data
     *
     * @return  Mage_Sales_Model_Quote_Item_Abstract
     */
    protected function _beforeSave()
    {
        parent::_beforeSave();
        if ($this->getParentItem()) {
            $this->setParentItemId($this->getParentItem()->getId());
        }
        return $this;
    }


    /**
     * Set parent item
     *
     * @param  Mage_Sales_Model_Quote_Item $parentItem
     * @return Mage_Sales_Model_Quote_Item
     */
    public function setParentItem($parentItem)
    {
        if ($parentItem) {
            $this->_parentItem = $parentItem;
            $parentItem->addChild($this);
        }
        return $this;
    }

    /**
     * Get parent item
     *
     * @return Mage_Sales_Model_Quote_Item
     */
    public function getParentItem()
    {
        return $this->_parentItem;
    }

    /**
     * Get chil items
     *
     * @return array
     */
    public function getChildren()
    {
        return $this->_children;
    }

    /**
     * Add child item
     *
     * @param  Mage_Sales_Model_Quote_Item_Abstract $child
     * @return Mage_Sales_Model_Quote_Item_Abstract
     */
    public function addChild($child)
    {
        $this->setHasChildren(true);
        $this->_children[] = $child;
        return $this;
    }

    /**
     * Adds message(s) for quote item. Duplicated messages are not added.
     *
     * @param  mixed $messages
     * @return Mage_Sales_Model_Quote_Item_Abstract
     */
    public function setMessage($messages)
    {
        $messagesExists = $this->getMessage(false);
        if (!is_array($messages)) {
            $messages = array($messages);
        }
        foreach ($messages as $message) {
            if (!in_array($message, $messagesExists)) {
                $this->addMessage($message);
            }
        }
        return $this;
    }

    /**
     * Add message of quote item to array of messages
     *
     * @param   string $message
     * @return  Mage_Sales_Model_Quote_Item_Abstract
     */
    public function addMessage($message)
    {
        $this->_messages[] = $message;
        return $this;
    }

    /**
     * Get messages array of quote item
     *
     * @param   bool $string flag for converting messages to string
     * @return  array|string
     */
    public function getMessage($string = true)
    {
        if ($string) {
            return join("\n", $this->_messages);
        }
        return $this->_messages;
    }

    /**
     * Removes message by text
     *
     * @param string $text
     * @return Mage_Sales_Model_Quote_Item_Abstract
     */
    public function removeMessageByText($text)
    {
        foreach ($this->_messages as $key => $message) {
            if ($message == $text) {
                unset($this->_messages[$key]);
            }
        }
        return $this;
    }

    /**
     * Clears all messages
     *
     * @return Mage_Sales_Model_Quote_Item_Abstract
     */
    public function clearMessage()
    {
        $this->unsMessage(); // For older compatibility, when we kept message inside data array
        $this->_messages = array();
        return $this;
    }

    /**
     * Retrieve store model object
     *
     * @return Mage_Core_Model_Store
     */
    public function getStore()
    {
        return $this->getQuote()->getStore();
    }

    /**
     * Checking item data
     *
     * @return Mage_Sales_Model_Quote_Item_Abstract
     */
    public function checkData()
    {
        $this->setHasError(false);
        $this->clearMessage();

        $qty = $this->_getData('qty');

        try {
            $this->setQty($qty);
        } catch (Mage_Core_Exception $e){
            $this->setHasError(true);
            $this->setMessage($e->getMessage());
        } catch (Exception $e){
            $this->setHasError(true);
            $this->setMessage(Mage::helper('Mage_Sales_Helper_Data')->__('Item qty declaration error.'));
        }

        try {
            $this->getProduct()->getTypeInstance()->checkProductBuyState($this->getProduct());
        } catch (Mage_Core_Exception $e) {
            $this->setHasError(true)
                ->setMessage($e->getMessage());
            $this->getQuote()->setHasError(true)
                ->addMessage(Mage::helper('Mage_Sales_Helper_Data')->__('Some of the products below do not have all the required options.'));
        } catch (Exception $e) {
            $this->setHasError(true)
                ->setMessage(Mage::helper('Mage_Sales_Helper_Data')->__('Item options declaration error.'));
            $this->getQuote()->setHasError(true)
                ->addMessage(Mage::helper('Mage_Sales_Helper_Data')->__('Items options declaration error.'));
        }

        if ($this->getProduct()->getHasError()) {
            $this->setHasError(true)
                ->setMessage(Mage::helper('Mage_Sales_Helper_Data')->__('Some of the selected options are not currently available.'));
            $this->getQuote()->setHasError(true)
                ->addMessage($this->getProduct()->getMessage(), 'options');
        }

        if ($this->getHasConfigurationUnavailableError()) {
            $this->setHasError(true)
                ->setMessage(Mage::helper('Mage_Sales_Helper_Data')->__('Selected option(s) or their combination is not currently available.'));
            $this->getQuote()->setHasError(true)
                ->addMessage(Mage::helper('Mage_Sales_Helper_Data')->__('Some item options or their combination are not currently available.'), 'unavailable-configuration');
            $this->unsHasConfigurationUnavailableError();
        }

        return $this;
    }

    /**
     * Get original (not related with parent item) item quantity
     *
     * @return  int|float
     */
    public function getQty()
    {
        return $this->_getData('qty');
    }

    /**
     * Get total item quantity (include parent item relation)
     *
     * @return  int|float
     */
    public function getTotalQty()
    {
        if ($this->getParentItem()) {
            return $this->getQty()*$this->getParentItem()->getQty();
        }
        return $this->getQty();
    }

    /**
     * Calculate item row total price
     *
     * @return Mage_Sales_Model_Quote_Item
     */
    public function calcRowTotal()
    {
        $qty        = $this->getTotalQty();
        // Round unit price before multiplying to prevent losing 1 cent on subtotal
        $total      = $this->getStore()->roundPrice($this->getCalculationPriceOriginal()) * $qty;
        $baseTotal  = $this->getBaseCalculationPriceOriginal() * $qty;

        $this->setRowTotal($this->getStore()->roundPrice($total));
        $this->setBaseRowTotal($this->getStore()->roundPrice($baseTotal));
        return $this;
    }

    /**
     * Get item price used for quote calculation process.
     * This method get custom price (if it is defined) or original product final price
     *
     * @return float
     */
    public function getCalculationPrice()
    {
        $price = $this->_getData('calculation_price');
        if (is_null($price)) {
            if ($this->hasCustomPrice()) {
                $price = $this->getCustomPrice();
            } else {
                $price = $this->getConvertedPrice();
            }
            $this->setData('calculation_price', $price);
        }
        return $price;
    }

    /**
     * Get item price used for quote calculation process.
     * This method get original custom price applied before tax calculation
     *
     * @return float
     */
    public function getCalculationPriceOriginal()
    {
        $price = $this->_getData('calculation_price');
        if (is_null($price)) {
            if ($this->hasOriginalCustomPrice()) {
                $price = $this->getOriginalCustomPrice();
            } else {
                $price = $this->getConvertedPrice();
            }
            $this->setData('calculation_price', $price);
        }
        return $price;
    }

    /**
     * Get calculation price used for quote calculation in base currency.
     *
     * @return float
     */
    public function getBaseCalculationPrice()
    {
        if (!$this->hasBaseCalculationPrice()) {
            if ($this->hasCustomPrice()) {
                $price = (float) $this->getCustomPrice();
                if ($price) {
                    $rate = $this->getStore()->convertPrice($price) / $price;
                    $price = $price / $rate;
                }
            } else {
                $price = $this->getPrice();
            }
            $this->setBaseCalculationPrice($price);
        }
        return $this->_getData('base_calculation_price');
    }

    /**
     * Get original calculation price used for quote calculation in base currency.
     *
     * @return float
     */
    public function getBaseCalculationPriceOriginal()
    {
        if (!$this->hasBaseCalculationPrice()) {
            if ($this->hasOriginalCustomPrice()) {
                $price = (float) $this->getOriginalCustomPrice();
                if ($price) {
                    $rate = $this->getStore()->convertPrice($price) / $price;
                    $price = $price / $rate;
                }
            } else {
                $price = $this->getPrice();
            }
            $this->setBaseCalculationPrice($price);
        }
        return $this->_getData('base_calculation_price');
    }

    /**
     * Get whether the item is nominal
     * TODO: fix for multishipping checkout
     *
     * @return bool
     */
    public function isNominal()
    {
        if (!$this->hasData('is_nominal')) {
            $this->setData('is_nominal', $this->getProduct() ? '1' == $this->getProduct()->getIsRecurring() : false);
        }
        return $this->_getData('is_nominal');
    }

    /**
     * Data getter for 'is_nominal'
     * Used for converting item to order item
     *
     * @return int
     */
    public function getIsNominal()
    {
        return (int)$this->isNominal();
    }

    /**
     * Get original price (retrieved from product) for item.
     * Original price value is in quote selected currency
     *
     * @return float
     */
    public function getOriginalPrice()
    {
        $price = $this->_getData('original_price');
        if (is_null($price)) {
            $price = $this->getStore()->convertPrice($this->getBaseOriginalPrice());
            $this->setData('original_price', $price);
        }
        return $price;
    }

    /**
     * Set original price to item (calculation price will be refreshed too)
     *
     * @param   float $price
     * @return  Mage_Sales_Model_Quote_Item_Abstract
     */
    public function setOriginalPrice($price)
    {
        return $this->setData('original_price', $price);
    }

    /**
     * Get Original item price (got from product) in base website currency
     *
     * @return float
     */
    public function getBaseOriginalPrice()
    {
        return $this->_getData('base_original_price');
    }

    /**
     * Specify custom item price (used in case whe we have apply not product price to item)
     *
     * @param   float $value
     * @return  Mage_Sales_Model_Quote_Item_Abstract
     */
    public function setCustomPrice($value)
    {
        $this->setCalculationPrice($value);
        $this->setBaseCalculationPrice(null);
        return $this->setData('custom_price', $value);
    }

    /**
     * Get item price. Item price currency is website base currency.
     *
     * @return decimal
     */
    public function getPrice()
    {
        return $this->_getData('price');
    }

    /**
     * Specify item price (base calculation price and converted price will be refreshed too)
     *
     * @param   float $value
     * @return  Mage_Sales_Model_Quote_Item_Abstract
     */
    public function setPrice($value)
    {
        $this->setBaseCalculationPrice(null);
        $this->setConvertedPrice(null);
        return $this->setData('price', $value);
    }

    /**
     * Get item price converted to quote currency
     * @return float
     */
    public function getConvertedPrice()
    {
        $price = $this->_getData('converted_price');
        if (is_null($price)) {
            $price = $this->getStore()->convertPrice($this->getPrice());
            $this->setData('converted_price', $price);
        }
        return $price;
    }

    /**
     * Set new value for converted price
     * @param float $value
     * @return Mage_Sales_Model_Quote_Item_Abstract
     */
    public function setConvertedPrice($value)
    {
        $this->setCalculationPrice(null);
        $this->setData('converted_price', $value);
        return $this;
    }

    /**
     * Clone quote item
     *
     * @return Mage_Sales_Model_Quote_Item
     */
    public function __clone()
    {
        $this->setId(null);
        $this->_parentItem  = null;
        $this->_children    = array();
        $this->_messages    = array();
        return $this;
    }

    /**
     * Checking if there children calculated or parent item
     * when we have parent quote item and its children
     *
     * @return bool
     */
    public function isChildrenCalculated()
    {
        if ($this->getParentItem()) {
            $calculate = $this->getParentItem()->getProduct()->getPriceType();
        } else {
            $calculate = $this->getProduct()->getPriceType();
        }

        if ((null !== $calculate) && (int)$calculate === Mage_Catalog_Model_Product_Type_Abstract::CALCULATE_CHILD) {
            return true;
        }
        return false;
    }

    /**
     * Checking can we ship product separatelly (each child separately)
     * or each parent product item can be shipped only like one item
     *
     * @return bool
     */
    public function isShipSeparately()
    {
        if ($this->getParentItem()) {
            $shipmentType = $this->getParentItem()->getProduct()->getShipmentType();
        } else {
            $shipmentType = $this->getProduct()->getShipmentType();
        }

        if ((null !== $shipmentType) &&
            (int)$shipmentType === Mage_Catalog_Model_Product_Type_Abstract::SHIPMENT_SEPARATELY) {
            return true;
        }
        return false;
    }
}
