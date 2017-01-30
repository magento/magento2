<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Model\Metadata;

use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Model\AttributeMetadataConverter;
use Magento\Customer\Model\AttributeMetadataDataProvider;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\Api\SimpleDataObjectConverter;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Service to fetch customer related custom attributes
 */
class CustomerMetadata implements CustomerMetadataInterface
{
    /**
     * @var array
     */
    private $customerDataObjectMethods;

    /**
     * @var AttributeMetadataConverter
     */
    private $attributeMetadataConverter;

    /**
     * @var AttributeMetadataDataProvider
     */
    private $attributeMetadataDataProvider;

    /**
     * @param AttributeMetadataConverter $attributeMetadataConverter
     * @param AttributeMetadataDataProvider $attributeMetadataDataProvider
     */
    public function __construct(
        AttributeMetadataConverter $attributeMetadataConverter,
        AttributeMetadataDataProvider $attributeMetadataDataProvider
    ) {
        $this->attributeMetadataConverter = $attributeMetadataConverter;
        $this->attributeMetadataDataProvider = $attributeMetadataDataProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributes($formCode)
    {
        $attributes = [];
        $attributesFormCollection = $this->attributeMetadataDataProvider->loadAttributesCollection(
            self::ENTITY_TYPE_CUSTOMER,
            $formCode
        );
        foreach ($attributesFormCollection as $attribute) {
            /** @var $attribute \Magento\Customer\Model\Attribute */
            $attributes[$attribute->getAttributeCode()] = $this->attributeMetadataConverter
                ->createMetadataAttribute($attribute);
        }
        if (empty($attributes)) {
            throw NoSuchEntityException::singleField('formCode', $formCode);
        }
        return $attributes;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributeMetadata($attributeCode)
    {
        /** @var AbstractAttribute $attribute */
        $attribute = $this->attributeMetadataDataProvider->getAttribute(self::ENTITY_TYPE_CUSTOMER, $attributeCode);
        if ($attribute && ($attributeCode === 'id' || $attribute->getId() !== null)) {
            $attributeMetadata = $this->attributeMetadataConverter->createMetadataAttribute($attribute);
            return $attributeMetadata;
        } else {
            throw new NoSuchEntityException(
                __(
                    'No such entity with %fieldName = %fieldValue, %field2Name = %field2Value',
                    [
                        'fieldName' => 'entityType',
                        'fieldValue' => self::ENTITY_TYPE_CUSTOMER,
                        'field2Name' => 'attributeCode',
                        'field2Value' => $attributeCode
                    ]
                )
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getAllAttributesMetadata()
    {
        /** @var AbstractAttribute[] $attribute */
        $attributeCodes = $this->attributeMetadataDataProvider->getAllAttributeCodes(
            self::ENTITY_TYPE_CUSTOMER,
            self::ATTRIBUTE_SET_ID_CUSTOMER
        );

        $attributesMetadata = [];

        foreach ($attributeCodes as $attributeCode) {
            try {
                $attributesMetadata[] = $this->getAttributeMetadata($attributeCode);
            } catch (NoSuchEntityException $e) {
                //If no such entity, skip
            }
        }

        return $attributesMetadata;
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomAttributesMetadata($dataObjectClassName = self::DATA_INTERFACE_NAME)
    {
        $customAttributes = [];
        if (!$this->customerDataObjectMethods) {
            $dataObjectMethods = array_flip(get_class_methods($dataObjectClassName));
            $baseClassDataObjectMethods = array_flip(
                get_class_methods('Magento\Framework\Api\AbstractExtensibleObject')
            );
            $this->customerDataObjectMethods = array_diff_key($dataObjectMethods, $baseClassDataObjectMethods);
        }
        foreach ($this->getAllAttributesMetadata() as $attributeMetadata) {
            $attributeCode = $attributeMetadata->getAttributeCode();
            $camelCaseKey = SimpleDataObjectConverter::snakeCaseToUpperCamelCase($attributeCode);
            $isDataObjectMethod = isset($this->customerDataObjectMethods['get' . $camelCaseKey])
                || isset($this->customerDataObjectMethods['is' . $camelCaseKey]);

            /** Even though disable_auto_group_change is system attribute, it should be available to the clients */
            if (!$isDataObjectMethod
                && (!$attributeMetadata->isSystem() || $attributeCode == 'disable_auto_group_change')
            ) {
                $customAttributes[] = $attributeMetadata;
            }
        }
        return $customAttributes;
    }
}
