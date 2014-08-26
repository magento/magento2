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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Customer\Model\Metadata;

use Magento\Customer\Service\V1\AddressMetadataServiceInterface;
use Magento\Customer\Service\V1\CustomerMetadataServiceInterface;

class Form
{
    /**#@+
     * Values for ignoreInvisible parameter in constructor
     */
    const IGNORE_INVISIBLE = true;

    const DONT_IGNORE_INVISIBLE = false;

    /**#@-*/

    /**
     * @var CustomerMetadataServiceInterface
     */
    protected $_customerMetadataService;

    /**
     * @var AddressMetadataServiceInterface
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
    protected $_filterAttributes = array();

    /**
     * @var bool
     */
    protected $_isAjax = false;

    /**
     * Attribute values
     *
     * @var array
     */
    protected $_attributeValues = array();

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
     * @var \Magento\Customer\Service\V1\Data\Eav\AttributeMetadata[]
     */
    protected $_attributes;

    /**
     * @param CustomerMetadataServiceInterface $customerMetadataService
     * @param AddressMetadataServiceInterface $addressMetadataService
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
        CustomerMetadataServiceInterface $customerMetadataService,
        AddressMetadataServiceInterface $addressMetadataService,
        ElementFactory $elementFactory,
        \Magento\Framework\App\RequestInterface $httpRequest,
        \Magento\Framework\Module\Dir\Reader $modulesReader,
        \Magento\Framework\Validator\ConfigFactory $validatorConfigFactory,
        $entityType,
        $formCode,
        array $attributeValues = array(),
        $ignoreInvisible = self::IGNORE_INVISIBLE,
        $filterAttributes = array(),
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
     * @return \Magento\Customer\Service\V1\Data\Eav\AttributeMetadata[]
     * @throws \LogicException For undefined entity type
     */
    public function getAttributes()
    {
        if (!isset($this->_attributes)) {
            if ($this->_entityType === CustomerMetadataServiceInterface::ENTITY_TYPE_CUSTOMER) {
                $this->_attributes = $this->_customerMetadataService->getAttributes($this->_formCode);
            } else if ($this->_entityType === AddressMetadataServiceInterface::ENTITY_TYPE_ADDRESS) {
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
     * @return \Magento\Customer\Service\V1\Data\Eav\AttributeMetadata|false
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
     * @return \Magento\Customer\Service\V1\Data\Eav\AttributeMetadata[]
     */
    public function getUserAttributes()
    {
        $result = array();
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
     * @return \Magento\Customer\Service\V1\Data\Eav\AttributeMetadata[]
     */
    public function getSystemAttributes()
    {
        $result = array();
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
     * @return \Magento\Customer\Service\V1\Data\Eav\AttributeMetadata[]
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
        $data = array();
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
     * @param \Magento\Customer\Service\V1\Data\Eav\AttributeMetadata $attribute
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
        $request->setParamSources();
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
        $validatorFactory = $this->_validatorConfigFactory->create(array('configFiles' => $configFiles));
        $builder = $validatorFactory->createValidatorBuilder('customer', 'form');

        $builder->addConfiguration(
            'metadata_data_validator',
            array('method' => 'setAttributes', 'arguments' => array($this->getAllowedAttributes()))
        );
        $builder->addConfiguration(
            'metadata_data_validator',
            array('method' => 'setData', 'arguments' => array($data))
        );
        $builder->addConfiguration(
            'metadata_data_validator',
            array('method' => 'setEntityType', 'arguments' => array($this->_entityType))
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
            $messages = array();
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
        $result = array();
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
