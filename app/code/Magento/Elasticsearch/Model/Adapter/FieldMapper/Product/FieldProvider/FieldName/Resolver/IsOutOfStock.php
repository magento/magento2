<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\FieldName\Resolver;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\AttributeAdapter;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\FieldName\ResolverInterface;
use Magento\Store\Model\StoreManagerInterface as StoreManager;

/**
 * Resolver field name for is_out_of_stock attribute.
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class IsOutOfStock implements ResolverInterface
{
    /**
     * @inheritdoc
     */
    public function getFieldName(AttributeAdapter $attribute, $context = []): ?string
    {
        if ($attribute->getAttributeCode() === 'is_out_of_stock') {
            return 'is_out_of_stock';
        }

        return null;
    }
}
