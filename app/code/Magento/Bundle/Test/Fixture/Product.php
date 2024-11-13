<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\Test\Fixture;

use Magento\Catalog\Model\Product\Type;
use Magento\Framework\DataObject;

class Product extends \Magento\Catalog\Test\Fixture\Product
{
    private const DEFAULT_DATA = [
        'id' => null,
        'type_id' => Type::TYPE_BUNDLE,
        'attribute_set_id' => 4,
        'name' => 'Bundle Product%uniqid%',
        'sku' => 'bundle-product%uniqid%',
        'price' => null,
        'weight' => null,
        'custom_attributes' => [
            'price_view' => '0',
            'sku_type' => '0',
            'price_type' => '0',
            'weight_type' => '0',
            'shipment_type' => '0',
        ],
        'extension_attributes' => [
            'bundle_product_options' => [],
        ]
    ];

    /**
     * {@inheritdoc}
     * @param array $data Parameters. Same format as \Magento\Catalog\Test\Fixture\Product::DEFAULT_DATA.
     * Custom attributes and extension attributes can be passed directly in the outer array instead of custom_attributes
     * or extension_attributes.
     * Additional fields:
     * - $data['_options']: An array of options. See Magento\Bundle\Test\Fixture\Option
     */
    public function apply(array $data = []): ?DataObject
    {
        return parent::apply($this->prepareData($data));
    }

    /**
     * Prepare product data
     *
     * @param array $data
     * @return array
     */
    private function prepareData(array $data): array
    {
        $data = array_merge(self::DEFAULT_DATA, $data);

        if (isset($data['_options'])) {
            $data['extension_attributes']['bundle_product_options'] = array_map(
                static function ($option) {
                    return $option instanceof DataObject ? $option->toArray() : $option;
                },
                $data['_options']
            );
            unset($data['_options']);
        }

        return $data;
    }
}
