<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch7\Model\Adapter\FieldMapper\Product\FieldProvider\FieldName\Resolver;

use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\AttributeAdapter;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\FieldName\ResolverInterface;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\FieldName\Resolver\DefaultResolver
    as DefaultFiledNameResolver;

/**
 * Default name resolver for Elasticsearch 7
 */
class DefaultResolver implements ResolverInterface
{
    /**
     * @var DefaultFiledNameResolver
     */
    private $baseResolver;

    /**
     * DefaultResolver constructor.
     * @param DefaultFiledNameResolver $baseResolver
     */
    public function __construct(DefaultFiledNameResolver $baseResolver)
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
