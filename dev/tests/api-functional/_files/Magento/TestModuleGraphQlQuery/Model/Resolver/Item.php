<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\TestModuleGraphQlQuery\Model\Resolver;

use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;

/**
 * Resolver for Item
 */
class Item implements ResolverInterface
{
    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        ?array $value = null,
        ?array $args = null
    ) {
        $id = 0;
        foreach ($args as $key => $argValue) {
            if ($key === "id") {
                $id = (int)$argValue;
            }
        }
        $itemData = [
            'item_id' => $id,
            'name' => "itemName"
        ];
        return $itemData;
    }
}
