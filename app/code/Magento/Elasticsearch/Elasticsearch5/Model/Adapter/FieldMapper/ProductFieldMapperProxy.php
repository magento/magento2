<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Elasticsearch5\Model\Adapter\FieldMapper;

use Magento\AdvancedSearch\Model\Client\ClientResolver;
use Magento\Elasticsearch\Model\Adapter\FieldMapperInterface;

/**
 * Proxy for product fields mappers
 */
class ProductFieldMapperProxy implements FieldMapperInterface
{
    /**
     * @var ClientResolver
     */
    private $clientResolver;

    /**
     * @var FieldMapperInterface[]
     */
    private $productFieldMappers;

    /**
     * CategoryFieldsProviderProxy constructor.
     * @param ClientResolver $clientResolver
     * @param FieldMapperInterface[] $productFieldMappers
     */
    public function __construct(
        ClientResolver $clientResolver,
        array $productFieldMappers
    ) {
        $this->clientResolver = $clientResolver;
        $this->productFieldMappers = $productFieldMappers;
    }

    /**
     * @return FieldMapperInterface
     */
    private function getProductFieldMapper()
    {
        return $this->productFieldMappers[$this->clientResolver->getCurrentEngine()];
    }

    /**
     * Get field name
     *
     * @param string $attributeCode
     * @param array $context
     * @return string
     * @since 100.1.0
     */
    public function getFieldName($attributeCode, $context = [])
    {
        return $this->getProductFieldMapper()->getFieldName($attributeCode, $context);
    }

    /**
     * Get all entity attribute types
     *
     * @param array $context
     * @return array
     * @since 100.1.0
     */
    public function getAllAttributesTypes($context = [])
    {
        return $this->getProductFieldMapper()->getAllAttributesTypes($context);
    }
}
