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
 * Import entity customer combined model
 *
 * @category    Mage
 * @package     Mage_ImportExport
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_ImportExport_Model_Import_Entity_CustomerComposite
    extends Mage_ImportExport_Model_Import_EntityAbstract
{
    /**#@+
     * Particular column names
     *
     * Names that begins with underscore is not an attribute. This name convention is for
     * to avoid interference with same attribute name.
     */
    const COLUMN_ADDRESS_PREFIX   = '_address_';
    const COLUMN_DEFAULT_BILLING  = '_address_default_billing_';
    const COLUMN_DEFAULT_SHIPPING = '_address_default_shipping_';
    /**#@-*/

    /**#@+
     * Data row scopes
     */
    const SCOPE_DEFAULT = 1;
    const SCOPE_ADDRESS = -1;
    /**#@-*/

    /**#@+
     * Component entity names
     */
    const COMPONENT_ENTITY_CUSTOMER = 'customer';
    const COMPONENT_ENTITY_ADDRESS  = 'address';
    /**#@-*/

    /**
     * Error code for orphan rows
     */
    const ERROR_ROW_IS_ORPHAN = 'rowIsOrphan';

    /**
     * @var Mage_ImportExport_Model_Import_Entity_Eav_Customer
     */
    protected $_customerEntity;

    /**
     * @var Mage_ImportExport_Model_Import_Entity_Eav_Customer_Address
     */
    protected $_addressEntity;

    /**
     * Column names that holds values with particular meaning
     *
     * @var array
     */
    protected $_specialAttributes = array(
        Mage_ImportExport_Model_Import_Entity_Eav_Customer::COLUMN_WEBSITE,
        Mage_ImportExport_Model_Import_Entity_Eav_Customer::COLUMN_STORE,
        self::COLUMN_DEFAULT_BILLING,
        self::COLUMN_DEFAULT_SHIPPING,
    );

    /**
     * Permanent entity columns
     *
     * @var array
     */
    protected $_permanentAttributes = array(
        Mage_ImportExport_Model_Import_Entity_Eav_Customer::COLUMN_EMAIL,
        Mage_ImportExport_Model_Import_Entity_Eav_Customer::COLUMN_WEBSITE,
    );

    /**
     * Customer attributes
     *
     * @var array
     */
    protected $_customerAttributes = array();

    /**
     * Address attributes
     *
     * @var array
     */
    protected $_addressAttributes = array();

    /**
     * Website code of current customer row
     *
     * @var string
     */
    protected $_currentWebsiteCode;

    /**
     * Email of current customer
     *
     * @var string
     */
    protected $_currentEmail;

    /**
     * Next customer entity ID
     *
     * @var int
     */
    protected $_nextCustomerId;

    /**
     * DB data source models
     *
     * @var Mage_ImportExport_Model_Resource_Import_Data[]
     */
    protected $_dataSourceModels;

    /**
     * Class constructor
     *
     * @param array $data
     */
    public function __construct(array $data = array())
    {
        parent::__construct($data);

        $this->addMessageTemplate(self::ERROR_ROW_IS_ORPHAN,
            $this->_helper('Mage_ImportExport_Helper_Data')
                ->__('Orphan rows that will be skipped due default row errors')
        );

        $this->_availableBehaviors = array(
            Mage_ImportExport_Model_Import::BEHAVIOR_APPEND,
            Mage_ImportExport_Model_Import::BEHAVIOR_DELETE
        );

        // customer entity stuff
        if (isset($data['customer_data_source_model'])) {
            $this->_dataSourceModels['customer'] = $data['customer_data_source_model'];
        } else {
            $arguments = array(
                'entity_type' => Mage_ImportExport_Model_Import_Entity_CustomerComposite::COMPONENT_ENTITY_CUSTOMER,
            );
            $this->_dataSourceModels['customer']
                = Mage::getResourceModel('Mage_ImportExport_Model_Resource_Import_CustomerComposite_Data',
                    array('arguments' => $arguments)
                );
        }
        if (isset($data['customer_entity'])) {
            $this->_customerEntity = $data['customer_entity'];
        } else {
            $data['data_source_model'] = $this->_dataSourceModels['customer'];
            $this->_customerEntity = Mage::getModel('Mage_ImportExport_Model_Import_Entity_Eav_Customer',
                array('data' => $data));
            unset($data['data_source_model']);
        }
        $this->_initCustomerAttributes();

        // address entity stuff
        if (isset($data['address_data_source_model'])) {
            $this->_dataSourceModels['address'] = $data['address_data_source_model'];
        } else {
            $arguments = array(
                'entity_type' => Mage_ImportExport_Model_Import_Entity_CustomerComposite::COMPONENT_ENTITY_ADDRESS,
                'customer_attributes' => $this->_customerAttributes
            );
            $this->_dataSourceModels['address']
                = Mage::getResourceModel('Mage_ImportExport_Model_Resource_Import_CustomerComposite_Data',
                    array('arguments' => $arguments)
                );
        }
        if (isset($data['address_entity'])) {
            $this->_addressEntity = $data['address_entity'];
        } else {
            $data['data_source_model'] = $this->_dataSourceModels['address'];
            $this->_addressEntity
                = Mage::getModel('Mage_ImportExport_Model_Import_Entity_Eav_Customer_Address', array('data' => $data));
            unset($data['data_source_model']);
        }
        $this->_initAddressAttributes();

        // next customer id
        if (isset($data['next_customer_id'])) {
            $this->_nextCustomerId = $data['next_customer_id'];
        } else {
            /** @var $resourceHelper Mage_ImportExport_Model_Resource_Helper_Mysql4 */
            $resourceHelper = Mage::getResourceHelper('Mage_ImportExport');
            $this->_nextCustomerId = $resourceHelper->getNextAutoincrement($this->_customerEntity->getEntityTable());
        }
    }

    /**
     * Collect customer attributes
     *
     * @return Mage_ImportExport_Model_Import_Entity_CustomerComposite
     */
    protected function _initCustomerAttributes()
    {
        /** @var $attribute Mage_Eav_Model_Entity_Attribute */
        foreach ($this->_customerEntity->getAttributeCollection() as $attribute) {
            $this->_customerAttributes[] = $attribute->getAttributeCode();
        }

        return $this;
    }

    /**
     * Collect address attributes
     *
     * @return Mage_ImportExport_Model_Import_Entity_CustomerComposite
     */
    protected function _initAddressAttributes()
    {
        /** @var $attribute Mage_Eav_Model_Entity_Attribute */
        foreach ($this->_addressEntity->getAttributeCollection() as $attribute) {
            $this->_addressAttributes[] = $attribute->getAttributeCode();
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
        $result = $this->_customerEntity->importData();
        if ($this->getBehavior() != Mage_ImportExport_Model_Import::BEHAVIOR_DELETE) {
            return $result && $this->_addressEntity->importData();
        }

        return $result;
    }

    /**
     * Imported entity type code getter
     *
     * @return string
     */
    public function getEntityTypeCode()
    {
        return 'customer_composite';
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
        $rowScope = $this->_getRowScope($rowData);
        if ($rowScope == self::SCOPE_DEFAULT) {
            if ($this->_customerEntity->validateRow($rowData, $rowNumber)) {
                $this->_currentWebsiteCode
                    = $rowData[Mage_ImportExport_Model_Import_Entity_Eav_Customer::COLUMN_WEBSITE];
                $this->_currentEmail = strtolower(
                    $rowData[Mage_ImportExport_Model_Import_Entity_Eav_Customer::COLUMN_EMAIL]
                );

                // Add new customer data into customer storage for address entity instance
                $websiteId = $this->_customerEntity->getWebsiteId($this->_currentWebsiteCode);
                if (!$this->_addressEntity->getCustomerStorage()->getCustomerId($this->_currentEmail, $websiteId)) {
                    $customerData = new Varien_Object(array(
                        'id'         => $this->_nextCustomerId,
                        'email'      => $this->_currentEmail,
                        'website_id' => $websiteId
                    ));
                    $this->_addressEntity->getCustomerStorage()->addCustomer($customerData);
                    $this->_nextCustomerId++;
                }

                return $this->_validateAddressRow($rowData, $rowNumber);
            } else {
                $this->_currentWebsiteCode = null;
                $this->_currentEmail = null;
            }
        } else {
            if (!empty($this->_currentWebsiteCode) && !empty($this->_currentEmail)) {
                return $this->_validateAddressRow($rowData, $rowNumber);
            } else {
                $this->addRowError(self::ERROR_ROW_IS_ORPHAN, $rowNumber);
            }
        }

        return false;
    }

    /**
     * Validate address row
     *
     * @param array $rowData
     * @param int $rowNumber
     * @return bool
     */
    protected function _validateAddressRow(array $rowData, $rowNumber)
    {
        if ($this->getBehavior() == Mage_ImportExport_Model_Import::BEHAVIOR_DELETE) {
            return true;
        }

        $rowData = $this->_prepareAddressRowData($rowData);
        if (empty($rowData)) {
            return true;
        } else {
            $rowData[Mage_ImportExport_Model_Import_Entity_Eav_Customer_Address::COLUMN_WEBSITE]
                = $this->_currentWebsiteCode;
            $rowData[Mage_ImportExport_Model_Import_Entity_Eav_Customer_Address::COLUMN_EMAIL] = $this->_currentEmail;
            $rowData[Mage_ImportExport_Model_Import_Entity_Eav_Customer_Address::COLUMN_ADDRESS_ID] = null;

            return $this->_addressEntity->validateRow($rowData, $rowNumber);
        }
    }

    /**
     * Prepare data row for address entity validation or import
     *
     * @param array $rowData
     * @return array
     */
    protected function _prepareAddressRowData(array $rowData)
    {
        $excludedAttributes = array(
            self::COLUMN_DEFAULT_BILLING,
            self::COLUMN_DEFAULT_SHIPPING
        );

        unset(
            $rowData[Mage_ImportExport_Model_Import_Entity_Eav_Customer::COLUMN_WEBSITE],
            $rowData[Mage_ImportExport_Model_Import_Entity_Eav_Customer::COLUMN_STORE]
        );

        $result = array();
        foreach ($rowData as $key => $value) {
            if (!in_array($key, $this->_customerAttributes) && !empty($value)) {
                if (!in_array($key, $excludedAttributes)) {
                    $key = str_replace(self::COLUMN_ADDRESS_PREFIX, '', $key);
                }
                $result[$key] = $value;
            }
        }

        return $result;
    }

    /**
     * Obtain scope of the row from row data
     *
     * @param array $rowData
     * @return int
     */
    protected function _getRowScope(array $rowData)
    {
        if (!isset($rowData[Mage_ImportExport_Model_Import_Entity_Eav_Customer::COLUMN_EMAIL])) {
            return self::SCOPE_ADDRESS;
        }
        return strlen(trim($rowData[Mage_ImportExport_Model_Import_Entity_Eav_Customer::COLUMN_EMAIL]))
            ? self::SCOPE_DEFAULT : self::SCOPE_ADDRESS;
    }

    /**
     * Set data from outside to change behavior
     *
     * @param array $parameters
     * @return Mage_ImportExport_Model_Import_Entity_CustomerComposite
     */
    public function setParameters(array $parameters)
    {
        parent::setParameters($parameters);

        if ($this->getBehavior() == Mage_ImportExport_Model_Import::BEHAVIOR_APPEND) {
            $parameters['behavior'] = Mage_ImportExport_Model_Import::BEHAVIOR_ADD_UPDATE;
        }

        $this->_customerEntity->setParameters($parameters);
        $this->_addressEntity->setParameters($parameters);

        return $this;
    }

    /**
     * Source model setter
     *
     * @param Mage_ImportExport_Model_Import_SourceAbstract $source
     * @return Mage_ImportExport_Model_Import_EntityAbstract
     */
    public function setSource(Mage_ImportExport_Model_Import_SourceAbstract $source)
    {
        $this->_customerEntity->setSource($source);
        $this->_addressEntity->setSource($source);

        return parent::setSource($source);
    }

    /**
     * Returns error information grouped by error types and translated (if possible)
     *
     * @return array
     */
    public function getErrorMessages()
    {
        $errors = $this->_customerEntity->getErrorMessages();
        $addressErrors = $this->_addressEntity->getErrorMessages();
        foreach ($addressErrors as $message => $rowNumbers) {
            if (isset($errors[$message])) {
                foreach ($rowNumbers as $rowNumber) {
                    $errors[$message][] = $rowNumber;
                }
                $errors[$message] = array_unique($errors[$message]);
            } else {
                $errors[$message] = $rowNumbers;
            }
        }

        return array_merge($errors, parent::getErrorMessages());
    }

    /**
     * Returns error counter value
     *
     * @return int
     */
    public function getErrorsCount()
    {
        return $this->_customerEntity->getErrorsCount() + $this->_addressEntity->getErrorsCount()
            + parent::getErrorsCount();
    }

    /**
     * Returns invalid rows count
     *
     * @return int
     */
    public function getInvalidRowsCount()
    {
        return $this->_customerEntity->getInvalidRowsCount() + $this->_addressEntity->getInvalidRowsCount()
            + parent::getInvalidRowsCount();
    }

    /**
     * Returns number of checked entities
     *
     * @return int
     */
    public function getProcessedEntitiesCount()
    {
        return $this->_customerEntity->getProcessedEntitiesCount() + $this->_addressEntity->getProcessedEntitiesCount();
    }

    /**
     * Is attribute contains particular data (not plain customer attribute)
     *
     * @param string $attributeCode
     * @return bool
     */
    public function isAttributeParticular($attributeCode)
    {
        if (in_array(str_replace(self::COLUMN_ADDRESS_PREFIX, '', $attributeCode), $this->_addressAttributes)) {
            return true;
        } else {
            return parent::isAttributeParticular($attributeCode);
        }
    }

    /**
     * Prepare validated row data for saving to db
     *
     * @param array $rowData
     * @return array
     */
    protected function _prepareRowForDb(array $rowData)
    {
        $rowData['_scope'] = $this->_getRowScope($rowData);
        $rowData[Mage_ImportExport_Model_Import_Entity_Eav_Customer_Address::COLUMN_WEBSITE]
            = $this->_currentWebsiteCode;
        $rowData[Mage_ImportExport_Model_Import_Entity_Eav_Customer_Address::COLUMN_EMAIL] = $this->_currentEmail;
        $rowData[Mage_ImportExport_Model_Import_Entity_Eav_Customer_Address::COLUMN_ADDRESS_ID] = null;

        return parent::_prepareRowForDb($rowData);
    }
}
