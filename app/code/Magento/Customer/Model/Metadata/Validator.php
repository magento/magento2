<?php
/**
 * Attribute data validator
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model\Metadata;

class Validator extends \Magento\Eav\Model\Validator\Attribute\Data
{
    /**
     * @var string
     */
    protected $_entityType;

    /**
     * @var array
     */
    protected $_entityData;

    /**
     * @param ElementFactory $attrDataFactory
     */
    public function __construct(ElementFactory $attrDataFactory)
    {
        $this->_attrDataFactory = $attrDataFactory;
    }

    /**
     * Validate EAV model attributes with data models
     *
     * @param \Magento\Framework\Object|array $entityData Data set from the Model attributes
     * @return bool
     */
    public function isValid($entityData)
    {
        if ($entityData instanceof \Magento\Framework\Object) {
            $this->_entityData = $entityData->getData();
        } else {
            $this->_entityData = $entityData;
        }
        return $this->validateData($this->_data, $this->_attributes, $this->_entityType);
    }

    /**
     * @param array                                                    $data
     * @param \Magento\Customer\Api\Data\AttributeMetadataInterface[] $attributes
     * @param string                                                   $entityType
     * @return bool
     */
    public function validateData(array $data, array $attributes, $entityType)
    {
        foreach ($attributes as $attribute) {
            $attributeCode = $attribute->getAttributeCode();
            if (!$attribute->getDataModel() && !$attribute->getFrontendInput()) {
                continue;
            }
            if (!isset($data[$attributeCode])) {
                $data[$attributeCode] = null;
            }
            $dataModel = $this->_attrDataFactory->create($attribute, $data[$attributeCode], $entityType);
            $dataModel->setExtractedData($data);
            $value = empty($data[$attributeCode]) && isset(
                $this->_entityData[$attributeCode]
            ) ? $this->_entityData[$attributeCode] : $data[$attributeCode];
            $result = $dataModel->validateValue($value);
            if (true !== $result) {
                $this->_addErrorMessages($attributeCode, (array)$result);
            }
        }
        return count($this->_messages) == 0;
    }

    /**
     * Set type of the entity
     *
     * @param string $entityType
     * @return void
     */
    public function setEntityType($entityType)
    {
        $this->_entityType = $entityType;
    }
}
