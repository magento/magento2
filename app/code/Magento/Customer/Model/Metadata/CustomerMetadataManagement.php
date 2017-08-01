<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model\Metadata;

use Magento\Customer\Api\CustomerMetadataManagementInterface;
use Magento\Customer\Api\Data\AttributeMetadataInterface;

/**
 * Service to manage customer related custom attributes
 * @since 2.0.0
 */
class CustomerMetadataManagement implements CustomerMetadataManagementInterface
{
    /**
     * @var AttributeResolver
     * @since 2.0.0
     */
    protected $attributeResolver;

    /**
     * @param AttributeResolver $attributeResolver
     * @since 2.0.0
     */
    public function __construct(
        AttributeResolver $attributeResolver
    ) {
        $this->attributeResolver = $attributeResolver;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function canBeSearchableInGrid(AttributeMetadataInterface $attribute)
    {
        return $this->attributeResolver->getModelByAttribute(self::ENTITY_TYPE_CUSTOMER, $attribute)
            ->canBeSearchableInGrid();
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function canBeFilterableInGrid(AttributeMetadataInterface $attribute)
    {
        return $this->attributeResolver->getModelByAttribute(self::ENTITY_TYPE_CUSTOMER, $attribute)
            ->canBeFilterableInGrid();
    }
}
