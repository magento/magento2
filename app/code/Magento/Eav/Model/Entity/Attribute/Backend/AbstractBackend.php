<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Model\Entity\Attribute\Backend;

use Magento\Framework\Exception\LocalizedException;

/**
 * Entity/Attribute/Model - attribute backend abstract
 *
 * @api
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 * @since 2.0.0
 */
abstract class AbstractBackend implements \Magento\Eav\Model\Entity\Attribute\Backend\BackendInterface
{
    /**
     * Reference to the attribute instance
     *
     * @var \Magento\Eav\Model\Entity\Attribute\AbstractAttribute
     * @since 2.0.0
     */
    protected $_attribute;

    /**
     * PK value_id for loaded entity (for faster updates)
     *
     * @var integer
     * @since 2.0.0
     */
    protected $_valueId;

    /**
     * PK value_ids for each loaded entity
     *
     * @var array
     * @since 2.0.0
     */
    protected $_valueIds = [];

    /**
     * Table name for this attribute
     *
     * @var string
     * @since 2.0.0
     */
    protected $_table;

    /**
     * Name of the entity_id field for the value table of this attribute
     *
     * @var string
     * @since 2.0.0
     */
    protected $_entityIdField;

    /**
     * Default value for the attribute
     *
     * @var mixed
     * @since 2.0.0
     */
    protected $_defaultValue = null;

    /**
     * Set attribute instance
     *
     * @param \Magento\Eav\Model\Entity\Attribute\AbstractAttribute $attribute
     * @return $this
     * @codeCoverageIgnore
     * @since 2.0.0
     */
    public function setAttribute($attribute)
    {
        $this->_attribute = $attribute;
        return $this;
    }

    /**
     * Get attribute instance
     *
     * @return \Magento\Eav\Model\Entity\Attribute\AbstractAttribute
     * @codeCoverageIgnore
     * @since 2.0.0
     */
    public function getAttribute()
    {
        return $this->_attribute;
    }

    /**
     * Get backend type of the attribute
     *
     * @return string
     * @codeCoverageIgnore
     * @since 2.0.0
     */
    public function getType()
    {
        return $this->getAttribute()->getBackendType();
    }

    /**
     * Check whether the attribute is a real field in the entity table
     *
     * @return bool
     * @codeCoverageIgnore
     * @since 2.0.0
     */
    public function isStatic()
    {
        return $this->getAttribute()->isStatic();
    }

    /**
     * Get table name for the values of the attribute
     *
     * @return string
     * @since 2.0.0
     */
    public function getTable()
    {
        if (empty($this->_table)) {
            if ($this->isStatic()) {
                $this->_table = $this->getAttribute()->getEntityType()->getValueTablePrefix();
            } elseif ($this->getAttribute()->getBackendTable()) {
                $this->_table = $this->getAttribute()->getBackendTable();
            } else {
                $entity = $this->getAttribute()->getEntity();
                $tableName = sprintf('%s_%s', $entity->getValueTablePrefix(), $this->getType());
                $this->_table = $tableName;
            }
        }

        return $this->_table;
    }

    /**
     * Get entity_id field in the attribute values tables
     *
     * @return string
     * @since 2.0.0
     */
    public function getEntityIdField()
    {
        if (empty($this->_entityIdField)) {
            if ($this->getAttribute()->getEntityIdField()) {
                $this->_entityIdField = $this->getAttribute()->getEntityIdField();
            } else {
                $this->_entityIdField = $this->getAttribute()->getEntityType()->getValueEntityIdField();
            }
        }

        return $this->_entityIdField;
    }

    /**
     * Set value id
     *
     * @param int $valueId
     * @return $this
     * @codeCoverageIgnore
     * @since 2.0.0
     */
    public function setValueId($valueId)
    {
        $this->_valueId = $valueId;
        return $this;
    }

    /**
     * Set entity value id
     *
     * @param \Magento\Framework\DataObject $entity
     * @param int $valueId
     * @return $this
     * @since 2.0.0
     */
    public function setEntityValueId($entity, $valueId)
    {
        if (!$entity || !$entity->getId()) {
            return $this->setValueId($valueId);
        }

        $this->_valueIds[$entity->getId()] = $valueId;
        return $this;
    }

