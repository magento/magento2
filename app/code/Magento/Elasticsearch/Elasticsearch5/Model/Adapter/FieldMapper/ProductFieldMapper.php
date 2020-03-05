<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Elasticsearch\Elasticsearch5\Model\Adapter\FieldMapper;

use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\AttributeProvider;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProviderInterface;
use Magento\Elasticsearch\Model\Adapter\FieldMapperInterface;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\FieldName\ResolverInterface;

/**
 * Class ProductFieldMapper provides field name by attribute code and retrieve all attribute types
 */
class ProductFieldMapper implements FieldMapperInterface
{
    /**
     * @var AttributeProvider
     */
    private $attributeAdapterProvider;

    /**
     * @var ResolverInterface
     */
    private $fieldNameResolver;

    /**
     * @var FieldProviderInterface
     */
    private $fieldProvider;

    /**
     * @param ResolverInterface $fieldNameResolver
     * @param AttributeProvider $attributeAdapterProvider
     * @param FieldProviderInterface $fieldProvider
     */
    public function __construct(
        ResolverInterface $fieldNameResolver,
        AttributeProvider $attributeAdapterProvider,
        FieldProviderInterface $fieldProvider
    ) {
        $this->fieldNameResolver = $fieldNameResolver;
        $this->attributeAdapterProvider = $attributeAdapterProvider;
        $this->fieldProvider = $fieldProvider;
    }

    /**
     * Get field name.
     *
     * @param string $attributeCode
     * @param array $context
     * @return string
     */
    public function getFieldName($attributeCode, $context = [])
    {
        $attributeAdapter = $this->attributeAdapterProvider->getByAttributeCode($attributeCode);
        return $this->fieldNameResolver->getFieldName($attributeAdapter, $context);
    }

    /**
     * Get all attributes types.
     *
     * @param array $context
     * @return array
     */
    public function getAllAttributesTypes($context = [])
    {
        return $this->fieldProvider->getFields($context);
    }
}
