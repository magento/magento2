<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Model;

use Magento\Framework\App\RequestInterface;

/**
 * EAV Entity Form Model
 *
 * @api
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 2.0.0
 */
abstract class Form
{
    /**
     * Current module path name
     *
     * @var string
     * @since 2.0.0
     */
    protected $_moduleName = '';

    /**
     * Current EAV entity type code
     *
     * @var string
     * @since 2.0.0
     */
    protected $_entityTypeCode = '';

    /**
     * Current store instance
     *
     * @var \Magento\Store\Model\Store
     * @since 2.0.0
     */
    protected $_store;

    /**
     * Current entity type instance
     *
     * @var \Magento\Eav\Model\Entity\Type
     * @since 2.0.0
     */
    protected $_entityType;

    /**
     * Current entity instance
     *
     * @var \Magento\Framework\Model\AbstractModel
     * @since 2.0.0
     */
    protected $_entity;

    /**
     * Current form code
     *
     * @var string
     * @since 2.0.0
     */
    protected $_formCode;

    /**
     * Array of form attributes
     *
     * @var array
     * @since 2.0.0
     */
    protected $_attributes;

    /**
     * Array of form system attributes
     *
     * @var array
     * @since 2.0.0
     */
    protected $_systemAttributes;

    /**
     * Array of form user defined attributes
     *
     * @var array
     * @since 2.0.0
     */
    protected $_userAttributes;

    /**
     * Array of form attributes that is not omitted
     *
     * @var array
     * @since 2.0.0
     */
    protected $_allowedAttributes = null;

    /**
     * Is AJAX request flag
     *
     * @var bool
     * @since 2.0.0
     */
    protected $_isAjax = false;

    /**
     * Whether the invisible form fields need to be filtered/ignored
     *
     * @var bool
     * @since 2.0.0
     */
    protected $_ignoreInvisible = true;

    /**
     * @var \Magento\Framework\Validator
     * @since 2.0.0
     */
    protected $_validator = null;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     * @since 2.0.0
     */
    protected $_storeManager;

    /**
     * @var \Magento\Eav\Model\Config
     * @since 2.0.0
     */
    protected $_eavConfig;

    /**
     * @var \Magento\Framework\Module\Dir\Reader
     * @since 2.0.0
     */
    protected $_modulesReader;

    /**
     * @var \Magento\Eav\Model\AttributeDataFactory
     * @since 2.0.0
     */
    protected $_attrDataFactory;

    /**
     * @var \Magento\Framework\Validator\UniversalFactory $universalFactory
     * @since 2.0.0
     */
    protected $_universalFactory;

    /**
     * @var RequestInterface
     * @since 2.0.0
     */
    protected $_httpRequest;

    /**
     * @var \Magento\Framework\Validator\ConfigFactory
     * @since 2.0.0
     */
    protected $_validatorConfigFactory;

    /**
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Framework\Module\Dir\Reader $modulesReader
     * @param \Magento\Eav\Model\AttributeDataFactory $attrDataFactory
     * @param \Magento\Framework\Validator\UniversalFactory $universalFactory
     * @param RequestInterface $httpRequest
     * @param \Magento\Framework\Validator\ConfigFactory $validatorConfigFactory
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Framework\Module\Dir\Reader $modulesReader,
        \Magento\Eav\Model\AttributeDataFactory $attrDataFactory,
        \Magento\Framework\Validator\UniversalFactory $universalFactory,
        RequestInterface $httpRequest,
        \Magento\Framework\Validator\ConfigFactory $validatorConfigFactory
    ) {
        if (empty($this->_moduleName)) {
            throw new \Magento\Framework\Exception\LocalizedException(__('The current module pathname is undefined.'));
        }
        if (empty($this->_entityTypeCode)) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('The current module EAV entity is undefined.')
            );
        }
        $this->_storeManager = $storeManager;
        $this->_eavConfig = $eavConfig;
        $this->_modulesReader = $modulesReader;
        $this->_attrDataFactory = $attrDataFactory;
        $this->_universalFactory = $universalFactory;
        $this->_httpRequest = $httpRequest;
        $this->_validatorConfigFactory = $validatorConfigFactory;
    }

    /**
     * Get EAV Entity Form Attribute Collection
     *
     * @return mixed
     * @since 2.0.0
     */
    protected function _getFormAttributeCollection()
    {
        return $this->_universalFactory->create(
            str_replace('_', '\\', $this->_moduleName) . '\\Model\\ResourceModel\\Form\\Attribute\\Collection'
        );
    }