    /**
     * Retrieve value id
     *
     * @return int
     * @codeCoverageIgnore
     * @since 2.0.0
     */
    public function getValueId()
    {
        return $this->_valueId;
    }

    /**
     * Get entity value id
     *
     * @param \Magento\Framework\DataObject $entity
     * @return int
     * @since 2.0.0
     */
    public function getEntityValueId($entity)
    {
        if (!$entity || !$entity->getId() || !array_key_exists($entity->getId(), $this->_valueIds)) {
            return $this->getValueId();
        }

        return $this->_valueIds[$entity->getId()];
    }

    /**
     * Retrieve default value
     *
     * @return mixed
     * @since 2.0.0
     */
    public function getDefaultValue()
    {
        if ($this->_defaultValue === null) {
            if ($this->getAttribute()->getDefaultValue()) {
                $this->_defaultValue = $this->getAttribute()->getDefaultValue();
            } else {
                $this->_defaultValue = "";
            }
        }

        return $this->_defaultValue;
    }

    /**
     * Validate object
     *
     * @param \Magento\Framework\DataObject $object
     * @return bool
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @since 2.0.0
     */
    public function validate($object)
    {
        $attribute = $this->getAttribute();
        $attrCode = $attribute->getAttributeCode();
        $value = $object->getData($attrCode);

        if ($attribute->getIsVisible()
            && $attribute->getIsRequired()
            && $attribute->isValueEmpty($value)
            && $attribute->isValueEmpty($attribute->getDefaultValue())
        ) {
            throw new LocalizedException(__('The value of attribute "%1" must be set', $attrCode));
        }

        if ($attribute->getIsUnique()
            && !$attribute->getIsRequired()
            && ($value == '' || $attribute->isValueEmpty($value))
        ) {
            return true;
        }

        if ($attribute->getIsUnique()) {
            if (!$attribute->getEntity()->checkAttributeUniqueValue($attribute, $object)) {
                $label = $attribute->getFrontend()->getLabel();
                throw new LocalizedException(__('The value of attribute "%1" must be unique', $label));
            }
        }

        return true;
    }

    /**
     * After load method
     *
     * @param \Magento\Framework\DataObject $object
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @codeCoverageIgnore
     * @since 2.0.0
     */
    public function afterLoad($object)
    {
        return $this;
    }

    /**
     * Before save method
     *
     * @param \Magento\Framework\DataObject $object
     * @return $this
     * @since 2.0.0
     */
    public function beforeSave($object)
    {
        $attrCode = $this->getAttribute()->getAttributeCode();
        if (!$object->hasData($attrCode) && $this->getDefaultValue()) {
            $object->setData($attrCode, $this->getDefaultValue());
        }

        return $this;
    }

    /**
     * After save method
     *
     * @param \Magento\Framework\DataObject $object
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @codeCoverageIgnore
     * @since 2.0.0
     */
    public function afterSave($object)
    {
        return $this;
    }

    /**
     * Before delete method
     *
     * @param \Magento\Framework\DataObject $object
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @codeCoverageIgnore
     * @since 2.0.0
     */
    public function beforeDelete($object)
    {
        return $this;
    }

    /**
     * After delete method
     *
     * @param \Magento\Framework\DataObject $object
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @codeCoverageIgnore
     * @since 2.0.0
     */
    public function afterDelete($object)
    {
        return $this;
    }

    /**
     * Retrieve data for update attribute
     *
     * @param \Magento\Framework\DataObject $object
     * @return array
     * @since 2.0.0
     */
    public function getAffectedFields($object)
    {
        $data = [];
        $data[$this->getTable()][] = [
            'attribute_id' => $this->getAttribute()->getAttributeId(),
            'value_id' => $this->getEntityValueId($object),
        ];
        return $data;
    }

    /**
     * By default attribute value is considered scalar that can be stored in a generic way
     *
     * {@inheritdoc}
     * @codeCoverageIgnore
     * @since 2.0.0
     */
    public function isScalar()
    {
        return true;
    }
}
