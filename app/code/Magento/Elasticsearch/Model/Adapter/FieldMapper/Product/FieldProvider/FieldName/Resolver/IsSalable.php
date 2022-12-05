<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\FieldName\Resolver;

use Magento\Elasticsearch\Model\Adapter\BatchDataMapper\InventoryFieldsProvider;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\AttributeAdapter;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\FieldName\ResolverInterface;

/**
 * Resolver field name for is_salable attribute.
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class IsSalable implements ResolverInterface
{
    /**
     * @inheritdoc
     */
    public function getFieldName(AttributeAdapter $attribute, $context = []): ?string
    {
        if ($attribute->getAttributeCode() === InventoryFieldsProvider::IS_SALABLE) {
            return $attribute->getAttributeCode();
        }

        return null;
    }
}