    /**
     * Get EAV Entity Form Attribute Collection with applied filters
     *
     * @return \Magento\Eav\Model\ResourceModel\Form\Attribute\Collection
     * @since 2.0.0
     */
    protected function _getFilteredFormAttributeCollection()
    {
        return $this->_getFormAttributeCollection()->setStore(
            $this->getStore()
        )->setEntityType(
            $this->getEntityType()
        )->addFormCodeFilter(
            $this->getFormCode()
        )->setSortOrder();
    }

    /**
     * Set current store
     *
     * @param \Magento\Store\Model\Store|string|int $store
     * @return $this
     * @codeCoverageIgnore
     * @since 2.0.0
     */
    public function setStore($store)
    {
        $this->_store = $this->_storeManager->getStore($store);
        return $this;
    }

    /**
     * Set entity instance
     *
     * @param \Magento\Framework\Model\AbstractModel $entity
     * @return $this
     * @since 2.0.0
     */
    public function setEntity(\Magento\Framework\Model\AbstractModel $entity)
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
     * @param \Magento\Eav\Model\Entity\Type|string|int $entityType
     * @return $this
     * @since 2.0.0
     */
    public function setEntityType($entityType)
    {
        $this->_entityType = $this->_eavConfig->getEntityType($entityType);
        return $this;
    }

    /**
     * Set form code
     *
     * @param string $formCode
     * @return $this
     * @since 2.0.0
     */
    public function setFormCode($formCode)
    {
        $this->_formCode = $formCode;
        return $this;
    }

    /**
     * Return current store instance
     *
     * @return \Magento\Store\Model\Store
     * @since 2.0.0
     */
    public function getStore()
    {
        if ($this->_store === null) {
            $this->_store = $this->_storeManager->getStore();
        }
        return $this->_store;
    }

    /**
     * Return current form code
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return string
     * @since 2.0.0
     */
    public function getFormCode()
    {
        if (empty($this->_formCode)) {
            throw new \Magento\Framework\Exception\LocalizedException(__('The form code is not defined.'));
        }
        return $this->_formCode;
    }

    /**
     * Return entity type instance
     * Return EAV entity type if entity type is not defined
     *
     * @return \Magento\Eav\Model\Entity\Type
     * @since 2.0.0
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
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return \Magento\Framework\Model\AbstractModel
     * @since 2.0.0
     */
    public function getEntity()
    {
        if ($this->_entity === null) {
            throw new \Magento\Framework\Exception\LocalizedException(__('The entity instance is not defined.'));
        }
        return $this->_entity;
    }

