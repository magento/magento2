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
 * @copyright  Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * API2 catalog_product Validator
 *
 * @category   Mage
 * @package    Mage_Catalog
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Catalog_Model_Api2_Product_Validator_Product extends Mage_Api2_Model_Resource_Validator
{
    /**
     * The greatest decimal value which could be stored. Corresponds to DECIMAL (12,4) SQL type
     */
    const MAX_DECIMAL_VALUE = 99999999.9999;

    /**
     * Validator product
     *
     * @var Mage_Catalog_Model_Product
     */
    protected $_product = null;

    /**
     * Validation operation
     *
     * @var string
     */
    protected $_operation = null;

    public function __construct($options)
    {
        if (isset($options['product'])) {
            if ($options['product'] instanceof Mage_Catalog_Model_Product) {
                $this->_product = $options['product'];
            } else {
                throw new Exception("Passed parameter 'product' is wrong.");
            }
        }

        if (!isset($options['operation']) || empty($options['operation'])) {
            throw new Exception("Passed parameter 'operation' is empty.");
        }
        $this->_operation = $options['operation'];
    }

    /**
     * Get validator product
     *
     * @return Mage_Catalog_Model_Product|null
     */
    protected function _getProduct()
    {
        return $this->_product;
    }

    /**
     * Is update mode
     *
     * @return bool
     */
    protected function _isUpdate()
    {
        return $this->_operation == Mage_Api2_Model_Resource::OPERATION_UPDATE;
    }

    /**
     * Validate product data
     *
     * @param array $data
     * @return bool
     */
    public function isValidData(array $data)
    {
        if ($this->_isUpdate()) {
            $product = $this->_getProduct();
            if (!is_null($product) && $product->getId()) {
                $data['attribute_set_id'] = $product->getAttributeSetId();
                $data['type_id'] = $product->getTypeId();
            }
        }

        try {
            $this->_validateProductType($data);
            /** @var $productEntity Mage_Eav_Model_Entity_Type */
            $productEntity = Mage::getModel('Mage_Eav_Model_Entity_Type')->loadByCode(Mage_Catalog_Model_Product::ENTITY);
            $this->_validateAttributeSet($data, $productEntity);
            $this->_validateSku($data);
            $this->_validateGiftOptions($data);
            $this->_validateGroupPrice($data);
            $this->_validateTierPrice($data);
            $this->_validateStockData($data);
            $this->_validateAttributes($data, $productEntity);
            $isSatisfied = count($this->getErrors()) == 0;
        } catch (Mage_Api2_Exception $e) {
            $this->_addError($e->getMessage());
            $isSatisfied = false;
        }


        return $isSatisfied;
    }

    /**
     * Collect required EAV attributes, validate applicable attributes and validate source attributes values
     *
     * @param array $data
     * @param Mage_Eav_Model_Entity_Type $productEntity
     * @return array
     */
    protected function _validateAttributes($data, $productEntity)
    {
        if (!isset($data['attribute_set_id']) || empty($data['attribute_set_id'])) {
            $this->_critical('Missing "attribute_set_id" in request.', Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
        }
        if (!isset($data['type_id']) || empty($data['type_id'])) {
            $this->_critical('Missing "type_id" in request.', Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
        }
        // Validate weight
        if (isset($data['weight']) && !empty($data['weight']) && $data['weight'] > 0
            && !Zend_Validate::is($data['weight'], 'Between', array(0, self::MAX_DECIMAL_VALUE))) {
            $this->_addError('The "weight" value is not within the specified range.');
        }
        // msrp_display_actual_price_type attribute values needs to be a string to pass validation
        // see Mage_Catalog_Model_Product_Attribute_Source_Msrp_Type_Price::getAllOptions()
        if (isset($data['msrp_display_actual_price_type'])) {
            $data['msrp_display_actual_price_type'] = (string) $data['msrp_display_actual_price_type'];
        }
        $requiredAttributes = array('attribute_set_id');
        $positiveNumberAttributes = array('weight', 'price', 'special_price', 'msrp');
        /** @var $attribute Mage_Catalog_Model_Resource_Eav_Attribute */
        foreach ($productEntity->getAttributeCollection($data['attribute_set_id']) as $attribute) {
            $attributeCode = $attribute->getAttributeCode();
            $value = false;
            $isSet = false;
            if (isset($data[$attribute->getAttributeCode()])) {
                $value = $data[$attribute->getAttributeCode()];
                $isSet = true;
            }
            $applicable = false;
            if (!$attribute->getApplyTo() || in_array($data['type_id'], $attribute->getApplyTo())) {
                $applicable = true;
            }

            if (!$applicable && !$attribute->isStatic() && $isSet) {
                $productTypes = Mage_Catalog_Model_Product_Type::getTypes();
                $this->_addError(sprintf('Attribute "%s" is not applicable for product type "%s"', $attributeCode,
                    $productTypes[$data['type_id']]['label']));
            }

            if ($applicable && $isSet) {
                // Validate dropdown attributes
                if ($attribute->usesSource()
                    // skip check when field will be validated later as a required one
                    && !(empty($value) && $attribute->getIsRequired())) {
                    $allowedValues = $this->_getAttributeAllowedValues($attribute->getSource()->getAllOptions());
                    if (!is_array($value)) {
                        // make validation of select and multiselect identical
                        $value = array($value);
                    }
                    foreach ($value as $selectValue) {
                        $useStrictMode = !is_numeric($selectValue);
                        if (!in_array($selectValue, $allowedValues, $useStrictMode)
                            && !$this->_isConfigValueUsed($data, $attributeCode)) {
                            $this->_addError(sprintf('Invalid value "%s" for attribute "%s".',
                                $selectValue, $attributeCode));
                        }
                    }
                }
                // Validate datetime attributes
                if ($attribute->getBackendType() == 'datetime') {
                    try {
                        $attribute->getBackend()->formatDate($value);
                    } catch (Zend_Date_Exception $e) {
                        $this->_addError(sprintf('Invalid date in the "%s" field.', $attributeCode));
                    }
                }
                // Validate positive number required attributes
                if (in_array($attributeCode, $positiveNumberAttributes) && (!empty($value) && $value !== 0)
                    && (!is_numeric($value) || $value < 0)
                ) {
                    $this->_addError(sprintf('Please enter a number 0 or greater in the "%s" field.', $attributeCode));
                }
            }

            if ($applicable && $attribute->getIsRequired() && $attribute->getIsVisible()) {
                if (!in_array($attributeCode, $positiveNumberAttributes) || $value !== 0) {
                    $requiredAttributes[] = $attribute->getAttributeCode();
                }
            }
        }

        foreach ($requiredAttributes as $key) {
            if (!array_key_exists($key, $data)) {
                if (!$this->_isUpdate()) {
                    $this->_addError(sprintf('Missing "%s" in request.', $key));
                    continue;
                }
            } else if (!is_numeric($data[$key]) && empty($data[$key])) {
                $this->_addError(sprintf('Empty value for "%s" in request.', $key));
            }
        }
    }

    /**
     * Validate product type
     *
     * @param array $data
     * @return bool
     */
    protected function _validateProductType($data)
    {
        if ($this->_isUpdate()) {
            return true;
        }
        if (!isset($data['type_id']) || empty($data['type_id'])) {
            $this->_critical('Missing "type_id" in request.', Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
        }
        if (!array_key_exists($data['type_id'], Mage_Catalog_Model_Product_Type::getTypes())) {
            $this->_critical('Invalid product type.', Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Validate attribute set
     *
     * @param array $data
     * @param Mage_Eav_Model_Entity_Type $productEntity
     * @return bool
     */
    protected function _validateAttributeSet($data, $productEntity)
    {
        if ($this->_isUpdate()) {
            return true;
        }
        if (!isset($data['attribute_set_id']) || empty($data['attribute_set_id'])) {
            $this->_critical('Missing "attribute_set_id" in request.', Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
        }
        /** @var $attributeSet Mage_Eav_Model_Entity_Attribute_Set */
        $attributeSet = Mage::getModel('Mage_Eav_Model_Entity_Attribute_Set')->load($data['attribute_set_id']);
        if (!$attributeSet->getId() || $productEntity->getEntityTypeId() != $attributeSet->getEntityTypeId()) {
            $this->_critical('Invalid attribute set.', Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Validate SKU
     *
     * @param array $data
     * @return bool
     */
    protected function _validateSku($data)
    {
        if ($this->_isUpdate() && !isset($data['sku'])) {
            return true;
        }
        if (!Zend_Validate::is((string)$data['sku'], 'StringLength', array('min' => 0, 'max' => 64))) {
            $this->_addError('SKU length should be 64 characters maximum.');
        }
    }

    /**
     * Validate product gift options data
     *
     * @param array $data
     */
    protected function _validateGiftOptions($data)
    {
        if (isset($data['gift_wrapping_price'])) {
            if (!(is_numeric($data['gift_wrapping_price']) && $data['gift_wrapping_price'] >= 0)) {
                $this->_addError('Please enter a number 0 or greater in the "gift_wrapping_price" field.');
            }
        }
    }

    /**
     * Validate Group Price complex attribute
     *
     * @param array $data
     */
    protected function _validateGroupPrice($data)
    {
        if (isset($data['group_price']) && is_array($data['group_price'])) {
            $groupPrices = $data['group_price'];
            foreach ($groupPrices as $index => $groupPrice) {
                $fieldSet = 'group_price:' . $index;
                $this->_validateWebsiteIdForGroupPrice($groupPrice, $fieldSet);
                $this->_validateCustomerGroup($groupPrice, $fieldSet);
                $this->_validatePositiveNumber($groupPrice, $fieldSet, 'price', true, true);
            }
        }
    }

    /**
     * Validate Tier Price complex attribute
     *
     * @param array $data
     */
    protected function _validateTierPrice($data)
    {
        if (isset($data['tier_price']) && is_array($data['tier_price'])) {
            $tierPrices = $data['tier_price'];
            foreach ($tierPrices as $index => $tierPrice) {
                $fieldSet = 'tier_price:' . $index;
                $this->_validateWebsiteIdForGroupPrice($tierPrice, $fieldSet);
                $this->_validateCustomerGroup($tierPrice, $fieldSet);
                $this->_validatePositiveNumber($tierPrice, $fieldSet, 'price_qty');
                $this->_validatePositiveNumber($tierPrice, $fieldSet, 'price');
            }
        }
    }

    /**
     * Check if website id is appropriate according to price scope settings
     *
     * @param array $data
     * @param string $fieldSet
     */
    protected function _validateWebsiteIdForGroupPrice($data, $fieldSet)
    {
        if (!isset($data['website_id'])) {
            $this->_addError(sprintf('The "website_id" value in the "%s" set is a required field.', $fieldSet));
        } else {
            /** @var $catalogHelper Mage_Catalog_Helper_Data */
            $catalogHelper = Mage::helper('Mage_Catalog_Helper_Data');
            $website = Mage::getModel('Mage_Core_Model_Website')->load($data['website_id']);
            $isAllWebsitesValue = is_numeric($data['website_id']) && ($data['website_id'] == 0);
            $isGlobalPriceScope = (int)$catalogHelper->getPriceScope() == Mage_Catalog_Helper_Data::PRICE_SCOPE_GLOBAL;
            if (is_null($website->getId()) || ($isGlobalPriceScope && !$isAllWebsitesValue)) {
                $this->_addError(sprintf('Invalid "website_id" value in the "%s" set.', $fieldSet));
            }
        }
    }

    /**
     * Validate product inventory data
     *
     * @param array $data
     */
    protected function _validateStockData($data)
    {
        if (isset($data['stock_data']) && is_array($data['stock_data'])) {
            $stockData = $data['stock_data'];
            $fieldSet = 'stock_data';
            if (!(isset($stockData['use_config_manage_stock']) && $stockData['use_config_manage_stock'])) {
                $this->_validateBoolean($stockData, $fieldSet, 'manage_stock');
            }
            if ($this->_isManageStockEnabled($stockData)) {
                $this->_validateNumeric($stockData, $fieldSet, 'qty');
                $this->_validatePositiveNumber($stockData, $fieldSet, 'min_qty', false, true, true);
                $this->_validateNumeric($stockData, $fieldSet, 'notify_stock_qty', false, true);
                $this->_validateBoolean($stockData, $fieldSet, 'is_qty_decimal');
                if (isset($stockData['is_qty_decimal']) && (bool) $stockData['is_qty_decimal'] == true) {
                    $this->_validateBoolean($stockData, $fieldSet, 'is_decimal_divided');
                }
                $this->_validateBoolean($stockData, $fieldSet, 'enable_qty_increments', true);
                if (isset($stockData['enable_qty_increments']) && (bool) $stockData['enable_qty_increments'] == true) {
                    $this->_validatePositiveNumeric($stockData, $fieldSet, 'qty_increments', false, true);
                }
                if (Mage::helper('Mage_Catalog_Helper_Data')->isModuleEnabled('Mage_CatalogInventory')) {
                    $this->_validateSource($stockData, $fieldSet, 'backorders',
                        'cataloginventory/source_backorders', true);
                    $this->_validateSource($stockData, $fieldSet, 'is_in_stock', 'cataloginventory/source_stock');
                }
            }

            $this->_validatePositiveNumeric($stockData, $fieldSet, 'min_sale_qty', false, true);
            $this->_validatePositiveNumeric($stockData, $fieldSet, 'max_sale_qty', false, true);
        }
    }

    /**
     * Determine if stock management is enabled
     *
     * @param array $stockData
     * @return bool
     */
    protected function _isManageStockEnabled($stockData)
    {
        if (!(isset($stockData['use_config_manage_stock']) && $stockData['use_config_manage_stock'])) {
            $manageStock = isset($stockData['manage_stock']) && $stockData['manage_stock'];
        } else {
            $manageStock = Mage::getStoreConfig(
                Mage_CatalogInventory_Model_Stock_Item::XML_PATH_ITEM . 'manage_stock');
        }
        return (bool) $manageStock;
    }

    /**
     * Validate Customer Group field
     *
     * @param string $fieldSet
     * @param array $data
     */
    protected function _validateCustomerGroup($data, $fieldSet)
    {
        if (!isset($data['cust_group'])) {
            $this->_addError(sprintf('The "cust_group" value in the "%s" set is a required field.', $fieldSet));
        } else {
            if (!is_numeric($data['cust_group'])) {
                $this->_addError(sprintf('Invalid "cust_group" value in the "%s" set', $fieldSet));
            } else {
                $customerGroup = Mage::getModel('Mage_Customer_Model_Group')->load($data['cust_group']);
                if (is_null($customerGroup->getId())) {
                    $this->_addError(sprintf('Invalid "cust_group" value in the "%s" set', $fieldSet));
                }
            }
        }
    }

    /**
     * Validate field to be positive number
     *
     * @param array $data
     * @param string $fieldSet
     * @param string $field
     * @param bool $required
     * @param bool $equalsZero
     * @param bool $skipIfConfigValueUsed
     */
    protected function _validatePositiveNumber($data, $fieldSet, $field, $required = true, $equalsZero = false,
        $skipIfConfigValueUsed = false)
    {
        // in case when 'Use Config Settings' is selected no validation needed
        if (!($skipIfConfigValueUsed && $this->_isConfigValueUsed($data, $field))) {
            if (!isset($data[$field]) && $required) {
                $this->_addError(sprintf('The "%s" value in the "%s" set is a required field.', $field, $fieldSet));
            }

            if (isset($data[$field])) {
                $isValid = $equalsZero ? $data[$field] >= 0 : $data[$field] > 0;
                if (!(is_numeric($data[$field]) && $isValid)) {
                    $message = $equalsZero
                        ? 'Please enter a number 0 or greater in the "%s" field in the "%s" set.'
                        : 'Please enter a number greater than 0 in the "%s" field in the "%s" set.';
                    $this->_addError(sprintf($message, $field, $fieldSet));
                }
            }
        }
    }

    /**
     * Validate field to be a positive number
     *
     * @param array $data
     * @param string $fieldSet
     * @param string $field
     * @param bool $required
     * @param bool $skipIfConfigValueUsed
     */
    protected function _validatePositiveNumeric($data, $fieldSet, $field, $required = false,
        $skipIfConfigValueUsed = false)
    {
        // in case when 'Use Config Settings' is selected no validation needed
        if (!($skipIfConfigValueUsed && $this->_isConfigValueUsed($data, $field))) {
            if (!isset($data[$field]) && $required) {
                $this->_addError(sprintf('The "%s" value in the "%s" set is a required field.',$field, $fieldSet));
            }

            if (isset($data[$field]) && (!is_numeric($data[$field]) || $data[$field] < 0)) {
                $this->_addError(sprintf('Please use numbers only in the "%s" field in the "%s" set. ' .
                    'Please avoid spaces or other non numeric characters.', $field, $fieldSet));
            }
        }
    }

    /**
     * Validate field to be a number
     *
     * @param array $data
     * @param string $fieldSet
     * @param string $field
     * @param bool $required
     * @param bool $skipIfConfigValueUsed
     */
    protected function _validateNumeric($data, $fieldSet, $field, $required = false, $skipIfConfigValueUsed = false)
    {
        // in case when 'Use Config Settings' is selected no validation needed
        if (!($skipIfConfigValueUsed && $this->_isConfigValueUsed($data, $field))) {
            if (!isset($data[$field]) && $required) {
                $this->_addError(sprintf('The "%s" value in the "%s" set is a required field.',$field, $fieldSet));
            }

            if (isset($data[$field]) && !is_numeric($data[$field])) {
                $this->_addError(sprintf('Please enter a valid number in the "%s" field in the "%s" set.',
                    $field, $fieldSet));
            }
        }
    }

    /**
     * Validate dropdown fields value
     *
     * @param array $data
     * @param string $fieldSet
     * @param string $field
     * @param string $sourceModelName
     * @param bool $skipIfConfigValueUsed
     */
    protected function _validateSource($data, $fieldSet, $field, $sourceModelName, $skipIfConfigValueUsed = false)
    {
        // in case when 'Use Config Settings' is selected no validation needed
        if (!($skipIfConfigValueUsed && $this->_isConfigValueUsed($data, $field))) {
            if (isset($data[$field])) {
                $sourceModel = Mage::getSingleton($sourceModelName);
                if ($sourceModel) {
                    $allowedValues = $this->_getAttributeAllowedValues($sourceModel->toOptionArray());
                    $useStrictMode = !is_numeric($data[$field]);
                    if (!in_array($data[$field], $allowedValues, $useStrictMode)) {
                        $this->_addError(sprintf('Invalid "%s" value in the "%s" set.', $field, $fieldSet));
                    }
                }
            }
        }
    }

    /**
     * Validate bolean fields value
     *
     * @param array $data
     * @param string $fieldSet
     * @param string $field
     * @param bool $skipIfConfigValueUsed
     */
    protected function _validateBoolean($data, $fieldSet, $field, $skipIfConfigValueUsed = false)
    {
        // in case when 'Use Config Settings' is selected no validation needed
        if (!($skipIfConfigValueUsed && $this->_isConfigValueUsed($data, $field))) {
            if (isset($data[$field])) {
                $allowedValues = $this->_getAttributeAllowedValues(
                    Mage::getSingleton('Mage_Eav_Model_Entity_Attribute_Source_Boolean')->getAllOptions());
                $useStrictMode = !is_numeric($data[$field]);
                if (!in_array($data[$field], $allowedValues, $useStrictMode)) {
                    $this->_addError(sprintf('Invalid "%s" value in the "%s" set.', $field, $fieldSet));
                }
            }
        }
    }

    /**
     * Retrieve all attribute allowed values from source model in plain array format
     *
     * @param array $options
     * @return array
     */
    protected function _getAttributeAllowedValues(array $options)
    {
        $values = array();
        foreach ($options as $option) {
            if (isset($option['value'])) {
                $value = $option['value'];
                if (is_array($value)) {
                    $values = array_merge($values, $this->_getAttributeAllowedValues($value));
                } else {
                    $values[] = $value;
                }
            }
        }

        return $values;
    }

    /**
     * Check if value from config is used
     *
     * @param array $data
     * @param string $field
     * @return bool
     */
    protected function _isConfigValueUsed($data, $field)
    {
        return isset($data["use_config_$field"]) && $data["use_config_$field"];
    }

    /**
     * Throw API2 exception
     *
     * @param string $message
     * @param int $code
     * @throws Mage_Api2_Exception
     */
    protected function _critical($message, $code)
    {
        throw new Mage_Api2_Exception($message, $code);
    }
}
