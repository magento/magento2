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

namespace Magento\Customer\Service\V1;

use Magento\Customer\Service\V1\Data\Eav\AttributeMetadataConverter;
use Magento\Customer\Service\V1\Data\Eav\AttributeMetadataDataProvider;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Service\Config\MetadataConfig;
use Magento\Framework\Service\SimpleDataObjectConverter;

/**
 * Service to fetch customer related custom attributes
 */
class CustomerMetadataService implements CustomerMetadataServiceInterface
{
    /**
     * @var array
     */
    private $customerDataObjectMethods;

    /**
     * @var MetadataConfig
     */
    private $metadataConfig;

    /**
     * @var AttributeMetadataConverter
     */
    private $attributeMetadataConverter;

    /**
     * @var AttributeMetadataDataProvider
     */
    private $attributeMetadataDataProvider;

    /**
     * @param MetadataConfig $metadataConfig
     * @param AttributeMetadataConverter $attributeMetadataConverter
     * @param AttributeMetadataDataProvider $attributeMetadataDataProvider
     */
    public function __construct(
        MetadataConfig $metadataConfig,
        AttributeMetadataConverter $attributeMetadataConverter,
        AttributeMetadataDataProvider $attributeMetadataDataProvider
    ) {
        $this->metadataConfig = $metadataConfig;
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
        if ($attribute && ($attributeCode === 'id' || !is_null($attribute->getId()))) {
            $attributeMetadata = $this->attributeMetadataConverter->createMetadataAttribute($attribute);
            return $attributeMetadata;
        } else {
            throw new NoSuchEntityException(
                NoSuchEntityException::MESSAGE_DOUBLE_FIELDS,
                [
                    'fieldName' => 'entityType',
                    'fieldValue' => self::ENTITY_TYPE_CUSTOMER,
                    'field2Name' => 'attributeCode',
                    'field2Value' => $attributeCode,
                ]
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
    public function getCustomAttributesMetadata($dataObjectClassName = self::DATA_OBJECT_CLASS_NAME)
    {
        $customAttributes = [];
        if (!$this->customerDataObjectMethods) {
            $dataObjectMethods = array_flip(get_class_methods($dataObjectClassName));
            $baseClassDataObjectMethods = array_flip(
                get_class_methods('Magento\Framework\Service\Data\AbstractExtensibleObject')
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
        return array_merge($customAttributes, $this->metadataConfig->getCustomAttributesMetadata($dataObjectClassName));
    }
}
