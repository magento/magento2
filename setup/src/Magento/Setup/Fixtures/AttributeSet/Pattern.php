<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Fixtures\AttributeSet;

/**
 * Generate Data for Fixture to create an Attribute Set with specified pattern.
 */
class Pattern
{
    /**
     * @var array
     */
    private $attributePattern = [
        'is_required' => 1,
        'is_visible_on_front' => 1,
        'is_visible_in_advanced_search' => 0,
        'attribute_code' => 'attribute_',
        'backend_type' => '',
        'is_searchable' => 0,
        'is_filterable' => 0,
        'is_filterable_in_search' => 0,
        'frontend_label' => 'Attribute ',
        'frontend_input' => 'select',
    ];

    /**
     * Generate Data for Fixture to create an Attribute Set with specified pattern.
     *
     * @param string $name
     * @param int $attributesPerSet
     * @param int $optionsPerAttribute
     * @param callable $attributePattern  callback in f($index, $attributeData) format
     * @return array
     */
    public function generateAttributeSet(
        $name,
        $attributesPerSet,
        $optionsPerAttribute,
        $attributePattern = null
    ) {
        $attributeSet = [
            'name' => $name,
            'attributes' => []
        ];
        for ($index = 1; $index <= $attributesPerSet; $index++) {
            $attributeData =  $this->generateAttribute(
                $index,
                is_array($optionsPerAttribute) ? $optionsPerAttribute[$index-1] : $optionsPerAttribute
            );
            if (is_callable($attributePattern)) {
                $attributeData = $attributePattern($index, $attributeData);
            }
            $attributeSet['attributes']['attribute'][] = $attributeData;
        }

        return $attributeSet;
    }

    /**
     * Generate Attributes for Set.
     *
     * @param int $index
     * @param int $optionsPerAttribute
     * @return array
     */
    private function generateAttribute($index, $optionsPerAttribute)
    {
        $attribute = $this->attributePattern; // copy pattern
        $attribute['attribute_code'] = $attribute['attribute_code'] . $index;
        $attribute['frontend_label'] = $attribute['frontend_label'] . $index;
        $attribute['options'] = ['option' => $this->generateOptions($optionsPerAttribute)];
        $attribute['default_option'] = $attribute['options']['option'][0]['label'];
        return $attribute;
    }

    /**
     * Generate Options for Attribute.
     *
     * @param int $optionsPerAttribute
     * @return array
     */
    private function generateOptions($optionsPerAttribute)
    {
        $options = [];
        for ($index = 1; $index <= $optionsPerAttribute; $index++) {
            $options[] = [
                'label' => 'option ' . $index,
                'value' => 'option_' . $index
            ];
        }

        return $options;
    }
}
