<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model\Metadata;

use Magento\Customer\Api\AddressMetadataInterface;
use Magento\Customer\Api\CustomerMetadataInterface;

/**
 * @api
 */
class Form
{
    /**#@+
     * Values for ignoreInvisible parameter in constructor
     */
    const IGNORE_INVISIBLE = true;

    const DONT_IGNORE_INVISIBLE = false;

    /**#@-*/

    /**#@-*/
    protected $_customerMetadataService;

    /**
     * @var AddressMetadataInterface
     */
    protected $_addressMetadataService;

    /**
     * @var ElementFactory
     */
    protected $_elementFactory;

    /**
     * @var string
     */
    protected $_entityType;

    /**
     * @var string
     */
    protected $_formCode;

    /**
     * @var bool
     */
    protected $_ignoreInvisible = true;

    /**
     * @var array
     */
    protected $_filterAttributes = [];

    /**
     * @var bool
     */
    protected $_isAjax = false;

    /**
     * Attribute values
     *
     * @var array
     */
    protected $_attributeValues = [];

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_httpRequest;

    /**
     * @var \Magento\Framework\Module\Dir\Reader
     */
    protected $_modulesReader;

    /**
     * @var \Magento\Framework\Validator\ConfigFactory
     */
    protected $_validatorConfigFactory;

    /**
     * @var \Magento\Framework\Validator
     */
    protected $_validator;

    /**
     * @var \Magento\Customer\Api\Data\AttributeMetadataInterface[]
     */
    protected $_attributes;

    /**
     * @param CustomerMetadataInterface $customerMetadataService
     * @param AddressMetadataInterface $addressMetadataService
     * @param ElementFactory $elementFactory
     * @param \Magento\Framework\App\RequestInterface $httpRequest
     * @param \Magento\Framework\Module\Dir\Reader $modulesReader
     * @param \Magento\Framework\Validator\ConfigFactory $validatorConfigFactory
     * @param string $entityType
     * @param string $formCode
     * @param array $attributeValues
     * @param bool $ignoreInvisible
     * @param array $filterAttributes
     * @param bool $isAjax
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        CustomerMetadataInterface $customerMetadataService,
        AddressMetadataInterface $addressMetadataService,
        ElementFactory $elementFactory,
        \Magento\Framework\App\RequestInterface $httpRequest,
        \Magento\Framework\Module\Dir\Reader $modulesReader,
        \Magento\Framework\Validator\ConfigFactory $validatorConfigFactory,
        $entityType,
        $formCode,
        array $attributeValues = [],
        $ignoreInvisible = self::IGNORE_INVISIBLE,
        $filterAttributes = [],
        $isAjax = false
    ) {
        $this->_customerMetadataService = $customerMetadataService;
        $this->_addressMetadataService = $addressMetadataService;
        $this->_elementFactory = $elementFactory;
        $this->_attributeValues = $attributeValues;
        $this->_entityType = $entityType;
        $this->_formCode = $formCode;
        $this->_ignoreInvisible = $ignoreInvisible;
        $this->_filterAttributes = $filterAttributes;
        $this->_isAjax = $isAjax;
        $this->_httpRequest = $httpRequest;
        $this->_modulesReader = $modulesReader;
        $this->_validatorConfigFactory = $validatorConfigFactory;
    }

    /**
     * Retrieve attributes metadata for the form
     *
     * @return \Magento\Customer\Api\Data\AttributeMetadataInterface[]
     * @throws \LogicException For undefined entity type
     */
    public function getAttributes()
    {
        if (!isset($this->_attributes)) {
            if ($this->_entityType === \Magento\Customer\Api\CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER) {
                $this->_attributes = $this->_customerMetadataService->getAttributes($this->_formCode);
            } elseif ($this->_entityType === \Magento\Customer\Api\AddressMetadataInterface::ENTITY_TYPE_ADDRESS) {
                $this->_attributes = $this->_addressMetadataService->getAttributes($this->_formCode);
            } else {
                throw new \LogicException('Undefined entity type: ' . $this->_entityType);
            }
        }
        return $this->_attributes;
    }

    /**
     * Return attribute instance by code or false
     *
     * @param string $attributeCode
     * @return \Magento\Customer\Api\Data\AttributeMetadataInterface|false
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
     * Retrieve user defined attributes
     *
     * @return \Magento\Customer\Api\Data\AttributeMetadataInterface[]
     */
    public function getUserAttributes()
    {
        $result = [];
        foreach ($this->getAttributes() as $attribute) {
            if ($attribute->isUserDefined()) {
                $result[$attribute->getAttributeCode()] = $attribute;
            }
        }
        return $result;
    }

    /**
     * Retrieve system required attributes
     *
     * @return \Magento\Customer\Api\Data\AttributeMetadataInterface[]
     */
    public function getSystemAttributes()
    {
        $result = [];
        foreach ($this->getAttributes() as $attribute) {
            if (!$attribute->isUserDefined()) {
                $result[$attribute->getAttributeCode()] = $attribute;
            }
        }
        return $result;
    }

