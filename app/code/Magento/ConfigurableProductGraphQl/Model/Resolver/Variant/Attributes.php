<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types = 1);

namespace Magento\ConfigurableProductGraphQl\Model\Resolver\Variant;

use GraphQL\Type\Definition\ResolveInfo;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Option;
use Magento\Framework\GraphQl\Config\Data\Field;
use Magento\Framework\GraphQl\Resolver\ResolverInterface;
use Magento\Framework\GraphQl\Resolver\Value;
use Magento\Framework\GraphQl\Resolver\ValueFactory;

/**
 * Format a product's option information to conform to GraphQL schema representation
 */
class Attributes implements ResolverInterface
{
    /**
     * @var ValueFactory
     */
    private $valueFactory;

    /**
     * @param ValueFactory $valueFactory
     */
    public function __construct(ValueFactory $valueFactory)
    {
        $this->valueFactory = $valueFactory;
    }

    /**
     * Format product's option data to conform to GraphQL schema
     *
     * {@inheritdoc}
     */
    public function resolve(
        Field $field,
        array $value = null,
        array $args = null,
        $context,
        ResolveInfo $info
    ): ?Value {
        if (!isset($value['options']) || !isset($value['product'])) {
            return null;
        }

        $result = function () use ($value) {
            $data = [];
            foreach ($value['options'] as $option) {
                $code = $option['attribute_code'];
                if (!isset($value['product'][$code])) {
                    continue;
                }

                foreach ($option['values'] as $optionValue) {
                    if ($optionValue['value_index'] != $value['product'][$code]) {
                        continue;
                    }
                    $data[] = [
                        'label' => $optionValue['label'],
                        'code' => $code,
                        'use_default_value' => $optionValue['use_default_value'],
                        'value_index' => $optionValue['value_index']
                    ];
                }
            }

            return $data;
        };

        return $this->valueFactory->create($result);
    }
}
