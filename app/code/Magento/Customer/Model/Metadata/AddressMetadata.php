<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Model\Metadata;

use Magento\Customer\Api\AddressMetadataInterface;
use Magento\Customer\Model\AttributeMetadataConverter;
use Magento\Customer\Model\AttributeMetadataDataProvider;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\Api\SimpleDataObjectConverter;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Service to fetch customer address related custom attributes
 * @since 2.0.0
 */
class AddressMetadata implements AddressMetadataInterface
{
    /**
     * @var array
     * @since 2.0.0
     */
    private $addressDataObjectMethods;

    /**
     * @var AttributeMetadataConverter
     * @since 2.0.0
     */
    private $attributeMetadataConverter;

    /**
     * @var AttributeMetadataDataProvider
     * @since 2.0.0
     */
    private $attributeMetadataDataProvider;

    /**
     * @param AttributeMetadataConverter $attributeMetadataConverter
     * @param AttributeMetadataDataProvider $attributeMetadataDataProvider
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function getAttributes($formCode)
    {
        $attributes = [];
        $attributesFormCollection = $this->attributeMetadataDataProvider->loadAttributesCollection(
            AddressMetadataInterface::ENTITY_TYPE_ADDRESS,
            $formCode
        );
        foreach ($attributesFormCollection as $attribute) {
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
     * @since 2.0.0
     */
    public function getAttributeMetadata($attributeCode)
    {
        /** @var AbstractAttribute $attribute */
        $attribute = $this->attributeMetadataDataProvider
            ->getAttribute(AddressMetadataInterface::ENTITY_TYPE_ADDRESS, $attributeCode);
        if ($attribute && ($attributeCode === 'id' || $attribute->getId() !== null)) {
            $attributeMetadata = $this->attributeMetadataConverter->createMetadataAttribute($attribute);
            return $attributeMetadata;
        } else {
            throw new NoSuchEntityException(
                __(
                    'No such entity with %fieldName = %fieldValue, %field2Name = %field2Value',
                    [
                        'fieldName' => 'entityType',
                        'fieldValue' => AddressMetadataInterface::ENTITY_TYPE_ADDRESS,
                        'field2Name' => 'attributeCode',
                        'field2Value' => $attributeCode
                    ]
                )
            );
        }
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getAllAttributesMetadata()
    {
        /** @var AbstractAttribute[] $attribute */
        $attributeCodes = $this->attributeMetadataDataProvider->getAllAttributeCodes(
            AddressMetadataInterface::ENTITY_TYPE_ADDRESS,
            AddressMetadataInterface::ATTRIBUTE_SET_ID_ADDRESS
        );

        $allAttributesMetadata = [];

        foreach ($attributeCodes as $attributeCode) {
            try {
                $allAttributesMetadata[] = $this->getAttributeMetadata($attributeCode);
            } catch (NoSuchEntityException $e) {
                //If no such entity, skip
            }
        }

        return $allAttributesMetadata;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getCustomAttributesMetadata($dataObjectClassName = AddressMetadataInterface::DATA_INTERFACE_NAME)
    {
        if (!$this->addressDataObjectMethods) {
            $dataObjectMethods = array_flip(get_class_methods($dataObjectClassName));
            $baseClassDataObjectMethods = array_flip(
                get_class_methods(\Magento\Framework\Api\AbstractExtensibleObject::class)
            );
            $this->addressDataObjectMethods = array_diff_key($dataObjectMethods, $baseClassDataObjectMethods);
        }
        $customAttributes = [];
        foreach ($this->getAllAttributesMetadata() as $attributeMetadata) {
            $attributeCode = $attributeMetadata->getAttributeCode();
            $camelCaseKey = SimpleDataObjectConverter::snakeCaseToUpperCamelCase($attributeCode);
            $isDataObjectMethod = isset($this->addressDataObjectMethods['get' . $camelCaseKey])
                || isset($this->addressDataObjectMethods['is' . $camelCaseKey]);

            if (!$isDataObjectMethod && !$attributeMetadata->isSystem()) {
                $customAttributes[] = $attributeMetadata;
            }
        }
        return $customAttributes;
    }
}
