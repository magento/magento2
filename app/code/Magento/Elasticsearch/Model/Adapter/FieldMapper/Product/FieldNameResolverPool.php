<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Elasticsearch\Model\Adapter\FieldMapper\Product;

use Magento\Elasticsearch\Model\Adapter\FieldMapper\FieldNameResolverPoolInterface;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldName\SpecificationInterface;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldName\ResolverInterface;

/**
 * Pool of field name resolvers.
 */
class FieldNameResolverPool implements FieldNameResolverPoolInterface
{
    /**
     * @var ResolverInterface[]
     */
    private $map;

    /**
     * @var SpecificationInterface
     */
    private $specification;

    /**
     * @param SpecificationInterface $specification
     * @param array $map
     */
    public function __construct(
        SpecificationInterface $specification,
        array $map
    ) {
        $this->specification = $specification;
        $this->map = $map;
    }

    /**
     * Get field name resolver.
     *
     * @param string $attributeCode
     * @param array $context
     * @return ResolverInterface
     */
    public function getResolver(string $attributeCode, $context = []): ResolverInterface
    {
        $specification = $this->specification->resolve($attributeCode, $context);
        if (!isset($this->map[$specification])) {
            $specification = 'default';
        }

        return $this->map[$specification];
    }
}
