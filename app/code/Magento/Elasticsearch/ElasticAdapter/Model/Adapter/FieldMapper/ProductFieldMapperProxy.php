<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\ElasticAdapter\Model\Adapter\FieldMapper;

use Magento\AdvancedSearch\Model\Client\ClientResolver;
use Magento\Elasticsearch\Model\Adapter\FieldMapperInterface;

/**
 * Proxy for product fields mappers
 * @deprecated Elasticsearch is no longer supported by Adobe
 * @see this class will be responsible for ES only
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
     * Get Product Field Mapper
     *
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
     */
    public function getAllAttributesTypes($context = [])
    {
        return $this->getProductFieldMapper()->getAllAttributesTypes($context);
    }
}