    /**
     * Retrieve filtered attributes
     *
     * @return \Magento\Customer\Api\Data\AttributeMetadataInterface[]
     */
    public function getAllowedAttributes()
    {
        $attributes = $this->getAttributes();
        foreach ($attributes as $attributeCode => $attribute) {
            if ($this->_ignoreInvisible && !$attribute->isVisible() || in_array(
                $attribute->getAttributeCode(),
                $this->_filterAttributes
            )
            ) {
                unset($attributes[$attributeCode]);
            }
        }
        return $attributes;
    }

    /**
     * Extract data from request and return associative data array
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @param string $scope the request scope
     * @param boolean $scopeOnly search value only in scope or search value in global too
     * @return array
     */
    public function extractData(\Magento\Framework\App\RequestInterface $request, $scope = null, $scopeOnly = true)
    {
        $data = [];
        foreach ($this->getAllowedAttributes() as $attribute) {
            $dataModel = $this->_getAttributeDataModel($attribute);
            $dataModel->setRequestScope($scope);
            $dataModel->setRequestScopeOnly($scopeOnly);
            $data[$attribute->getAttributeCode()] = $dataModel->extractValue($request);
        }
        return $data;
    }

    /**
     * Compact data array to form attribute values
     *
     * @param array $data
     * @return array attribute values
     */
    public function compactData(array $data)
    {
        foreach ($this->getAllowedAttributes() as $attribute) {
            $dataModel = $this->_getAttributeDataModel($attribute);
            $dataModel->setExtractedData($data);
            if (!isset($data[$attribute->getAttributeCode()])) {
                $data[$attribute->getAttributeCode()] = false;
            }
            $attributeCode = $attribute->getAttributeCode();
            $this->_attributeValues[$attributeCode] = $dataModel->compactValue($data[$attributeCode]);
        }

        return $this->_attributeValues;
    }

    /**
     * Restore data array from SESSION to attribute values
     *
     * @param array $data
     * @return array
     */
    public function restoreData(array $data)
    {
        foreach ($this->getAllowedAttributes() as $attribute) {
            $dataModel = $this->_getAttributeDataModel($attribute);
            $dataModel->setExtractedData($data);
            if (!isset($data[$attribute->getAttributeCode()])) {
                $data[$attribute->getAttributeCode()] = false;
            }
            $attributeCode = $attribute->getAttributeCode();
            $this->_attributeValues[$attributeCode] = $dataModel->restoreValue($data[$attributeCode]);
        }
        return $this->_attributeValues;
    }

    /**
     * Return attribute data model by attribute
     *
     * @param \Magento\Customer\Api\Data\AttributeMetadataInterface $attribute
     * @return \Magento\Eav\Model\Attribute\Data\AbstractData
     */
    protected function _getAttributeDataModel($attribute)
    {
        $dataModel = $this->_elementFactory->create(
            $attribute,
            isset(
                $this->_attributeValues[$attribute->getAttributeCode()]
            ) ? $this->_attributeValues[$attribute->getAttributeCode()] : null,
            $this->_entityType,
            $this->_isAjax
        );
        return $dataModel;
    }

    /**
     * Prepare request with data and returns it
     *
     * @param array $data
     * @return \Magento\Framework\App\RequestInterface
     */
    public function prepareRequest(array $data)
    {
        $request = clone $this->_httpRequest;
        $request->clearParams();
        $request->setParams($data);
        return $request;
    }

    /**
     * Get validator
     *
     * @param array $data
     * @return \Magento\Framework\Validator
     */
    protected function _getValidator(array $data)
    {
        if ($this->_validator !== null) {
            return $this->_validator;
        }

        $configFiles = $this->_modulesReader->getConfigurationFiles('validation.xml');
        $validatorFactory = $this->_validatorConfigFactory->create(['configFiles' => $configFiles]);
        $builder = $validatorFactory->createValidatorBuilder('customer', 'form');

        $builder->addConfiguration(
            'metadata_data_validator',
            ['method' => 'setAttributes', 'arguments' => [$this->getAllowedAttributes()]]
        );
        $builder->addConfiguration(
            'metadata_data_validator',
            ['method' => 'setData', 'arguments' => [$data]]
        );
        $builder->addConfiguration(
            'metadata_data_validator',
            ['method' => 'setEntityType', 'arguments' => [$this->_entityType]]
        );
        $this->_validator = $builder->createValidator();

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
        if (!$validator->isValid(false)) {
            $messages = [];
            foreach ($validator->getMessages() as $errorMessages) {
                $messages = array_merge($messages, (array)$errorMessages);
            }
            return $messages;
        }
        return true;
    }

    /**
     * Return array of formatted allowed attributes values.
     *
     * @param string $format
     * @return array
     */
    public function outputData($format = \Magento\Eav\Model\AttributeDataFactory::OUTPUT_FORMAT_TEXT)
    {
        $result = [];
        foreach ($this->getAllowedAttributes() as $attribute) {
            $dataModel = $this->_getAttributeDataModel($attribute);
            $result[$attribute->getAttributeCode()] = $dataModel->outputValue($format);
        }
        return $result;
    }

    /**
     * Set whether invisible attributes should be ignored.
     *
     * @param bool $ignoreInvisible
     * @return void
     */
    public function setInvisibleIgnored($ignoreInvisible)
    {
        $this->_ignoreInvisible = $ignoreInvisible;
    }
}
