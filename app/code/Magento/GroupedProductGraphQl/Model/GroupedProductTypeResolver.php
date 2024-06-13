<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GroupedProductGraphQl\Model;

use Magento\Framework\GraphQl\Query\Resolver\TypeResolverInterface;
use Magento\GroupedProduct\Model\Product\Type\Grouped as Type;

/**
 * @inheritdoc
 */
class GroupedProductTypeResolver implements TypeResolverInterface
{
    const GROUPED_PRODUCT = 'GroupedProduct';
    /**
     * @inheritdoc
     */
    public function resolveType(array $data) : string
    {
        if (isset($data['type_id']) && $data['type_id'] == Type::TYPE_CODE) {
            return self::GROUPED_PRODUCT;
        }
        return '';
    }
}
