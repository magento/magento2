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
 * @package     Mage_Eav
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * EAV Entity Form Model
 *
 * @category    Mage
 * @package     Mage_Eav
 * @author      Magento Core Team <core@magentocommerce.com>
 */
abstract class Mage_Eav_Model_Form
{
    /**
     * Current module path name
     *
     * @var string
     */
    protected $_moduleName = '';

    /**
     * Current EAV entity type code
     *
     * @var string
     */
    protected $_entityTypeCode = '';

    /**
     * Current store instance
     *
     * @var Mage_Core_Model_Store
     */
    protected $_store;

    /**
     * Current entity type instance
     *
     * @var Mage_Eav_Model_Entity_Type
     */
    protected $_entityType;

    /**
     * Current entity instance
     *
     * @var Mage_Core_Model_Abstract
     */
    protected $_entity;

    /**
     * Current form code
     *
     * @var string
     */
    protected $_formCode;

    /**
     * Array of form attributes
     *
     * @var array
     */
    protected $_attributes;

    /**
     * Array of form system attributes
     *
     * @var array
     */
    protected $_systemAttributes;

    /**
     * Array of form user defined attributes
     *
     * @var array
     */
    protected $_userAttributes;

    /**
     * Array of form attributes that is not omitted
     *
     * @var array
     */
    protected $_allowedAttributes = null;

    /**
     * Is AJAX request flag
     *
     * @var boolean
     */
    protected $_isAjax          = false;

    /**
     * Whether the invisible form fields need to be filtered/ignored
     *
     * @var bool
     */
    protected $_ignoreInvisible = true;

    /**
     * @var Magento_Validator
     */
    protected $_validator = null;

    /**
     * Checks correct module choice
     *
     * @throws Mage_Core_Exception
     */
    public function __construct()
    {
        if (empty($this->_moduleName)) {
            Mage::throwException(Mage::helper('Mage_Eav_Helper_Data')->__('Current module pathname is undefined'));
        }
        if (empty($this->_entityTypeCode)) {
            Mage::throwException(Mage::helper('Mage_Eav_Helper_Data')->__('Current module EAV entity is undefined'));
        }
    }

    /**
     * Get EAV Entity Form Attribute Collection
     *
     * @return mixed
     */
    protected function _getFormAttributeCollection()
    {
        return Mage::getResourceModel($this->_moduleName . '_Model_Resource_Form_Attribute_Collection');
    }

    /**
     * Get EAV Entity Form Attribute Collection with applied filters
     *
     * @return Mage_Eav_Model_Resource_Form_Attribute_Collection|mixed
     */
    protected function _getFilteredFormAttributeCollection()
    {
        return $this->_getFormAttributeCollection()
            ->setStore($this->getStore())
            ->setEntityType($this->getEntityType())
            ->addFormCodeFilter($this->getFormCode())
            ->setSortOrder();
    }

    /**
     * Set current store
     *
     * @param Mage_Core_Model_Store|string|int $store
     * @return Mage_Eav_Model_Form
     */
    public function setStore($store)
    {
        $this->_store = Mage::app()->getStore($store);
        return $this;
    }

    /**
     * Set entity instance
     *
     * @param Mage_Core_Model_Abstract $entity
     * @return Mage_Eav_Model_Form
     */
    public function setEntity(Mage_Core_Model_Abstract $entity)
    {
        $this->_entity = $entity;
        if ($entity->getEntityTypeId()) {
            $this->setEntityType($entity->getEntityTypeId());
        }
        return $this;
    }

    /**
     * Set entity type instance
     *
     * @param Mage_Eav_Model_Entity_Type|string|int $entityType
     * @return Mage_Eav_Model_Form
     */
    public function setEntityType($entityType)
    {
        $this->_entityType = Mage::getSingleton('Mage_Eav_Model_Config')->getEntityType($entityType);
        return $this;
    }

    /**
     * Set form code
     *
     * @param string $formCode
     * @return Mage_Eav_Model_Form
     */
    public function setFormCode($formCode)
    {
        $this->_formCode = $formCode;
        return $this;
    }

    /**
     * Return current store instance
     *
     * @return Mage_Core_Model_Store
     */
    public function getStore()
    {
        if ($this->_store === null) {
            $this->_store = Mage::app()->getStore();
        }
        return $this->_store;
    }

    /**
     * Return current form code
     *
     * @throws Mage_Core_Exception
     * @return string
     */
    public function getFormCode()
    {
        if (empty($this->_formCode)) {
            Mage::throwException(Mage::helper('Mage_Eav_Helper_Data')->__('Form code is not defined'));
        }
        return $this->_formCode;
    }

