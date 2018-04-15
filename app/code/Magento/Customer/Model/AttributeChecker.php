<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model;

use Magento\Customer\Api\AddressMetadataInterface;
use Magento\Customer\Api\AddressMetadataManagementInterface;
use Magento\Customer\Model\Metadata\AttributeResolver;

/**
 * Customer attribute checker.
 */
class AttributeChecker
{
    /**
     * @var AddressMetadataInterface
     */
    private $addressMetadata;

    /**
     * @var AttributeResolver
     */
    private $attributeResolver;

    /**
     * @param AddressMetadataInterface $addressMetadata
     * @param AttributeResolver $attributeResolver
     */
    public function __construct(
        AddressMetadataInterface $addressMetadata,
        AttributeResolver $attributeResolver
    ) {
        $this->addressMetadata = $addressMetadata;
        $this->attributeResolver = $attributeResolver;
    }

    /**
     * Checks whether it is allowed to show an attribute on the form
     *
     * This check relies on the attribute's property 'getUsedInForms' which contains a list of forms
     * where allowed to render specified attribute.
     *
     * @param string $attributeCode
     * @param string $formName
     * @return bool
     */
    public function isAttributeAllowedOnForm($attributeCode, $formName)
    {
        $isAllowed = false;
        $attributeMetadata = $this->addressMetadata->getAttributeMetadata($attributeCode);
        if ($attributeMetadata) {
            /** @var Attribute $attribute */
            $attribute = $this->attributeResolver->getModelByAttribute(
                AddressMetadataManagementInterface::ENTITY_TYPE_ADDRESS,
                $attributeMetadata
            );
            $usedInForms = $attribute->getUsedInForms();
            $isAllowed = in_array($formName, $usedInForms, true);
        }

        return $isAllowed;
    }
}
