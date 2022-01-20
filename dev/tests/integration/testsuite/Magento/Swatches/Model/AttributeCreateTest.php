<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Swatches\Model;

use Magento\Catalog\Api\Data\ProductAttributeInterfaceFactory;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Eav\Api\Data\AttributeOptionInterfaceFactory;

/**
 * Test save of swatch attribute
 *
 */
class AttributeCreateTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @magentoAppArea adminhtml
     * @magentoDbIsolation enabled
     */
    public function testSetScopeDefault()
    {
        $om = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

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
            function ($values, $index) {
                $values['option_' . $index] = ['option ' . $index];
                return $values;
            },
            []
        );

        $data['options']['option'] = array_reduce(
            range(1, $optionsPerAttribute),
            function ($values, $index) {
                $values[] = [
                    'label' => 'option ' . $index,
                    'value' => 'option_' . $index
                ];
                return $values;
            },
            []
        );

        $options = [];
        foreach ($data['options']['option'] as $optionData) {
            $options[] = $om->get(AttributeOptionInterfaceFactory::class)->create(['data' => $optionData]);
        }

        $attribute = $om->get(ProductAttributeInterfaceFactory::class)
            ->create(['data' => $data]);
        $attribute->setOptions($options);
        $attribute->setNote('auto');

        $attribute = $om->get(ProductAttributeRepositoryInterface::class)->save($attribute);
        $this->assertNotEmpty($attribute->getId());
        $this->assertEquals('swatch_visual', $attribute->getFrontendInput());
    }
}
