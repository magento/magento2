<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch7\Model\Adapter\FieldMapper\Product\FieldProvider\FieldName\Resolver;

use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\AttributeAdapter;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\FieldName\ResolverInterface;

/**
 * Default name resolver for Elasticsearch 7
 * @deprecated 100.3.0 because of EOL for Elasticsearch7
 * @see this class will be responsible for ES7 only
 */
class DefaultResolver implements ResolverInterface
{
    /**
     * @var ResolverInterface
     */
    private $baseResolver;

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
