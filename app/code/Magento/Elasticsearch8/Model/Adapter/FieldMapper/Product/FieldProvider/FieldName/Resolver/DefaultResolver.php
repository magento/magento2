<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch8\Model\Adapter\FieldMapper\Product\FieldProvider\FieldName\Resolver;

use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\AttributeAdapter;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\FieldName\ResolverInterface;

/**
 * Default name resolver for Elasticsearch 8
 * @deprecated Elasticsearch8 is no longer supported by Adobe
 * @see this class will be responsible for ES8 only
 */
class DefaultResolver implements ResolverInterface
{
    /**
     * @var ResolverInterface
     */
    private ResolverInterface $baseResolver;

    /**
     * DefaultResolver constructor.
     * @param ResolverInterface $baseResolver
     */
    public function __construct(ResolverInterface $baseResolver)
    {
        $this->baseResolver = $baseResolver;
    }

    /**
     * Get field name.
     *
     * @param AttributeAdapter $attribute
     * @param array $context
     * @return string|null
     */
    public function getFieldName(AttributeAdapter $attribute, $context = []): ?string
    {
        $fieldName = $this->baseResolver->getFieldName($attribute, $context);
        if ($fieldName === '_all') {
            $fieldName = '_search';
        }

        return $fieldName;
    }
}