    /**
     * Return entity type instance
     * Return EAV entity type if entity type is not defined
     *
     * @return Mage_Eav_Model_Entity_Type
     */
    public function getEntityType()
    {
        if ($this->_entityType === null) {
            $this->setEntityType($this->_entityTypeCode);
        }
        return $this->_entityType;
    }

    /**
     * Return current entity instance
     *
     * @throws Mage_Core_Exception
     * @return Mage_Core_Model_Abstract
     */
    public function getEntity()
    {
        if ($this->_entity === null) {
            Mage::throwException(Mage::helper('Mage_Eav_Helper_Data')->__('Entity instance is not defined'));
        }
        return $this->_entity;
    }

    /**
     * Return array of form attributes
     *
     * @return array
     */
    public function getAttributes()
    {
        if ($this->_attributes === null) {
            $this->_attributes      = array();
            $this->_userAttributes  = array();
            /** @var $attribute Mage_Eav_Model_Attribute */
            foreach ($this->_getFilteredFormAttributeCollection() as $attribute) {
                $this->_attributes[$attribute->getAttributeCode()] = $attribute;
                if ($attribute->getIsUserDefined()) {
                    $this->_userAttributes[$attribute->getAttributeCode()] = $attribute;
                } else {
                    $this->_systemAttributes[$attribute->getAttributeCode()] = $attribute;
                }
                if (!$this->_isAttributeOmitted($attribute)) {
                    $this->_allowedAttributes[$attribute->getAttributeCode()] = $attribute;
                }
            }
        }
        return $this->_attributes;
    }

    /**
     * Return attribute instance by code or false
     *
     * @param string $attributeCode
     * @return Mage_Eav_Model_Entity_Attribute|bool
     */
    public function getAttribute($attributeCode)
    {
        $attributes = $this->getAttributes();
        if (isset($attributes[$attributeCode])) {
            return $attributes[$attributeCode];
        }
        return false;
    }

    /**
     * Return array of form user defined attributes
     *
     * @return array
     */
    public function getUserAttributes()
    {
        if ($this->_userAttributes === null) {
            // load attributes
            $this->getAttributes();
        }
        return $this->_userAttributes;
    }

    /**
     * Return array of form system attributes
     *
     * @return array
     */
    public function getSystemAttributes()
    {
        if ($this->_systemAttributes === null) {
            // load attributes
            $this->getAttributes();
        }
        return $this->_systemAttributes;
    }

    /**
     * Get not omitted attributes
     *
     * @return array
     */
    public function getAllowedAttributes()
    {
        if ($this->_allowedAttributes === null) {
            // load attributes
            $this->getAttributes();
        }
        return $this->_allowedAttributes;
    }

    /**
     * Return attribute data model by attribute
     *
     * @param Mage_Eav_Model_Entity_Attribute $attribute
     * @return Mage_Eav_Model_Attribute_Data_Abstract
     */
    protected function _getAttributeDataModel(Mage_Eav_Model_Entity_Attribute $attribute)
    {
        $dataModel = Mage_Eav_Model_Attribute_Data::factory($attribute, $this->getEntity());
        $dataModel->setIsAjaxRequest($this->getIsAjaxRequest());

        return $dataModel;
    }

    /**
     * Prepare request with data and returns it
     *
     * @param array $data
     * @return Zend_Controller_Request_Http
     */
    public function prepareRequest(array $data)
    {
        $request = clone Mage::app()->getRequest();
        $request->setParamSources();
        $request->clearParams();
        $request->setParams($data);

        return $request;
    }

    /**
     * Extract data from request and return associative data array
     *
     * @param Zend_Controller_Request_Http $request
     * @param string $scope the request scope
     * @param boolean $scopeOnly search value only in scope or search value in global too
     * @return array
     */
    public function extractData(Zend_Controller_Request_Http $request, $scope = null, $scopeOnly = true)
    {
        $data = array();
        /** @var $attribute Mage_Eav_Model_Attribute */
        foreach ($this->getAllowedAttributes() as $attribute) {
            $dataModel = $this->_getAttributeDataModel($attribute);
            $dataModel->setRequestScope($scope);
            $dataModel->setRequestScopeOnly($scopeOnly);
            $data[$attribute->getAttributeCode()] = $dataModel->extractValue($request);
        }
        return $data;
    }

    /**
     * Get validator
     *
     * @param array $data
     * @return Magento_Validator
     */
    protected function _getValidator(array $data)
    {
        if ($this->_validator === null) {
            $configFiles = Mage::getConfig()->getModuleConfigurationFiles('validation.xml');
            $validatorFactory = new Magento_Validator_Config($configFiles);
            $builder = $validatorFactory->createValidatorBuilder('eav_entity', 'form');

            $builder->addConfiguration('eav_data_validator', array(
                'method' => 'setAttributes',
                'arguments' => array($this->getAllowedAttributes())
            ));
            $builder->addConfiguration('eav_data_validator', array(
                'method' => 'setData',
                'arguments' => array($data)
            ));
            $this->_validator = $builder->createValidator();
        }
        return $this->_validator;
    }

