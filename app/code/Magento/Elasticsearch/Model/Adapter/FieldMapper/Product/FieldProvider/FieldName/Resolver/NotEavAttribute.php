<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\FieldName\Resolver;

use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\AttributeAdapter;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\FieldName\ResolverInterface;

/**
 * Resolver field name for not EAV attribute.
 * @deprecated Elasticsearch is no longer supported by Adobe
 * @see this class will be responsible for ES only
 */
class NotEavAttribute implements ResolverInterface
{
    /**
     * Get field name for not EAV attributes.
     *
     * @param AttributeAdapter $attribute
     * @param array $context
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getFieldName(AttributeAdapter $attribute, $context = []): ?string
    {
        if (!$attribute->isEavAttribute()) {
            return $attribute->getAttributeCode();
        }

        return null;
    }
}
