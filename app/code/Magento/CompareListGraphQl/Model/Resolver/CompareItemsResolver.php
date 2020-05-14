<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CompareListGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class CompareItemsResolver implements ResolverInterface
{

    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $items[] = [
          'sku' => 'test123',
          'name' => 'NAME test',
          'productId' => '123'
        ];
        return ['items' => $items];
    }
}
