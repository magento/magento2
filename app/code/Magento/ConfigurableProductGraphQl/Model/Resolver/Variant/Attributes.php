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
        foreach ($value['options'] as $optionId => $option) {
            if (!isset($option['attribute_code'])) {
                continue;
            }
            $code = $option['attribute_code'];
            /** @var Product|null $model */
            $model = $value['product']['model'] ?? null;
            if (!$model || !$model->getData($code)) {
                continue;
            }

            if (isset($option['options_map'])) {
                $optionsFromMap = $this->getOptionsFromMap(
                    $option['options_map'] ?? [],
                    $code,
                    (int) $optionId,
                    (int) $model->getData($code)
                );
                if (!empty($optionsFromMap)) {
                    $data[] = $optionsFromMap;
                }
            }
        }
        return $data;
    }

    /**
     * Get options by index mapping
     *
     * @param array $optionMap
     * @param string $code
     * @param int $optionId
     * @param int $attributeCodeId
     * @return array
     */
    private function getOptionsFromMap(array $optionMap, string $code, int $optionId, int $attributeCodeId): array
    {
        $data = [];
        if (isset($optionMap[$optionId . ':' . $attributeCodeId])) {
            $optionValue = $optionMap[$optionId . ':' . $attributeCodeId];
            $data = $this->getOptionsArray($optionValue, $code);
        }
        return $data;
    }

    /**
     * Get options formatted as array
     *
     * @param array $optionValue
     * @param string $code
     * @return array
     */
    private function getOptionsArray(array $optionValue, string $code): array
    {
        return [
            'label' => $optionValue['label'] ??  null,
            'code' => $code,
            'use_default_value' => $optionValue['use_default_value'] ?? null,
            'value_index' => $optionValue['value_index'] ?? null,
        ];
    }
}