    /**
     * Return array of form attributes
     *
     * @return array
     * @since 2.0.0
     */
    public function getAttributes()
    {
        if ($this->_attributes === null) {
            $this->_attributes = [];
            $this->_userAttributes = [];
            /** @var $attribute \Magento\Eav\Model\Attribute */
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
     * @return \Magento\Eav\Model\Entity\Attribute|bool
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @param \Magento\Eav\Model\Entity\Attribute $attribute
     * @return \Magento\Eav\Model\Attribute\Data\AbstractData
     * @since 2.0.0
     */
    protected function _getAttributeDataModel(\Magento\Eav\Model\Entity\Attribute $attribute)
    {
        $dataModel = $this->_attrDataFactory->create($attribute, $this->getEntity());
        $dataModel->setIsAjaxRequest($this->getIsAjaxRequest());

        return $dataModel;
    }

    /**
     * Prepare request with data and returns it
     *
     * @param array $data
     * @return RequestInterface
     * @since 2.0.0
     */
    public function prepareRequest(array $data)
    {
        $request = clone $this->_httpRequest;
        $request->clearParams();
        $request->setParams($data);
        return $request;
    }

    /**
     * Extract data from request and return associative data array
     *
     * @param RequestInterface $request
     * @param string $scope the request scope
     * @param bool $scopeOnly search value only in scope or search value in global too
     * @return array
     * @since 2.0.0
     */
    public function extractData(RequestInterface $request, $scope = null, $scopeOnly = true)
    {
        $data = [];
        /** @var $attribute \Magento\Eav\Model\Attribute */
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
     * @return \Magento\Framework\Validator
     * @since 2.0.0
     */
    protected function _getValidator(array $data)
    {
        if ($this->_validator === null) {
            $configFiles = $this->_modulesReader->getConfigurationFiles('validation.xml');
            /** @var $validatorFactory \Magento\Framework\Validator\Config */
            $validatorFactory = $this->_validatorConfigFactory->create(['configFiles' => $configFiles]);
            $builder = $validatorFactory->createValidatorBuilder('eav_entity', 'form');

            $builder->addConfiguration(
                'eav_data_validator',
                ['method' => 'setAttributes', 'arguments' => [$this->getAllowedAttributes()]]
            );
            $builder->addConfiguration(
                'eav_data_validator',
                ['method' => 'setData', 'arguments' => [$data]]
            );
            $this->_validator = $builder->createValidator();
        }
        return $this->_validator;
    }

    /**
     * Validate data array and return true or array of errors
     *
     * @param array $data
     * @return bool|array
     * @since 2.0.0
     */
    public function validateData(array $data)
    {
        $validator = $this->_getValidator($data);
        if (!$validator->isValid($this->getEntity())) {
            $messages = [];
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
     * @return $this
     * @since 2.0.0
     */
    public function compactData(array $data)
    {
        /** @var $attribute \Magento\Eav\Model\Attribute */
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
     * @return $this
     * @since 2.0.0
     */
    public function restoreData(array $data)
    {
        /** @var $attribute \Magento\Eav\Model\Attribute */
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
     * @since 2.0.0
     */
    public function outputData($format = \Magento\Eav\Model\AttributeDataFactory::OUTPUT_FORMAT_TEXT)
    {
        $data = [];
        /** @var $attribute \Magento\Eav\Model\Attribute */
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
     * @return $this
     * @since 2.0.0
     */
    public function resetEntityData()
    {
        /** @var $attribute \Magento\Eav\Model\Attribute */
        foreach ($this->getAllowedAttributes() as $attribute) {
            $value = $this->getEntity()->getOrigData($attribute->getAttributeCode());
            $this->getEntity()->setData($attribute->getAttributeCode(), $value);
        }
        return $this;
    }

    /**
     * Set is AJAX Request flag
     *
     * @param bool $flag
     * @return $this
     * @since 2.0.0
     */
    public function setIsAjaxRequest($flag = true)
    {
        $this->_isAjax = (bool)$flag;
        return $this;
    }

    /**
     * Return is AJAX Request
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     * @since 2.0.0
     */
    public function getIsAjaxRequest()
    {
        return $this->_isAjax;
    }

    /**
     * Set default attribute values for new entity
     *
     * @return $this
     * @since 2.0.0
     */
    public function initDefaultValues()
    {
        if (!$this->getEntity()->getId()) {
            /** @var $attribute \Magento\Eav\Model\Attribute */
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
     * @return bool|$this
     * @since 2.0.0
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
     * @param \Magento\Eav\Model\Attribute $attribute
     * @return bool
     * @since 2.0.0
     */
    protected function _isAttributeOmitted($attribute)
    {
        if ($this->_ignoreInvisible && !$attribute->getIsVisible()) {
            return true;
        }
        return false;
    }
}
