<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Swatches\Controller\Adminhtml\Product;

use Magento\Framework\Exception\LocalizedException;

/**
 * Test for product attribute save controller.
 *
 * @magentoAppArea adminhtml
 * @magentoDbIsolation enabled
 */
class AttributeTest extends \Magento\TestFramework\TestCase\AbstractBackendController
{
    /**
     * Generate random hex color.
     *
     * @return string
     */
    private function getRandomColor(): string
    {
        return '#' . str_pad(dechex(mt_rand(0, 0xFFFFFF)), 6, '0', STR_PAD_LEFT);
    }

    /**
     * Get visual swatches data set.
     *
     * @param int $optionsCount
     * @return array
     */
    private function getSwatchVisualDataSet(int $optionsCount): array
    {
        $optionsData = [];
        $expectedOptionsLabels = [];
        for ($i = 0; $i < $optionsCount; $i++) {
            $order = $i + 1;
            $expectedOptionLabelOnStoreView = "value_{$i}_store_1";
            $expectedOptionsLabels[$i+1] = $expectedOptionLabelOnStoreView;
            $optionsData []= "optionvisual[order][option_{$i}]={$order}";
            $optionsData []= "defaultvisual[]=option_{$i}";
            $optionsData []= "swatchvisual[value][option_{$i}]={$this->getRandomColor()}";
            $optionsData []= "optionvisual[value][option_{$i}][0]=value_{$i}_admin";
            $optionsData []= "optionvisual[value][option_{$i}][1]={$expectedOptionLabelOnStoreView}";
            $optionsData []= "optionvisual[delete][option_{$i}]=";
        }
        $optionsData []= "visual_swatch_validation=";
        $optionsData []= "visual_swatch_validation_unique=";
        return [
            'attribute_data' => array_merge_recursive(
                [
                    'serialized_swatch_values' => json_encode($optionsData),
                ],
                $this->getAttributePreset(),
                [
                    'frontend_input' => 'swatch_visual',
                ]
            ),
            'expected_options_count' => $optionsCount + 1,
            'expected_store_labels' => $expectedOptionsLabels,
        ];
    }

    /**
     * Get text swatches data set.
     *
     * @param int $optionsCount
     * @return array
     */
    private function getSwatchTextDataSet(int $optionsCount): array
    {
        $optionsData = [];
        $expectedOptionsLabels = [];
        for ($i = 0; $i < $optionsCount; $i++) {
            $order = $i + 1;
            $expectedOptionLabelOnStoreView = "value_{$i}_store_1";
            $expectedOptionsLabels[$i+1] = $expectedOptionLabelOnStoreView;
            $optionsData []= "optiontext[order][option_{$i}]={$order}";
            $optionsData []= "defaulttext[]=option_{$i}";
            $optionsData []= "swatchtext[value][option_{$i}]=x{$i}";
            $optionsData []= "optiontext[value][option_{$i}][0]=value_{$i}_admin";
            $optionsData []= "optiontext[value][option_{$i}][1]={$expectedOptionLabelOnStoreView}";
            $optionsData []= "optiontext[delete][option_{$i}]=";
        }
        $optionsData []= "text_swatch_validation=";
        $optionsData []= "text_swatch_validation_unique=";
        return [
            'attribute_data' => array_merge_recursive(
                [
                    'serialized_swatch_values' => json_encode($optionsData),
                ],
                $this->getAttributePreset(),
                [
                    'frontend_input' => 'swatch_text',
                ]
            ),
            'expected_options_count' => $optionsCount + 1,
            'expected_store_labels' => $expectedOptionsLabels,
        ];
    }

    /**
     * Get data preset for new attribute.
     *
     * @return array
     */
    private function getAttributePreset(): array
    {
        return [
            'serialized_options' => '[]',
            'form_key' => 'XxtpPYjm2YPYUlAt',
            'frontend_label' => [
                0 => 'asdasd',
                1 => '',
                2 => '',
            ],
            'is_required' => '0',
            'update_product_preview_image' => '0',
            'use_product_image_for_swatch' => '0',
            'is_global' => '0',
            'default_value_text' => '512',
            'default_value_yesno' => '1',
            'default_value_date' => '1/1/70',
            'default_value_textarea' => '512',
            'is_unique' => '0',
            'is_used_in_grid' => '1',
            'is_visible_in_grid' => '1',
            'is_filterable_in_grid' => '1',
            'is_searchable' => '0',
            'is_comparable' => '0',
            'is_filterable' => '0',
            'is_filterable_in_search' => '0',
            'position' => '0',
            'is_used_for_promo_rules' => '0',
            'is_html_allowed_on_front' => '1',
            'is_visible_on_front' => '0',
            'used_in_product_listing' => '0',
            'used_for_sort_by' => '0',
            'attribute_code' => 'test_many_swatches',
        ];
    }

    /**
     * Data provider for large swatches amount test.
     *
     * @return array
     */
    public function getLargeSwatchesAmountAttributeData(): array
    {
        $maxInputVars = ini_get('max_input_vars');
        // Each option is at least 7 variables array for a visual swatch.
        // Set options count to exceed max_input_vars by 20 options (140 variables).
        $swatchVisualOptionsCount = (int)floor($maxInputVars / 7) + 20;
        $swatchTextOptionsCount = (int)floor($maxInputVars / 4) + 80;
        return [
            'visual swatches' => $this->getSwatchVisualDataSet($swatchVisualOptionsCount),
            'text swatches' => $this->getSwatchTextDataSet($swatchTextOptionsCount),
        ];
    }

    /**
     * Test attribute saving with large amount of options exceeding maximum allowed by max_input_vars limit.
     *
     * @dataProvider getLargeSwatchesAmountAttributeData()
     * @param array $attributeData
     * @param int $expectedOptionsCount
     * @param array $expectedLabels
     * @return void
     */
    public function testLargeOptionsDataSet(
        array $attributeData,
        int $expectedOptionsCount,
        array $expectedLabels
    ) {
        $this->getRequest()->setPostValue($attributeData);
        $this->dispatch('backend/catalog/product_attribute/save');
        $entityTypeId = $this->_objectManager->create(
            \Magento\Eav\Model\Entity::class
        )->setType(
            \Magento\Catalog\Model\Product::ENTITY
        )->getTypeId();

        /** @var $attribute \Magento\Catalog\Model\ResourceModel\Eav\Attribute */
        $attribute = $this->_objectManager->create(
            \Magento\Catalog\Model\ResourceModel\Eav\Attribute::class
        )->setEntityTypeId(
            $entityTypeId
        );
        try {
            $attribute->loadByCode($entityTypeId, 'test_many_swatches');
            $options = $attribute->getOptions();
            // assert that all options are saved without truncation
            $this->assertEquals(
                $expectedOptionsCount,
                count($options),
                'Expected options count does not match (regarding first empty option for non-required attribute)'
            );

            foreach ($expectedLabels as $optionOrderNum => $label) {
                $this->assertEquals(
                    $label,
                    $options[$optionOrderNum]->getLabel(),
                    "Label for option #{$optionOrderNum} does not match expected."
                );
            }
        } catch (LocalizedException $e) {
            $this->fail('Test failed with exception on attribute model load: ' . $e);
        }
    }
}