    /**
     * Validate data array and return true or array of errors
     *
     * @param array $data
     * @return boolean|array
     */
    public function validateData(array $data)
    {
        $validator = $this->_getValidator($data);
        if (!$validator->isValid($this->getEntity())) {
            $messages = array();
            foreach ($validator->getMessages() as $errorMessages) {
                $messages = array_merge($messages, (array)$errorMessages);
            }
            return $messages;
        }
        return true;
    }

    /**
     * Compact data array to current entity
     *
     * @param array $data
     * @return Mage_Eav_Model_Form
     */
    public function compactData(array $data)
    {
        /** @var $attribute Mage_Eav_Model_Attribute */
        foreach ($this->getAllowedAttributes() as $attribute) {
            $dataModel = $this->_getAttributeDataModel($attribute);
            $dataModel->setExtractedData($data);
            if (!isset($data[$attribute->getAttributeCode()])) {
                $data[$attribute->getAttributeCode()] = false;
            }
            $dataModel->compactValue($data[$attribute->getAttributeCode()]);
        }

        return $this;
    }

    /**
     * Restore data array from SESSION to current entity
     *
     * @param array $data
     * @return Mage_Eav_Model_Form
     */
    public function restoreData(array $data)
    {
        /** @var $attribute Mage_Eav_Model_Attribute */
        foreach ($this->getAllowedAttributes() as $attribute) {
            $dataModel = $this->_getAttributeDataModel($attribute);
            $dataModel->setExtractedData($data);
            if (!isset($data[$attribute->getAttributeCode()])) {
                $data[$attribute->getAttributeCode()] = false;
            }
            $dataModel->restoreValue($data[$attribute->getAttributeCode()]);
        }
        return $this;
    }

    /**
     * Return array of entity formatted values
     *
     * @param string $format
     * @return array
     */
    public function outputData($format = Mage_Eav_Model_Attribute_Data::OUTPUT_FORMAT_TEXT)
    {
        $data = array();
        /** @var $attribute Mage_Eav_Model_Attribute */
        foreach ($this->getAllowedAttributes() as $attribute) {
            $dataModel = $this->_getAttributeDataModel($attribute);
            $dataModel->setExtractedData($data);
            $data[$attribute->getAttributeCode()] = $dataModel->outputValue($format);
        }
        return $data;
    }

    /**
     * Restore entity original data
     *
     * @return Mage_Eav_Model_Form
     */
    public function resetEntityData()
    {
        /** @var $attribute Mage_Eav_Model_Attribute */
        foreach ($this->getAllowedAttributes() as $attribute) {
            $value = $this->getEntity()->getOrigData($attribute->getAttributeCode());
            $this->getEntity()->setData($attribute->getAttributeCode(), $value);
        }
        return $this;
    }

    /**
     * Set is AJAX Request flag
     *
     * @param boolean $flag
     * @return Mage_Eav_Model_Form
     */
    public function setIsAjaxRequest($flag = true)
    {
        $this->_isAjax = (bool)$flag;
        return $this;
    }

    /**
     * Return is AJAX Request
     *
     * @return boolean
     */
    public function getIsAjaxRequest()
    {
        return $this->_isAjax;
    }

    /**
     * Set default attribute values for new entity
     *
     * @return Mage_Eav_Model_Form
     */
    public function initDefaultValues()
    {
        if (!$this->getEntity()->getId()) {
            /** @var $attribute Mage_Eav_Model_Attribute */
            foreach ($this->getAttributes() as $attribute) {
                $default = $attribute->getDefaultValue();
                if ($default != '') {
                    $this->getEntity()->setData($attribute->getAttributeCode(), $default);
                }
            }
        }
        return $this;
    }

    /**
     * Combined getter/setter whether to omit invisible attributes during rendering/validation
     *
     * @param mixed $setValue
     * @return bool|Mage_Eav_Model_Form
     */
    public function ignoreInvisible($setValue = null)
    {
        if (null !== $setValue) {
            $this->_ignoreInvisible = (bool)$setValue;
            return $this;
        }
        return $this->_ignoreInvisible;
    }

    /**
     * Whether the specified attribute needs to skip rendering/validation
     *
     * @param Mage_Eav_Model_Attribute $attribute
     * @return bool
     */
    protected function _isAttributeOmitted($attribute)
    {
        if ($this->_ignoreInvisible && !$attribute->getIsVisible()) {
            return true;
        }
        return false;
    }
}
