<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
use Magento\Eav\Api\Data\AttributeOptionInterface;

/** @var \Magento\Framework\ObjectManagerInterface $objectManager */
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

$data = [
    'is_required' => 1,
    'is_visible_on_front' => 1,
    'is_visible_in_advanced_search' => 0,
    'attribute_code' => 'color_swatch',
    'backend_type' => '',
    'is_searchable' => 0,
    'is_filterable' => 0,
    'is_filterable_in_search' => 0,
    'frontend_label' => 'Attribute ',
    'entity_type_id' => 4
];
$optionsPerAttribute = 3;

$data['frontend_input'] = 'swatch_visual';
$data['swatch_input_type'] = 'visual';
$data['swatchvisual']['value'] = array_reduce(
    range(1, $optionsPerAttribute),
    function ($values, $index) use ($optionsPerAttribute) {
        $values['option_' . $index] = '#'
            . str_repeat(
                dechex(255 * $index / $optionsPerAttribute),
                3
            );
        return $values;
    },
    []
);
$data['optionvisual']['value'] = array_reduce(
    range(1, $optionsPerAttribute),
    function ($values, $index) use ($optionsPerAttribute) {
        $values['option_' . $index] = ['option ' . $index];
        return $values;
    },
    []
);

$data['options']['option'] = array_reduce(
    range(1, $optionsPerAttribute),
    function ($values, $index) use ($optionsPerAttribute) {
        $values[] = [
            'label' => 'option ' . $index,
            'value' => 'option_' . $index,
        ];
        return $values;
    },
    []
);

$options = [];
foreach ($data['options']['option'] as $optionData) {
    $options[] = $objectManager->get(AttributeOptionInterface::class)
        ->setLabel($optionData['label'])
        ->setValue($optionData['value']);
}

$attribute = $objectManager->create(
    \Magento\Catalog\Api\Data\ProductAttributeInterface::class,
    ['data' => $data]
);
$attribute->setOptions($options);
$attribute->save();
