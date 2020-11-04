<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SearchStorefront\DataProvider;

use Magento\Framework\GraphQl\Config\Element\Type;
use Magento\Framework\GraphQl\Config\Element\InterfaceType;

/**
 * Map for category attributes.
 */
class CategoryAttributesMapper
{
    /**
     * Returns attribute values for given attribute codes.
     *
     * @param array $fetchResult
     * @return array
     */
    public function getAttributesValues(array $fetchResult): array
    {
        $attributes = [];

        foreach ($fetchResult as $row) {
            if (!isset($attributes[$row['entity_id']])) {
                $attributes[$row['entity_id']] = $row;
                //TODO: do we need to introduce field mapping?
                $attributes[$row['entity_id']]['id'] = $row['entity_id'];
            }
            if (isset($row['attribute_code'])) {
                $attributes[$row['entity_id']][$row['attribute_code']] = $row['value'];
            }
        }

        return $attributes;
    }

    /**
     * Format attributes that should be converted to array type
     *
     * @param array $attributes
     * @return array
     */
    private function formatAttributes(array $attributes): array
    {
        $arrayTypeAttributes = $this->getFieldsOfArrayType();

        return $arrayTypeAttributes
            ? array_map(
                function ($data) use ($arrayTypeAttributes) {
                    foreach ($arrayTypeAttributes as $attributeCode) {
                        $data[$attributeCode] = $this->valueToArray($data[$attributeCode] ?? null);
                    }
                    return $data;
                },
                $attributes
            )
            : $attributes;
    }

    /**
     * Cast string to array
     *
     * @param string|null $value
     * @return array
     */
    private function valueToArray($value): array
    {
        return $value ? \explode(',', $value) : [];
    }

    /**
     * Get fields that should be converted to array type
     *
     * @return array
     */
    private function getFieldsOfArrayType(): array
    {
        $categoryTreeSchema = $this->graphqlConfig->getConfigElement('CategoryTree');
        if (!$categoryTreeSchema instanceof Type) {
            throw new \LogicException('CategoryTree type not defined in schema.');
        }

        $fields = [];
        foreach ($categoryTreeSchema->getInterfaces() as $interface) {
            /** @var InterfaceType $configElement */
            $configElement = $this->graphqlConfig->getConfigElement($interface['interface']);

            foreach ($configElement->getFields() as $field) {
                if ($field->isList()) {
                    $fields[] = $field->getName();
                }
            }
        }

        return $fields;
    }
}
