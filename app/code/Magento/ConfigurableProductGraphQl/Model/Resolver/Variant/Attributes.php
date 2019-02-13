<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProductGraphQl\Model\Resolver\Variant;

use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Catalog\Model\Product;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;

/**
 * Format a product's option information to conform to GraphQL schema representation
 */
class Attributes implements ResolverInterface
{
    /**
     * @inheritdoc
     *
     * Format product's option data to conform to GraphQL schema
     *
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @throws \Exception
     * @return mixed|Value
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
            /** @var Product|null $model */
            $model = $value['product']['model'] ?? null;
            if (!$model || !$model->getData($code)) {
                continue;
            }

            foreach ($option['values'] as $optionValue) {
                if ($optionValue['value_index'] != $model->getData($code)) {
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
