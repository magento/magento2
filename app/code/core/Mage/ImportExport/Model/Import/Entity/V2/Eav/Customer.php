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
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Import entity customer model
 *
 * @category    Mage
 * @package     Mage_ImportExport
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_ImportExport_Model_Import_Entity_V2_Eav_Customer
    extends Mage_ImportExport_Model_Import_Entity_V2_Eav_Customer_Abstract
{
    /**#@+
     * Permanent column names
     *
     * Names that begins with underscore is not an attribute. This name convention is for
     * to avoid interference with same attribute name.
     */
    const COLUMN_EMAIL   = 'email';
    const COLUMN_STORE   = '_store';
    /**#@-*/

    /**#@+
     * Error codes
     */
    const ERROR_DUPLICATE_EMAIL_SITE = 'duplicateEmailSite';
    const ERROR_ROW_IS_ORPHAN        = 'rowIsOrphan';
    const ERROR_INVALID_STORE        = 'invalidStore';
    const ERROR_EMAIL_SITE_NOT_FOUND = 'emailSiteNotFound';
    const ERROR_PASSWORD_LENGTH      = 'passwordLength';
    /**#@-*/

    /**
     * Minimum password length
     */
    const MIN_PASSWORD_LENGTH = 6;

    /**
     * Default customer group
     */
    const DEFAULT_GROUP_ID = 1;

    /**
     * Customers information from import file
     *
     * @var array
     */
    protected $_newCustomers = array();

    /**
     * Array of attribute codes which will be ignored in validation and import procedures.
     * For example, when entity attribute has own validation and import procedures
     * or just to deny this attribute processing.
     *
     * @var array
     */
    protected $_ignoredAttributes = array('website_id', 'store_id');

    /**
     * Customer entity DB table name.
     *
     * @var string
     */
    protected $_entityTable;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->_particularAttributes[] = self::COLUMN_WEBSITE;
        $this->_particularAttributes[] = self::COLUMN_STORE;
        $this->_permanentAttributes[]  = self::COLUMN_EMAIL;
        $this->_permanentAttributes[]  = self::COLUMN_WEBSITE;
        $this->_indexValueAttributes[] = 'group_id';

        /** @var $helper Mage_ImportExport_Helper_Data */
        $helper = Mage::helper('Mage_ImportExport_Helper_Data');

        $this->addMessageTemplate(self::ERROR_DUPLICATE_EMAIL_SITE, $helper->__('E-mail is duplicated in import file'));
        $this->addMessageTemplate(self::ERROR_ROW_IS_ORPHAN,
            $helper->__('Orphan rows that will be skipped due default row errors')
        );
        $this->addMessageTemplate(self::ERROR_INVALID_STORE,
            $helper->__('Invalid value in Store column (store does not exists?)')
        );
        $this->addMessageTemplate(self::ERROR_EMAIL_SITE_NOT_FOUND,
            $helper->__('E-mail and website combination is not found')
        );
        $this->addMessageTemplate(self::ERROR_PASSWORD_LENGTH, $helper->__('Invalid password length'));

        $this->_initStores(true)
            ->_initAttributes();

        /** @var $customerResource Mage_Customer_Model_Resource_Customer */
        $customerResource = Mage::getModel('Mage_Customer_Model_Customer')->getResource();
        $this->_entityTable = $customerResource->getEntityTable();
    }

    /**
     * Gather and save information about customer entities.
     *
     * @return Mage_ImportExport_Model_Import_Entity_Customer
     */
    protected function _saveCustomers()
    {
        /** @var $resource Mage_Customer_Model_Customer */
        $resource = Mage::getModel('Mage_Customer_Model_Customer');

        $passwordAttribute = $resource->getAttribute('password_hash');
        $passwordAttributeId = $passwordAttribute->getId();
        $passwordStorageTable = $passwordAttribute->getBackend()->getTable();

        $dateTimeFormat = Varien_Date::convertZendToStrftime(Varien_Date::DATETIME_INTERNAL_FORMAT, true, true);

        $nextEntityId = Mage::getResourceHelper('Mage_ImportExport')->getNextAutoincrement($this->_entityTable);

        while ($bunch = $this->_dataSourceModel->getNextBunch()) {
            $entitiesToCreate = array();
            $entitiesToUpdate = array();
            $attributes   = array();

            foreach ($bunch as $rowNumber => $rowData) {
                if (!$this->validateRow($rowData, $rowNumber)) {
                    continue;
                }

                // entity table data
                $entityRow = array(
                    'group_id'   => empty($rowData['group_id'])
                        ? self::DEFAULT_GROUP_ID : $rowData['group_id'],

                    'store_id'   => empty($rowData[self::COLUMN_STORE])
                        ? 0 : $this->_storeCodeToId[$rowData[self::COLUMN_STORE]],

                    'created_at' => empty($rowData['created_at'])
                        ? now() : gmstrftime($dateTimeFormat, strtotime($rowData['created_at'])),

                    'updated_at' => now()
                );

                $emailInLowercase = strtolower($rowData[self::COLUMN_EMAIL]);
                if ($entityId = $this->_getCustomerId($emailInLowercase, $rowData[self::COLUMN_WEBSITE])) { // edit
                    $entityRow['entity_id'] = $entityId;
                    $entitiesToUpdate[] = $entityRow;
                } else { // create
                    $entityId = $nextEntityId++;
                    $entityRow['entity_id'] = $entityId;
                    $entityRow['entity_type_id'] = $this->getEntityTypeId();
                    $entityRow['attribute_set_id'] = 0;
                    $entityRow['website_id'] = $this->_websiteCodeToId[$rowData[self::COLUMN_WEBSITE]];
                    $entityRow['email'] = $emailInLowercase;
                    $entityRow['is_active'] = 1;
                    $entitiesToCreate[] = $entityRow;

                    $this->_newCustomers[$emailInLowercase][$rowData[self::COLUMN_WEBSITE]] = $entityId;
                }

                // attribute values
                foreach (array_intersect_key($rowData, $this->_attributes) as $attributeCode => $value) {
                    if (!$this->_attributes[$attributeCode]['is_static'] && strlen($value)) {
                        /** @var $attribute Mage_Customer_Model_Attribute */
                        $attribute = $resource->getAttribute($attributeCode);
                        $backendModel = $attribute->getBackendModel();
                        $attributeParameters = $this->_attributes[$attributeCode];

                        if ('select' == $attributeParameters['type']) {
                            $value = $attributeParameters['options'][strtolower($value)];
                        } elseif ('datetime' == $attributeParameters['type']) {
                            $value = gmstrftime($dateTimeFormat, strtotime($value));
                        } elseif ($backendModel) {
                            $attribute->getBackend()->beforeSave($resource->setData($attributeCode, $value));
                            $value = $resource->getData($attributeCode);
                        }
                        $attributes[$attribute->getBackend()->getTable()][$entityId][$attributeParameters['id']]
                            = $value;

                        // restore 'backend_model' to avoid default setting
                        $attribute->setBackendModel($backendModel);
                    }
                }

                // password change/set
                if (isset($rowData['password']) && strlen($rowData['password'])) {
                    $attributes[$passwordStorageTable][$entityId][$passwordAttributeId]
                        = $resource->hashPassword($rowData['password']);
                }
            }

            $this->_saveCustomerEntity($entitiesToCreate, $entitiesToUpdate)
                ->_saveCustomerAttributes($attributes);
        }
        return $this;
    }

    /**
     * Update and insert data in entity table.
     *
     * @param array $entitiesToCreate Rows for insert
     * @param array $entitiesToUpdate Rows for update
     * @return Mage_ImportExport_Model_Import_Entity_V2_Eav_Customer
     */
    protected function _saveCustomerEntity(array $entitiesToCreate, array $entitiesToUpdate)
    {
        if ($entitiesToCreate) {
            $this->_connection->insertMultiple($this->_entityTable, $entitiesToCreate);
        }

        if ($entitiesToUpdate) {
            $this->_connection->insertOnDuplicate(
                $this->_entityTable,
                $entitiesToUpdate,
                array('group_id', 'store_id', 'updated_at', 'created_at')
            );
        }

        return $this;
    }

    /**
     * Save customer attributes.
     *
     * @param array $attributesData
     * @return Mage_ImportExport_Model_Import_Entity_V2_Eav_Customer
     */
    protected function _saveCustomerAttributes(array $attributesData)
    {
        foreach ($attributesData as $tableName => $data) {
            $tableData = array();

            foreach ($data as $customerId => $attrData) {
                foreach ($attrData as $attributeId => $value) {
                    $tableData[] = array(
                        'entity_id'      => $customerId,
                        'entity_type_id' => $this->getEntityTypeId(),
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
     * Import data rows
     *
     * @return boolean
     */
    protected function _importData()
    {
        $this->_saveCustomers();

        return true;
    }

    /**
     * EAV entity type code getter
     *
     * @abstract
     * @return string
     */
    public function getEntityTypeCode()
    {
        return $this->_getAttributeCollection()->getEntityTypeCode();
    }

    /**
     * Retrieve customer attribute EAV collection
     *
     * @return Mage_Customer_Model_Resource_Attribute_Collection
     */
    protected function _getAttributeCollection()
    {
        /** @var $collection Mage_Customer_Model_Resource_Attribute_Collection */
        $collection = Mage::getResourceModel('Mage_Customer_Model_Resource_Attribute_Collection');
        $collection->addSystemHiddenFilterWithPasswordHash();
        return $collection;
    }

    /**
     * Validate data row
     *
     * @param array $rowData
     * @param int $rowNumber
     * @return boolean
     */
    public function validateRow(array $rowData, $rowNumber)
    {
        if (isset($this->_validatedRows[$rowNumber])) { // check that row is already validated
            return !isset($this->_invalidRows[$rowNumber]);
        }
        $this->_validatedRows[$rowNumber] = true;

        $this->_processedEntitiesCount++;
        $email   = strtolower($rowData[self::COLUMN_EMAIL]);
        $website = $rowData[self::COLUMN_WEBSITE];

        if (!Zend_Validate::is($email, 'EmailAddress')) {
            $this->addRowError(self::ERROR_INVALID_EMAIL, $rowNumber);
        } elseif (!isset($this->_websiteCodeToId[$website])) {
            $this->addRowError(self::ERROR_INVALID_WEBSITE, $rowNumber);
        } else {
            if (isset($this->_newCustomers[strtolower($rowData[self::COLUMN_EMAIL])][$website])) {
                $this->addRowError(self::ERROR_DUPLICATE_EMAIL_SITE, $rowNumber);
            }
            $this->_newCustomers[$email][$website] = false;

            if (!empty($rowData[self::COLUMN_STORE]) && !isset($this->_storeCodeToId[$rowData[self::COLUMN_STORE]])) {
                $this->addRowError(self::ERROR_INVALID_STORE, $rowNumber);
            }
            // check password
            /** @var $stringHelper Mage_Core_Helper_String */
            $stringHelper = Mage::helper('Mage_Core_Helper_String');
            if (isset($rowData['password']) && strlen($rowData['password'])
                && $stringHelper->strlen($rowData['password']) < self::MIN_PASSWORD_LENGTH
            ) {
                $this->addRowError(self::ERROR_PASSWORD_LENGTH, $rowNumber);
            }
            // check simple attributes
            foreach ($this->_attributes as $attributeCode => $attributeParams) {
                if (in_array($attributeCode, $this->_ignoredAttributes)) {
                    continue;
                }
                if (isset($rowData[$attributeCode]) && strlen($rowData[$attributeCode])) {
                    $this->isAttributeValid($attributeCode, $attributeParams, $rowData, $rowNumber);
                } elseif ($attributeParams['is_required'] && !$this->_getCustomerId($email, $website)) {
                    $this->addRowError(self::ERROR_VALUE_IS_REQUIRED, $rowNumber, $attributeCode);
                }
            }
        }

        return !isset($this->_invalidRows[$rowNumber]);
    }
}
