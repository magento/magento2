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
 * @package     Mage_Catalog
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Catalog product option default type
 *
 * @category   Mage
 * @package    Mage_Catalog
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Catalog_Model_Product_Option_Type_Default extends Varien_Object
{
    /**
     * Option Instance
     *
     * @var Mage_Catalog_Model_Product_Option
     */
    protected $_option;

    /**
     * Product Instance
     *
     * @var Mage_Catalog_Model_Product
     */
    protected $_product;



    /**
     * description
     *
     * @var    mixed
     */
    protected $_productOptions = array();

    /**
     * Option Instance setter
     *
     * @param Mage_Catalog_Model_Product_Option $option
     * @return Mage_Catalog_Model_Product_Option_Type_Default
     */
    public function setOption($option)
    {
        $this->_option = $option;
        return $this;
    }

    /**
     * Option Instance getter
     *
     * @throws Mage_Core_Exception
     * @return Mage_Catalog_Model_Product_Option
     */
    public function getOption()
    {
        if ($this->_option instanceof Mage_Catalog_Model_Product_Option) {
            return $this->_option;
        }
        Mage::throwException(Mage::helper('Mage_Catalog_Helper_Data')->__('Wrong option instance type in options group.'));
    }

    /**
     * Product Instance setter
     *
     * @param Mage_Catalog_Model_Product $product
     * @return Mage_Catalog_Model_Product_Option_Type_Default
     */
    public function setProduct($product)
    {
        $this->_product = $product;
        return $this;
    }

    /**
     * Product Instance getter
     *
     * @throws Mage_Core_Exception
     * @return Mage_Catalog_Model_Product
     */
    public function getProduct()
    {
        if ($this->_product instanceof Mage_Catalog_Model_Product) {
            return $this->_product;
        }
        Mage::throwException(Mage::helper('Mage_Catalog_Helper_Data')->__('Wrong product instance type in options group.'));
    }

    /**
     * Getter for Configuration Item Option
     *
     * @return Mage_Catalog_Model_Product_Configuration_Item_Option_Interface
     */
    public function getConfigurationItemOption()
    {
        if ($this->_getData('configuration_item_option') instanceof Mage_Catalog_Model_Product_Configuration_Item_Option_Interface) {
            return $this->_getData('configuration_item_option');
        }

        // Back compatibility with quote specific keys to set configuration item options
        if ($this->_getData('quote_item_option') instanceof Mage_Sales_Model_Quote_Item_Option) {
            return $this->_getData('quote_item_option');
        }

        Mage::throwException(Mage::helper('Mage_Catalog_Helper_Data')->__('Wrong configuration item option instance in options group.'));
    }

    /**
     * Getter for Configuration Item
     *
     * @return Mage_Catalog_Model_Product_Configuration_Item_Interface
     */
    public function getConfigurationItem()
    {
        if ($this->_getData('configuration_item') instanceof Mage_Catalog_Model_Product_Configuration_Item_Interface) {
            return $this->_getData('configuration_item');
        }

        // Back compatibility with quote specific keys to set configuration item
        if ($this->_getData('quote_item') instanceof Mage_Sales_Model_Quote_Item) {
            return $this->_getData('quote_item');
        }

        Mage::throwException(Mage::helper('Mage_Catalog_Helper_Data')->__('Wrong configuration item instance in options group.'));
    }

    /**
     * Getter for Buy Request
     *
     * @return Varien_Object
     */
    public function getRequest()
    {
        if ($this->_getData('request') instanceof Varien_Object) {
            return $this->_getData('request');
        }
        Mage::throwException(Mage::helper('Mage_Catalog_Helper_Data')->__('Wrong BuyRequest instance in options group.'));
    }

    /**
     * Store Config value
     *
     * @param string $key Config value key
     * @return string
     */
    public function getConfigData($key)
    {
        return Mage::getStoreConfig('catalog/custom_options/' . $key);
    }

    /**
     * Validate user input for option
     *
     * @throws Mage_Core_Exception
     * @param array $values All product option values, i.e. array (option_id => mixed, option_id => mixed...)
     * @return Mage_Catalog_Model_Product_Option_Type_Default
     */
    public function validateUserValue($values)
    {
        Mage::getSingleton('Mage_Checkout_Model_Session')->setUseNotice(false);

        $this->setIsValid(false);

        $option = $this->getOption();
        if (!isset($values[$option->getId()]) && $option->getIsRequire() && !$this->getSkipCheckRequiredOption()) {
            Mage::throwException(Mage::helper('Mage_Catalog_Helper_Data')->__('Please specify the product required option(s).'));
        } elseif (isset($values[$option->getId()])) {
            $this->setUserValue($values[$option->getId()]);
            $this->setIsValid(true);
        }
        return $this;
    }

    /**
     * Check skip required option validation
     *
     * @return bool
     */
    public function getSkipCheckRequiredOption()
    {
        return $this->getProduct()->getSkipCheckRequiredOption() ||
            $this->getProcessMode() == Mage_Catalog_Model_Product_Type_Abstract::PROCESS_MODE_LITE;
    }

    /**
     * Prepare option value for cart
     *
     * @throws Mage_Core_Exception
     * @return mixed Prepared option value
     */
    public function prepareForCart()
    {
        if ($this->getIsValid()) {
            return $this->getUserValue();
        }
        Mage::throwException(Mage::helper('Mage_Catalog_Helper_Data')->__('Option validation failed to add product to cart.'));
    }

    /**
     * Flag to indicate that custom option has own customized output (blocks, native html etc.)
     *
     * @return boolean
     */
    public function isCustomizedView()
    {
        return false;
    }

    /**
     * Return formatted option value for quote option
     *
     * @param string $optionValue Prepared for cart option value
     * @return string
     */
    public function getFormattedOptionValue($optionValue)
    {
        return $optionValue;
    }

    /**
     * Return option html
     *
     * @param array $optionInfo
     * @return string
     */
    public function getCustomizedView($optionInfo)
    {
        return isset($optionInfo['value']) ? $optionInfo['value'] : $optionInfo;
    }

    /**
     * Return printable option value
     *
     * @param string $optionValue Prepared for cart option value
     * @return string
     */
    public function getPrintableOptionValue($optionValue)
    {
        return $optionValue;
    }

    /**
     * Return formatted option value ready to edit, ready to parse
     * (ex: Admin re-order, see Mage_Adminhtml_Model_Sales_Order_Create)
     *
     * @param string $optionValue Prepared for cart option value
     * @return string
     */
    public function getEditableOptionValue($optionValue)
    {
        return $optionValue;
    }

    /**
     * Parse user input value and return cart prepared value, i.e. "one, two" => "1,2"
     *
     * @param string $optionValue
     * @param array $productOptionValues Values for product option
     * @return string|null
     */
    public function parseOptionValue($optionValue, $productOptionValues)
    {
        return $optionValue;
    }

    /**
     * Prepare option value for info buy request
     *
     * @param string $optionValue
     * @return mixed
     */
    public function prepareOptionValueForRequest($optionValue)
    {
        return $optionValue;
    }

    /**
     * Return Price for selected option
     *
     * @param string $optionValue Prepared for cart option value
     * @param float $basePrice For percent price type
     * @return float
     */
    public function getOptionPrice($optionValue, $basePrice)
    {
        $option = $this->getOption();

        return $this->_getChargableOptionPrice(
            $option->getPrice(),
            $option->getPriceType() == 'percent',
            $basePrice
        );
    }

    /**
     * Return SKU for selected option
     *
     * @param string $optionValue Prepared for cart option value
     * @param string $skuDelimiter Delimiter for Sku parts
     * @return string
     */
    public function getOptionSku($optionValue, $skuDelimiter)
    {
        return $this->getOption()->getSku();
    }

    /**
     * Return value => key all product options (using for parsing)
     *
     * @return array Array of Product custom options, reversing option values and option ids
     */
    public function getProductOptions()
    {
        if (!isset($this->_productOptions[$this->getProduct()->getId()])) {
            foreach ($this->getProduct()->getOptions() as $_option) {
                /* @var $option Mage_Catalog_Model_Product_Option */
                $this->_productOptions[$this->getProduct()->getId()][$_option->getTitle()] = array('option_id' => $_option->getId());
                if ($_option->getGroupByType() == Mage_Catalog_Model_Product_Option::OPTION_GROUP_SELECT) {
                    $optionValues = array();
                    foreach ($_option->getValues() as $_value) {
                        /* @var $value Mage_Catalog_Model_Product_Option_Value */
                        $optionValues[$_value->getTitle()] = $_value->getId();
                    }
                    $this->_productOptions[$this->getProduct()->getId()][$_option->getTitle()]['values'] = $optionValues;
                } else {
                    $this->_productOptions[$this->getProduct()->getId()][$_option->getTitle()]['values'] = array();
                }
            }
        }
        if (isset($this->_productOptions[$this->getProduct()->getId()])) {
            return $this->_productOptions[$this->getProduct()->getId()];
        }
        return array();
    }

    /**
     * Return final chargable price for option
     *
     * @param float $price Price of option
     * @param boolean $isPercent Price type - percent or fixed
     * @param float $basePrice For percent price type
     * @return float
     */
    protected function _getChargableOptionPrice($price, $isPercent, $basePrice)
    {
        if($isPercent) {
            return ($basePrice * $price / 100);
        } else {
            return $price;
        }
    }

}
