<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Eav\Model\Validator\Attribute;

use Magento\Eav\Model\Attribute;
use Magento\Eav\Model\AttributeDataFactory;
use Magento\Eav\Model\Config;
use Magento\Framework\DataObject;

/**
 * EAV attribute data validator
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Data extends \Magento\Framework\Validator\AbstractValidator
{
    /**
     * @var array
     */
    protected $_attributes = [];

    /**
     * @var array
     */
    protected $allowedAttributesList = [];

    /**
     * @var array
     */
    protected $deniedAttributesList = [];

    /**
     * @var array
     */
    protected $_data = [];

    /**
     * @var AttributeDataFactory
     */
    protected $_attrDataFactory;

    /**
     * @var array
     */
    private $ignoredAttributesByTypesList;

    /**
     * @var \Magento\Eav\Model\Config
     */
    private $eavConfig;

    /**
     * @param AttributeDataFactory $attrDataFactory
     * @param Config|null $eavConfig
     * @param array $ignoredAttributesByTypesList
     */
    public function __construct(
        AttributeDataFactory $attrDataFactory,
        ?Config $eavConfig = null,
        array $ignoredAttributesByTypesList = []
    ) {
        $this->eavConfig = $eavConfig ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(Config::class);
        $this->_attrDataFactory = $attrDataFactory;
        $this->ignoredAttributesByTypesList = $ignoredAttributesByTypesList;
    }

    /**
     * @inheritDoc
     */
    public function _resetState(): void
    {
        parent::_resetState();
        $this->_attributes = [];
        $this->allowedAttributesList = [];
        $this->deniedAttributesList = [];
        $this->_data = [];
    }

    /**
     * Set list of attributes for validation in isValid method.
     *
     * @param Attribute[] $attributes
     * @return $this
     */
    public function setAttributes(array $attributes)
    {
        $this->_attributes = $attributes;
        return $this;
    }

    /**
     * Set codes of attributes that should be filtered in validation process.
     *
     * All attributes not in this list 't be involved in validation.
     *
     * @param array $attributesCodes
     * @return $this
     */
    public function setAllowedAttributesList(array $attributesCodes)
    {
        $this->allowedAttributesList = $attributesCodes;
        return $this;
    }

    /**
     * Set codes of attributes that should be excluded in validation process.
     *
     * All attributes in this list won't be involved in validation.
     *
     * @param array $attributesCodes
     * @return $this
     */
    public function setDeniedAttributesList(array $attributesCodes)
    {
        $this->deniedAttributesList = $attributesCodes;
        return $this;
    }

    /**
     * Set data for validation in isValid method.
     *
     * @param array $data
     * @return $this
     */
    public function setData(array $data)
    {
        $this->_data = $data;
        return $this;
    }

    /**
     * Validate EAV model attributes with data models
     *
     * @param \Magento\Framework\Model\AbstractModel $entity
     * @return bool
     */
    public function isValid($entity)
    {
        /** @var $attributes Attribute[] */
        $attributes = $this->_getAttributes($entity);

        $data = $this->retrieveData($entity);

        foreach ($attributes as $attribute) {
            $attributeCode = $attribute->getAttributeCode();
            if (!$attribute->getDataModel() && !$attribute->getFrontendInput()) {
                continue;
            }
            if (!isset($data[$attributeCode]) && !$attribute->getIsVisible()) {
                continue;
            }

            $dataModel = $this->_attrDataFactory->create($attribute, $entity);
            $dataModel->setExtractedData($data);
            if (!isset($data[$attributeCode])) {
                $data[$attributeCode] = '';
            }
            $result = $dataModel->validateValue($data[$attributeCode]);
            if (true !== $result) {
                $this->_addErrorMessages($attributeCode, (array)$result);
            }
        }
        return count($this->_messages) == 0;
    }

    /**
     * Get attributes involved in validation.
     *
     * This method return specified $_attributes if they defined by setAttributes method, otherwise if $entity
     * is EAV-model it returns it's all available attributes, otherwise it return empty array.
     *
     * @param \Magento\Framework\Model\AbstractModel $entity
     * @return array
     */
    protected function _getAttributes($entity)
    {
        /** @var \Magento\Eav\Model\Attribute[] $attributes */
        $attributes = [];
        $ignoreAttributes = $this->deniedAttributesList;

        if ($this->_attributes) {
            $attributes = $this->_attributes;
        } elseif ($entity instanceof \Magento\Framework\Model\AbstractModel &&
            $entity->getResource() instanceof \Magento\Eav\Model\Entity\AbstractEntity
        ) { // $entity is EAV-model
            $type = $entity->getEntityType()->getEntityTypeCode();
            /** @var \Magento\Eav\Model\Entity\Type $entityType */
            $entityType = $this->eavConfig->getEntityType($type);
            $attributes = $entityType->getAttributeCollection()->getItems();

            $ignoredTypeAttributes = $this->ignoredAttributesByTypesList[$entityType->getEntityTypeCode()] ?? [];
            if ($ignoredTypeAttributes) {
                $ignoreAttributes = array_merge($ignoreAttributes, $ignoredTypeAttributes);
            }
        }

        $attributesByCode = [];
        $attributesCodes = [];
        foreach ($attributes as $attribute) {
            $attributeCode = $attribute->getAttributeCode();
            $attributesByCode[$attributeCode] = $attribute;
            $attributesCodes[] = $attributeCode;
        }

        if ($this->allowedAttributesList) {
            $ignoreAttributes = array_merge(
                $ignoreAttributes,
                array_diff($attributesCodes, $this->allowedAttributesList)
            );
        }
        foreach ($ignoreAttributes as $attributeCode) {
            unset($attributesByCode[$attributeCode]);
        }

        return $attributesByCode;
    }

    /**
     * Add error messages
     *
     * @param string $code
     * @param array $messages
     * @return void
     */
    protected function _addErrorMessages($code, array $messages)
    {
        if (!array_key_exists($code, $this->_messages)) {
            $this->_messages[$code] = $messages;
        } else {
            $this->_messages[$code] = array_merge($this->_messages[$code], $messages);
        }
    }

    /**
     * Retrieve entity data
     *
     * @param \Magento\Framework\Model\AbstractModel $entity
     * @return array
     */
    private function retrieveData($entity): array
    {
        $data = [];
        if ($this->_data) {
            $data = $this->_data;
        } elseif ($entity instanceof DataObject) {
            $data = $entity->getData();
        }

        return $data;
    }
}
