<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * EAV attribute data validator
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Eav\Model\Validator\Attribute;

use Magento\Eav\Model\Attribute;

class Data extends \Magento\Framework\Validator\AbstractValidator
{
    /**
     * @var array
     */
    protected $_attributes = [];

    /**
     * @var array
     */
    protected $_attributesWhiteList = [];

    /**
     * @var array
     */
    protected $_attributesBlackList = [];

    /**
     * @var array
     */
    protected $_data = [];

    /**
     * @var \Magento\Eav\Model\AttributeDataFactory
     */
    protected $_attrDataFactory;

    /**
     * @param \Magento\Eav\Model\AttributeDataFactory $attrDataFactory
     */
    public function __construct(\Magento\Eav\Model\AttributeDataFactory $attrDataFactory)
    {
        $this->_attrDataFactory = $attrDataFactory;
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
    public function setAttributesWhiteList(array $attributesCodes)
    {
        $this->_attributesWhiteList = $attributesCodes;
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
    public function setAttributesBlackList(array $attributesCodes)
    {
        $this->_attributesBlackList = $attributesCodes;
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

        $data = [];
        if ($this->_data) {
            $data = $this->_data;
        } elseif ($entity instanceof \Magento\Framework\DataObject) {
            $data = $entity->getData();
        }

        foreach ($attributes as $attribute) {
            $attributeCode = $attribute->getAttributeCode();
            if (!$attribute->getDataModel() && !$attribute->getFrontendInput()) {
                continue;
            }
            $dataModel = $this->_attrDataFactory->create($attribute, $entity);
            $dataModel->setExtractedData($data);
            if (!isset($data[$attributeCode])) {
                $data[$attributeCode] = null;
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

        if ($this->_attributes) {
            $attributes = $this->_attributes;
        } elseif ($entity instanceof \Magento\Framework\Model\AbstractModel &&
            $entity->getResource() instanceof \Magento\Eav\Model\Entity\AbstractEntity
        ) { // $entity is EAV-model
            /** @var \Magento\Eav\Model\Entity\Type $entityType */
            $entityType = $entity->getEntityType();
            $attributes = $entityType->getAttributeCollection()->getItems();
        }

        $attributesByCode = [];
        $attributesCodes = [];
        foreach ($attributes as $attribute) {
            if (!$attribute->getIsVisible()) {
                continue;
            }
            $attributeCode = $attribute->getAttributeCode();
            $attributesByCode[$attributeCode] = $attribute;
            $attributesCodes[] = $attributeCode;
        }

        $ignoreAttributes = $this->_attributesBlackList;
        if ($this->_attributesWhiteList) {
            $ignoreAttributes = array_merge(
                $ignoreAttributes,
                array_diff($attributesCodes, $this->_attributesWhiteList)
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
}
