<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestModuleGraphQlQuery\Model\Resolver;

use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\PostFetchProcessorInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\Resolver\ValueFactory;
use Magento\Framework\GraphQl\Query\ResolverInterface;

class Item implements ResolverInterface
{
    /**
     * @var ValueFactory
     */
    private $valueFactory;

    /**
     * @param ValueFactory $valueFactory
     */
    public function __construct(
        ValueFactory $valueFactory
    ) {
        $this->valueFactory = $valueFactory;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) : Value {
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

        $result = function () use ($itemData) {
            return $itemData;
        };

        return $this->valueFactory->create($result);
    }
}
