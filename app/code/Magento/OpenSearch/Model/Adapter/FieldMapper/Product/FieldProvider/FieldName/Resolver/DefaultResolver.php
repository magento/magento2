<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\OpenSearch\Model\Adapter\FieldMapper\Product\FieldProvider\FieldName\Resolver;

use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\AttributeAdapter;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\FieldName\ResolverInterface;

/**
 * Default name resolver
 */
class DefaultResolver implements ResolverInterface
{
    /**
     * @var ResolverInterface
     */
    private $baseResolver;

    /**
     * @param ResolverInterface $baseResolver
     */
    public function __construct(ResolverInterface $baseResolver)
    {
        $this->baseResolver = $baseResolver;
    }

    /**
     * Returns field name.
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
