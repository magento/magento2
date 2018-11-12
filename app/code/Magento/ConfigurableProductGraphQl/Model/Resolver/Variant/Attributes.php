<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProductGraphQl\Model\Resolver\Variant;

use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;

/**
 * Format a product's option information to conform to GraphQL schema representation
 */
class Attributes implements ResolverInterface
{
    /**
     * Format product's option data to conform to GraphQL schema
     *
     * {@inheritdoc}
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if (!isset($value['options']) || !isset($value['product'])) {
            return null;
        }

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
    }
}
