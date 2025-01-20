<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * Root category tree field resolver, used for GraphQL request processing.
 *
 * @deprecated Use the UID instead of a numeric id
 * @see \Magento\CatalogGraphQl\Model\Resolver\RootCategoryUid
 */
class RootCategoryId implements ResolverInterface
{
    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, ?array $value = null, ?array $args = null)
    {
        return (int)$context->getExtensionAttributes()->getStore()->getRootCategoryId();
    }
}
