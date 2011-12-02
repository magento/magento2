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
 * @package     Mage_ImportExport
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Import entity customer address
 *
 * @category    Mage
 * @package     Mage_ImportExport
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_ImportExport_Model_Import_Entity_Customer_Address extends Mage_ImportExport_Model_Import_Entity_Abstract
{
    /**
     * Prefix for source file column name, which displays that column contains address data.
     */
    const COL_NAME_PREFIX = '_address_';

    /**
     * Particular columns that contains of customer default addresses.
     */
    const COL_NAME_DEFAULT_BILLING  = '_address_default_billing_';
    const COL_NAME_DEFAULT_SHIPPING = '_address_default_shipping_';

    /**
     * Error codes.
     */
    const ERROR_INVALID_REGION = 'invalidRegion';

    /**
     * Customer address attributes parameters.
     *
     *  [attr_code_1] => array(
     *      'options' => array(),
     *      'type' => 'text', 'price', 'textarea', 'select', etc.
     *      'id' => ..
     *  ),
     *  ...
     *
     * @var array
     */
    protected $_attributes = array();

    /**
     * Countrys and its regions.
     *
     * array(
     *   [country_id_lowercased_1] => array(
     *     [region_code_lowercased_1]         => region_id_1,
     *     [region_default_name_lowercased_1] => region_id_1,
     *     ...,
     *     [region_code_lowercased_n]         => region_id_n,
     *     [region_default_name_lowercased_n] => region_id_n
     *   ),
     *   ...
     * )
     *
     * @var array
     */
    protected $_countryRegions = array();

    /**
     * Customer import entity.
     *
     * @var Mage_ImportExport_Model_Import_Entity_Customer
     */
    protected $_customer;

    /**
     * Default addresses column names to appropriate customer attribute code.
     *
     * @var array
     */
    protected static $_defaultAddressAttrMapping = array(
        self::COL_NAME_DEFAULT_BILLING  => 'default_billing',
        self::COL_NAME_DEFAULT_SHIPPING => 'default_shipping'
    );

    /**
     * Customer entity DB table name.
     *
     * @var string
     */
    protected $_entityTable;

    /**
     * Attributes with index (not label) value.
     *
     * @var array
     */
    protected $_indexValueAttributes = array('country_id');

    /**
     * Validation failure message template definitions
     *
     * @var array
     */
    protected $_messageTemplates = array(self::ERROR_INVALID_REGION => 'Region is invalid');

    /**
     * Column names that holds values with particular meaning.
     *
     * @var array
     */
    protected $_particularAttributes = array(self::COL_NAME_DEFAULT_BILLING, self::COL_NAME_DEFAULT_SHIPPING);

    /**
     * Region ID to region default name pairs.
     *
     * @var array
     */
    protected $_regions = array();

    /**
     * Constructor.
     *
     * @param Mage_ImportExport_Model_Import_Entity_Customer $customer
     * @return void
     */
    public function __construct(Mage_ImportExport_Model_Import_Entity_Customer $customer)
    {
        parent::__construct();

        $this->_initAttributes()->_initCountryRegions();

        $this->_entityTable = Mage::getModel('Mage_Customer_Model_Address')->getResource()->getEntityTable();
        $this->_customer    = $customer;

        foreach ($this->_messageTemplates as $errorCode => $message) {
            $this->_customer->addMessageTemplate($errorCode, $message);
        }
    }

    /**
     * Import data rows.
     *
     * @return boolean
     */
    protected function _importData()
    {
        /** @var $customer Mage_Customer_Model_Customer */
        $customer       = Mage::getModel('Mage_Customer_Model_Customer');
        /** @var $resource Mage_Customer_Model_Address */
        $resource       = Mage::getModel('Mage_Customer_Model_Address');
        $strftimeFormat = Varien_Date::convertZendToStrftime(Varien_Date::DATETIME_INTERNAL_FORMAT, true, true);
        $table = $resource->getResource()->getEntityTable();
        $nextEntityId   = Mage::getResourceHelper('Mage_ImportExport')->getNextAutoincrement($table);
        $customerId     = null;
        $regionColName  = self::getColNameForAttrCode('region');
        $countryColName = self::getColNameForAttrCode('country_id');
        $regionIdAttr   = Mage::getSingleton('Mage_Eav_Model_Config')->getAttribute($this->getEntityTypeCode(), 'region_id');
        $regionIdTable  = $regionIdAttr->getBackend()->getTable();
        $regionIdAttrId = $regionIdAttr->getId();

        while ($bunch = $this->_dataSourceModel->getNextBunch()) {
            $entityRows = array();
            $attributes = array();
            $defaults   = array(); // customer default addresses (billing/shipping) data

            foreach ($bunch as $rowNum => $rowData) {
                if (!empty($rowData[Mage_ImportExport_Model_Import_Entity_Customer::COL_EMAIL])
                        && !empty($rowData[Mage_ImportExport_Model_Import_Entity_Customer::COL_WEBSITE])
                ) {
                    $customerId = $this->_customer->getCustomerId(
                        $rowData[Mage_ImportExport_Model_Import_Entity_Customer::COL_EMAIL],
                        $rowData[Mage_ImportExport_Model_Import_Entity_Customer::COL_WEBSITE]
                    );
                }
                if (!$customerId || !$this->_isRowWithAddress($rowData) || !$this->validateRow($rowData, $rowNum)) {
                    continue;
                }
                $entityId = $nextEntityId++;

                // entity table data
                $entityRows[] = array(
                    'entity_id'      => $entityId,
                    'entity_type_id' => $this->_entityTypeId,
                    'parent_id'      => $customerId,
                    'created_at'     => now(),
                    'updated_at'     => now()
                );
                // attribute values
                foreach ($this->_attributes as $attrAlias => $attrParams) {
                    if (isset($rowData[$attrAlias]) && strlen($rowData[$attrAlias])) {
                        if ('select' == $attrParams['type']) {
                            $value = $attrParams['options'][strtolower($rowData[$attrAlias])];
                        } elseif ('datetime' == $attrParams['type']) {
                            $value = gmstrftime($strftimeFormat, strtotime($rowData[$attrAlias]));
                        } else {
                            $value = $rowData[$attrAlias];
                        }
                        $attributes[$attrParams['table']][$entityId][$attrParams['id']] = $value;
                    }
                }
                // customer default addresses
                foreach (self::getDefaultAddressAttrMapping() as $colName => $customerAttrCode) {
                    if (!empty($rowData[$colName])) {
                        $attribute = $customer->getAttribute($customerAttrCode);
                        $defaults[$attribute->getBackend()->getTable()][$customerId][$attribute->getId()] = $entityId;
                    }
                }
                // let's try to find region ID
                if (!empty($rowData[$regionColName])) {
                    $countryNormalized = strtolower($rowData[$countryColName]);
                    $regionNormalized  = strtolower($rowData[$regionColName]);

                    if (isset($this->_countryRegions[$countryNormalized][$regionNormalized])) {
                        $regionId = $this->_countryRegions[$countryNormalized][$regionNormalized];
                        $attributes[$regionIdTable][$entityId][$regionIdAttrId] = $regionId;
                        // set 'region' attribute value as default name
                        $tbl = $this->_attributes[$regionColName]['table'];
                        $regionColNameId = $this->_attributes[$regionColName]['id'];
                        $attributes[$tbl][$entityId][$regionColNameId] = $this->_regions[$regionId];
                    }
                }
            }
            $this->_saveAddressEntity($entityRows)
                    ->_saveAddressAttributes($attributes)
                    ->_saveCustomerDefaults($defaults);
        }
        return true;
    }

    /**
     * Initialize customer address attributes.
     *
     * @return Mage_ImportExport_Model_Import_Entity_Customer_Address
     */
    protected function _initAttributes()
    {
        $addrCollection = Mage::getResourceModel('Mage_Customer_Model_Resource_Address_Attribute_Collection')
                            ->addSystemHiddenFilter()
                            ->addExcludeHiddenFrontendFilter();

        foreach ($addrCollection as $attribute) {
            $this->_attributes[self::getColNameForAttrCode($attribute->getAttributeCode())] = array(
                'id'          => $attribute->getId(),
                'code'        => $attribute->getAttributeCode(),
                'table'       => $attribute->getBackend()->getTable(),
                'is_required' => $attribute->getIsRequired(),
                'rules'       => $attribute->getValidateRules() ? unserialize($attribute->getValidateRules()) : null,
                'type'        => Mage_ImportExport_Model_Import::getAttributeType($attribute),
                'options'     => $this->getAttributeOptions($attribute)
            );
        }
        return $this;
    }

    /**
     * Initialize country regions hash for clever recognition.
     *
     * @return Mage_ImportExport_Model_Import_Entity_Customer_Address
     */
    protected function _initCountryRegions()
    {
        foreach (Mage::getResourceModel('Mage_Directory_Model_Resource_Region_Collection') as $regionRow) {
            $countryNormalized = strtolower($regionRow['country_id']);
            $regionCode = strtolower($regionRow['code']);
            $regionName = strtolower($regionRow['default_name']);
            $this->_countryRegions[$countryNormalized][$regionCode] = $regionRow['region_id'];
            $this->_countryRegions[$countryNormalized][$regionName] = $regionRow['region_id'];
            $this->_regions[$regionRow['region_id']] = $regionRow['default_name'];
        }
        return $this;
    }

    /**
     * Check address data availability in row data.
     *
     * @param array $rowData
     * @return bool
     */
    protected function _isRowWithAddress(array $rowData)
    {
        foreach (array_keys($this->_attributes) as $colName) {
            if (isset($rowData[$colName]) && strlen($rowData[$colName])) {
                return true;
            }
        }
        return false;
    }

    /**
     * Save customer address attributes.
     *
     * @param array $attributesData
     * @return Mage_ImportExport_Model_Import_Entity_Customer_Address
     */
    protected function _saveAddressAttributes(array $attributesData)
    {
        foreach ($attributesData as $tableName => $data) {
            $tableData = array();

            foreach ($data as $addressId => $attrData) {
                foreach ($attrData as $attributeId => $value) {
                    $tableData[] = array(
                        'entity_id'      => $addressId,
                        'entity_type_id' => $this->_entityTypeId,
                        'attribute_id'   => $attributeId,
                        'value'          => $value
                    );
                }
            }
            $this->_connection->insertMultiple($tableName, $tableData);
        }
        return $this;
    }

    /**
     * Update and insert data in entity table.
     *
     * @param array $entityRows Rows for insert
     * @return Mage_ImportExport_Model_Import_Entity_Customer_Address
     */
    protected function _saveAddressEntity(array $entityRows)
    {
        if ($entityRows) {
            if (Mage_ImportExport_Model_Import::BEHAVIOR_APPEND != $this->_customer->getBehavior()) {
                $customersToClean = array();

                foreach ($entityRows as $entityData) {
                    $customersToClean[$entityData['parent_id']] = true;
                }
                $this->_connection->delete(
                    $this->_entityTable,
                    $this->_connection->quoteInto('`parent_id` IN (?)', array_keys($customersToClean))
                );
            }
            $this->_connection->insertMultiple($this->_entityTable, $entityRows);
        }
        return $this;
    }

    /**
     * Save customer default addresses.
     *
     * @param array $defaults
     * @return Mage_ImportExport_Model_Import_Entity_Customer_Address
     */
    protected function _saveCustomerDefaults(array $defaults)
    {
        foreach ($defaults as $tableName => $data) {
            $tableData = array();

            foreach ($data as $customerId => $attrData) {
                foreach ($attrData as $attributeId => $value) {
                    $tableData[] = array(
                        'entity_id'      => $customerId,
                        'entity_type_id' => $this->_customer->getEntityTypeId(),
                        'attribute_id'   => $attributeId,
                        'value'          => $value
                    );
                }
            }
            $this->_connection->insertOnDuplicate($tableName, $tableData, array('value'));
        }
        return $this;
    }

    /**
     * Get column name which holds value for attribute with specified code.
     *
     * @static
     * @param string $attrCode
     * @return string
     */
    public static function getColNameForAttrCode($attrCode)
    {
        return self::COL_NAME_PREFIX . $attrCode;
    }

    /**
     * Customer default addresses column name to customer attribute mapping array.
     *
     * @static
     * @return array
     */
    public static function getDefaultAddressAttrMapping()
    {
        return self::$_defaultAddressAttrMapping;
    }

    /**
     * EAV entity type code getter.
     *
     * @return string
     */
    public function getEntityTypeCode()
    {
        return 'customer_address';
    }

    /**
     * Is attribute contains particular data (not plain entity attribute).
     *
     * @param string $attrCode
     * @return bool
     */
    public function isAttributeParticular($attrCode)
    {
        return isset($this->_attributes[$attrCode]) || in_array($attrCode, $this->_particularAttributes);
    }

    /**
     * Validate data row.
     *
     * @param array $rowData
     * @param int $rowNum
     * @return boolean
     */
    public function validateRow(array $rowData, $rowNum)
    {
        $rowIsValid = true;

        if ($this->_isRowWithAddress($rowData)) {
            foreach ($this->_attributes as $colName => $attrParams) {
                if (isset($rowData[$colName]) && strlen($rowData[$colName])) {
                    $rowIsValid &= $this->_customer->isAttributeValid($colName, $attrParams, $rowData, $rowNum);
                } elseif ($attrParams['is_required']) {
                    $this->_customer->addRowError(
                        Mage_ImportExport_Model_Import_Entity_Customer::ERROR_VALUE_IS_REQUIRED, $rowNum, $colName
                    );
                    $rowIsValid = false;
                }
            }
            // validate region for countries with known region list
            if ($rowIsValid) {
                $regionColName  = self::getColNameForAttrCode('region');
                $countryColName = self::getColNameForAttrCode('country_id');
                $countryRegions = isset($this->_countryRegions[strtolower($rowData[$countryColName])])
                                ? $this->_countryRegions[strtolower($rowData[$countryColName])]
                                : array();

                if (!empty($rowData[$regionColName])
                    && !empty($countryRegions)
                    && !isset($countryRegions[strtolower($rowData[$regionColName])])
                ) {
                    $this->_customer->addRowError(self::ERROR_INVALID_REGION, $rowNum);

                    $rowIsValid = false;
                }
            }
        }
        return $rowIsValid;
    }
}
