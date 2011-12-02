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
 * Import entity customer model
 *
 * @category    Mage
 * @package     Mage_ImportExport
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_ImportExport_Model_Import_Entity_Customer extends Mage_ImportExport_Model_Import_Entity_Abstract
{
    /**
     * Size of bunch - part of entities to save in one step.
     */
    const BUNCH_SIZE = 20;

    /**
     * Data row scopes.
     */
    const SCOPE_DEFAULT = 1;
    const SCOPE_ADDRESS = -1;

    /**
     * Permanent column names.
     *
     * Names that begins with underscore is not an attribute. This name convention is for
     * to avoid interference with same attribute name.
     */
    const COL_EMAIL   = 'email';
    const COL_WEBSITE = '_website';
    const COL_STORE   = '_store';

    /**
     * Error codes.
     */
    const ERROR_INVALID_WEBSITE      = 'invalidWebsite';
    const ERROR_INVALID_EMAIL        = 'invalidEmail';
    const ERROR_DUPLICATE_EMAIL_SITE = 'duplicateEmailSite';
    const ERROR_EMAIL_IS_EMPTY       = 'emailIsEmpty';
    const ERROR_ROW_IS_ORPHAN        = 'rowIsOrphan';
    const ERROR_VALUE_IS_REQUIRED    = 'valueIsRequired';
    const ERROR_INVALID_STORE        = 'invalidStore';
    const ERROR_EMAIL_SITE_NOT_FOUND = 'emailSiteNotFound';
    const ERROR_PASSWORD_LENGTH      = 'passwordLength';

    /**
     * Customer constants
     *
     */
    const DEFAULT_GROUP_ID = 1;
    const MAX_PASSWD_LENGTH = 6;

    /**
     * Customer address import entity model.
     *
     * @var Mage_ImportExport_Model_Import_Entity_Customer_Address
     */
    protected $_addressEntity;

    /**
     * Customer attributes parameters.
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
     * Customer account sharing. TRUE - is global, FALSE - is per website.
     *
     * @var boolean
     */
    protected $_customerGlobal;

    /**
     * Customer groups ID-to-name.
     *
     * @var array
     */
    protected $_customerGroups = array();

    /**
     * Customer entity DB table name.
     *
     * @var string
     */
    protected $_entityTable;

    /**
     * Array of attribute codes which will be ignored in validation and import procedures.
     * For example, when entity attribute has own validation and import procedures
     * or just to deny this attribute processing.
     *
     * @var array
     */
    protected $_ignoredAttributes = array('website_id', 'store_id', 'default_billing', 'default_shipping');

    /**
     * Attributes with index (not label) value.
     *
     * @var array
     */
    protected $_indexValueAttributes = array('group_id');

    /**
     * Validation failure message template definitions
     *
     * @var array
     */
    protected $_messageTemplates = array(
        self::ERROR_INVALID_WEBSITE      => 'Invalid value in Website column (website does not exists?)',
        self::ERROR_INVALID_EMAIL        => 'E-mail is invalid',
        self::ERROR_DUPLICATE_EMAIL_SITE => 'E-mail is duplicated in import file',
        self::ERROR_EMAIL_IS_EMPTY       => 'E-mail is not specified',
        self::ERROR_ROW_IS_ORPHAN        => 'Orphan rows that will be skipped due default row errors',
        self::ERROR_VALUE_IS_REQUIRED    => "Required attribute '%s' has an empty value",
        self::ERROR_INVALID_STORE        => 'Invalid value in Store column (store does not exists?)',
        self::ERROR_EMAIL_SITE_NOT_FOUND => 'E-mail and website combination is not found',
        self::ERROR_PASSWORD_LENGTH      => 'Invalid password length'
    );

    /**
     * Dry-runned customers information from import file.
     *
     * @var array
     */
    protected $_newCustomers = array();

    /**
     * Existing customers information. In form of:
     *
     * [customer e-mail] => array(
     *    [website code 1] => customer_id 1,
     *    [website code 2] => customer_id 2,
     *           ...       =>     ...      ,
     *    [website code n] => customer_id n,
     * )
     *
     * @var array
     */
    protected $_oldCustomers = array();

    /**
     * Column names that holds values with particular meaning.
     *
     * @var array
     */
    protected $_particularAttributes = array(self::COL_WEBSITE, self::COL_STORE);

    /**
     * Permanent entity columns.
     *
     * @var array
     */
    protected $_permanentAttributes = array(self::COL_EMAIL, self::COL_WEBSITE);

    /**
     * All stores code-ID pairs.
     *
     * @var array
     */
    protected $_storeCodeToId = array();

    /**
     * Website code-to-ID
     *
     * @var array
     */
    protected $_websiteCodeToId = array();

    /**
     * Website ID-to-code
     *
     * @var array
     */
    protected $_websiteIdToCode = array();

    /**
     * Constructor.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->_initWebsites()
            ->_initStores()
            ->_initCustomerGroups()
            ->_initAttributes()
            ->_initCustomers();

        $this->_entityTable   = Mage::getModel('Mage_Customer_Model_Customer')->getResource()->getEntityTable();
        $this->_addressEntity = Mage::getModel('Mage_ImportExport_Model_Import_Entity_Customer_Address', $this);
    }

    /**
     * Delete customers.
     *
     * @return Mage_ImportExport_Model_Import_Entity_Customer
     */
    protected function _deleteCustomers()
    {
        while ($bunch = $this->_dataSourceModel->getNextBunch()) {
            $idToDelete = array();

            foreach ($bunch as $rowNum => $rowData) {
                if (self::SCOPE_DEFAULT == $this->getRowScope($rowData) && $this->validateRow($rowData, $rowNum)) {
                    $idToDelete[] = $this->_oldCustomers[$rowData[self::COL_EMAIL]][$rowData[self::COL_WEBSITE]];
                }
            }
            if ($idToDelete) {
                $this->_connection->query(
                    $this->_connection->quoteInto(
                        "DELETE FROM `{$this->_entityTable}` WHERE `entity_id` IN (?)", $idToDelete
                    )
                );
            }
        }
        return $this;
    }

    /**
     * Save customer data to DB.
     *
     * @throws Exception
     * @return bool Result of operation.
     */
    protected function _importData()
    {
        if (Mage_ImportExport_Model_Import::BEHAVIOR_DELETE == $this->getBehavior()) {
            $this->_deleteCustomers();
        } else {
            $this->_saveCustomers();
            $this->_addressEntity->importData();
        }
        return true;
    }

    /**
     * Initialize customer attributes.
     *
     * @return Mage_ImportExport_Model_Import_Entity_Customer
     */
    protected function _initAttributes()
    {
        $collection = Mage::getResourceModel('Mage_Customer_Model_Resource_Attribute_Collection')
            ->addSystemHiddenFilterWithPasswordHash();
        foreach ($collection as $attribute) {
            $this->_attributes[$attribute->getAttributeCode()] = array(
                'id'          => $attribute->getId(),
                'is_required' => $attribute->getIsRequired(),
                'is_static'   => $attribute->isStatic(),
                'rules'       => $attribute->getValidateRules() ? unserialize($attribute->getValidateRules()) : null,
                'type'        => Mage_ImportExport_Model_Import::getAttributeType($attribute),
                'options'     => $this->getAttributeOptions($attribute)
            );
        }
        return $this;
    }

    /**
     * Initialize customer groups.
     *
     * @return Mage_ImportExport_Model_Import_Entity_Customer
     */
    protected function _initCustomerGroups()
    {
        foreach (Mage::getResourceModel('Mage_Customer_Model_Resource_Group_Collection') as $customerGroup) {
            $this->_customerGroups[$customerGroup->getId()] = true;
        }
        return $this;
    }

    /**
     * Initialize existent customers data.
     *
     * @return Mage_ImportExport_Model_Import_Entity_Customer
     */
    protected function _initCustomers()
    {
        foreach (Mage::getResourceModel('Mage_Customer_Model_Resource_Customer_Collection') as $customer) {
            $email = $customer->getEmail();

            if (!isset($this->_oldCustomers[$email])) {
                $this->_oldCustomers[$email] = array();
            }
            $this->_oldCustomers[$email][$this->_websiteIdToCode[$customer->getWebsiteId()]] = $customer->getId();
        }
        $this->_customerGlobal = Mage::getModel('Mage_Customer_Model_Customer')->getSharingConfig()->isGlobalScope();

        return $this;
    }

    /**
     * Initialize stores hash.
     *
     * @return Mage_ImportExport_Model_Import_Entity_Customer
     */
    protected function _initStores()
    {
        foreach (Mage::app()->getStores(true) as $store) {
            $this->_storeCodeToId[$store->getCode()] = $store->getId();
        }
        return $this;
    }

    /**
     * Initialize website values.
     *
     * @return Mage_ImportExport_Model_Import_Entity_Customer
     */
    protected function _initWebsites()
    {
        /** @var $website Mage_Core_Model_Website */
        foreach (Mage::app()->getWebsites(true) as $website) {
            $this->_websiteCodeToId[$website->getCode()] = $website->getId();
            $this->_websiteIdToCode[$website->getId()]   = $website->getCode();
        }
        return $this;
    }

    /**
     * Gather and save information about customer entities.
     *
     * @return Mage_ImportExport_Model_Import_Entity_Customer
     */
    protected function _saveCustomers()
    {
        /** @var $resource Mage_Customer_Model_Customer */
        $resource       = Mage::getModel('Mage_Customer_Model_Customer');
        $strftimeFormat = Varien_Date::convertZendToStrftime(Varien_Date::DATETIME_INTERNAL_FORMAT, true, true);
        $table = $resource->getResource()->getEntityTable();
        $nextEntityId   = Mage::getResourceHelper('Mage_ImportExport')->getNextAutoincrement($table);
        $passId         = $resource->getAttribute('password_hash')->getId();
        $passTable      = $resource->getAttribute('password_hash')->getBackend()->getTable();

        while ($bunch = $this->_dataSourceModel->getNextBunch()) {
            $entityRowsIn = array();
            $entityRowsUp = array();
            $attributes   = array();

            foreach ($bunch as $rowNum => $rowData) {
                if (!$this->validateRow($rowData, $rowNum)) {
                    continue;
                }
                if (self::SCOPE_DEFAULT == $this->getRowScope($rowData)) {
                    // entity table data
                    $entityRow = array(
                        'group_id'   => empty($rowData['group_id']) ? self::DEFAULT_GROUP_ID : $rowData['group_id'],
                        'store_id'   => empty($rowData[self::COL_STORE])
                                        ? 0 : $this->_storeCodeToId[$rowData[self::COL_STORE]],
                        'created_at' => empty($rowData['created_at'])
                                        ? now() : gmstrftime($strftimeFormat, strtotime($rowData['created_at'])),
                        'updated_at' => now()
                    );
                    if (isset($this->_oldCustomers[$rowData[self::COL_EMAIL]][$rowData[self::COL_WEBSITE]])) { // edit
                        $entityId = $this->_oldCustomers[$rowData[self::COL_EMAIL]][$rowData[self::COL_WEBSITE]];
                        $entityRow['entity_id'] = $entityId;
                        $entityRowsUp[] = $entityRow;
                    } else { // create
                        $entityId                      = $nextEntityId++;
                        $entityRow['entity_id']        = $entityId;
                        $entityRow['entity_type_id']   = $this->_entityTypeId;
                        $entityRow['attribute_set_id'] = 0;
                        $entityRow['website_id']       = $this->_websiteCodeToId[$rowData[self::COL_WEBSITE]];
                        $entityRow['email']            = $rowData[self::COL_EMAIL];
                        $entityRow['is_active']        = 1;
                        $entityRowsIn[]                = $entityRow;

                        $this->_newCustomers[$rowData[self::COL_EMAIL]][$rowData[self::COL_WEBSITE]] = $entityId;
                    }
                    // attribute values
                    foreach (array_intersect_key($rowData, $this->_attributes) as $attrCode => $value) {
                        if (!$this->_attributes[$attrCode]['is_static'] && strlen($value)) {
                            /** @var $attribute Mage_Customer_Model_Attribute */
                            $attribute  = $resource->getAttribute($attrCode);
                            $backModel  = $attribute->getBackendModel();
                            $attrParams = $this->_attributes[$attrCode];

                            if ('select' == $attrParams['type']) {
                                $value = $attrParams['options'][strtolower($value)];
                            } elseif ('datetime' == $attrParams['type']) {
                                $value = gmstrftime($strftimeFormat, strtotime($value));
                            } elseif ($backModel) {
                                $attribute->getBackend()->beforeSave($resource->setData($attrCode, $value));
                                $value = $resource->getData($attrCode);
                            }
                            $attributes[$attribute->getBackend()->getTable()][$entityId][$attrParams['id']] = $value;

                            // restore 'backend_model' to avoid default setting
                            $attribute->setBackendModel($backModel);
                        }
                    }
                    // password change/set
                    if (isset($rowData['password']) && strlen($rowData['password'])) {
                        $attributes[$passTable][$entityId][$passId] = $resource->hashPassword($rowData['password']);
                    }
                }
            }
            $this->_saveCustomerEntity($entityRowsIn, $entityRowsUp)->_saveCustomerAttributes($attributes);
        }
        return $this;
    }

    /**
     * Save customer attributes.
     *
     * @param array $attributesData
     * @return Mage_ImportExport_Model_Import_Entity_Customer
     */
    protected function _saveCustomerAttributes(array $attributesData)
    {
        foreach ($attributesData as $tableName => $data) {
            $tableData = array();

            foreach ($data as $customerId => $attrData) {
                foreach ($attrData as $attributeId => $value) {
                    $tableData[] = array(
                        'entity_id'      => $customerId,
                        'entity_type_id' => $this->_entityTypeId,
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
     * Update and insert data in entity table.
     *
     * @param array $entityRowsIn Row for insert
     * @param array $entityRowsUp Row for update
     * @return Mage_ImportExport_Model_Import_Entity_Customer
     */
    protected function _saveCustomerEntity(array $entityRowsIn, array $entityRowsUp)
    {
        if ($entityRowsIn) {
            $this->_connection->insertMultiple($this->_entityTable, $entityRowsIn);
        }
        if ($entityRowsUp) {
            $this->_connection->insertOnDuplicate(
                $this->_entityTable,
                $entityRowsUp,
                array('group_id', 'store_id', 'updated_at', 'created_at')
            );
        }
        return $this;
    }

    /**
     * Get customer ID. Method tries to find ID from old and new customers. If it fails - it returns NULL.
     *
     * @param string $email
     * @param string $websiteCode
     * @return string|null
     */
    public function getCustomerId($email, $websiteCode)
    {
        if (isset($this->_oldCustomers[$email][$websiteCode])) {
            return $this->_oldCustomers[$email][$websiteCode];
        } elseif (isset($this->_newCustomers[$email][$websiteCode])) {
            return $this->_newCustomers[$email][$websiteCode];
        } else {
            return null;
        }
    }

    /**
     * EAV entity type code getter.
     *
     * @abstract
     * @return string
     */
    public function getEntityTypeCode()
    {
        return 'customer';
    }

    /**
     * Obtain scope of the row from row data.
     *
     * @param array $rowData
     * @return int
     */
    public function getRowScope(array $rowData)
    {
        return strlen(trim($rowData[self::COL_EMAIL])) ? self::SCOPE_DEFAULT : self::SCOPE_ADDRESS;
    }

    /**
     * Is attribute contains particular data (not plain entity attribute).
     *
     * @param string $attrCode
     * @return bool
     */
    public function isAttributeParticular($attrCode)
    {
        return parent::isAttributeParticular($attrCode) || $this->_addressEntity->isAttributeParticular($attrCode);
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
        static $email   = null; // e-mail is remembered through all customer rows
        static $website = null; // website is remembered through all customer rows

        if (isset($this->_validatedRows[$rowNum])) { // check that row is already validated
            return !isset($this->_invalidRows[$rowNum]);
        }
        $this->_validatedRows[$rowNum] = true;

        $rowScope = $this->getRowScope($rowData);

        if (self::SCOPE_DEFAULT == $rowScope) {
            $this->_processedEntitiesCount ++;
        }
        // BEHAVIOR_DELETE use specific validation logic
        if (Mage_ImportExport_Model_Import::BEHAVIOR_DELETE == $this->getBehavior()) {
            if (self::SCOPE_DEFAULT == $rowScope
                    && !isset($this->_oldCustomers[$rowData[self::COL_EMAIL]][$rowData[self::COL_WEBSITE]])) {
                $this->addRowError(self::ERROR_EMAIL_SITE_NOT_FOUND, $rowNum);
            }
        } elseif (self::SCOPE_DEFAULT == $rowScope) { // row is SCOPE_DEFAULT = new customer block begins
            $email   = $rowData[self::COL_EMAIL];
            $website = $rowData[self::COL_WEBSITE];

            if (!Zend_Validate::is($email, 'EmailAddress')) {
                $this->addRowError(self::ERROR_INVALID_EMAIL, $rowNum);
            } elseif (!isset($this->_websiteCodeToId[$website])) {
                $this->addRowError(self::ERROR_INVALID_WEBSITE, $rowNum);
            } else {
                if (isset($this->_newCustomers[$email][$website])) {
                    $this->addRowError(self::ERROR_DUPLICATE_EMAIL_SITE, $rowNum);
                }
                $this->_newCustomers[$email][$website] = false;

                if (!empty($rowData[self::COL_STORE]) && !isset($this->_storeCodeToId[$rowData[self::COL_STORE]])) {
                    $this->addRowError(self::ERROR_INVALID_STORE, $rowNum);
                }
                // check password
                if (isset($rowData['password']) && strlen($rowData['password'])
                    && Mage::helper('Mage_Core_Helper_String')->strlen($rowData['password']) < self::MAX_PASSWD_LENGTH
                ) {
                    $this->addRowError(self::ERROR_PASSWORD_LENGTH, $rowNum);
                }
                // check simple attributes
                foreach ($this->_attributes as $attrCode => $attrParams) {
                    if (in_array($attrCode, $this->_ignoredAttributes)) {
                        continue;
                    }
                    if (isset($rowData[$attrCode]) && strlen($rowData[$attrCode])) {
                        $this->isAttributeValid($attrCode, $attrParams, $rowData, $rowNum);
                    } elseif ($attrParams['is_required'] && !isset($this->_oldCustomers[$email][$website])) {
                        $this->addRowError(self::ERROR_VALUE_IS_REQUIRED, $rowNum, $attrCode);
                    }
                }
            }
            if (isset($this->_invalidRows[$rowNum])) {
                $email = false; // mark row as invalid for next address rows
            }
        } else {
            if (null === $email) { // first row is not SCOPE_DEFAULT
                $this->addRowError(self::ERROR_EMAIL_IS_EMPTY, $rowNum);
            } elseif (false === $email) { // SCOPE_DEFAULT row is invalid
                $this->addRowError(self::ERROR_ROW_IS_ORPHAN, $rowNum);
            }
        }
        // validate row data by address entity
        $this->_addressEntity->validateRow($rowData, $rowNum);

        return !isset($this->_invalidRows[$rowNum]);
    }
}
