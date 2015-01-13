<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Test\Block\Adminhtml\Product;

use Magento\Backend\Test\Block\Widget\FormTabs;
use Mtf\Client\Element;
use Mtf\Fixture\DataFixture;
use Mtf\Fixture\FixtureInterface;
use Mtf\Fixture\InjectableFixture;

/**
 * Class ProductForm
 * Product creation form
 */
class ProductForm extends \Magento\Catalog\Test\Block\Adminhtml\Product\ProductForm
{
    /**
     * Fill the product form
     *
     * @param FixtureInterface $product
     * @param Element|null $element [optional]
     * @param FixtureInterface|null $category [optional]
     * @return FormTabs
     */
    public function fill(FixtureInterface $product, Element $element = null, FixtureInterface $category = null)
    {
        $tabs = $this->getFieldsByTabs($product);
        ksort($tabs);

        if ($product instanceof DataFixture) {
            $tabs = $this->normalizeDeprecateData($tabs);
            $category = ($category === null) ? $product->getCategories()['category'] : $category;
        }

        if ($category) {
            $tabs['product-details']['category_ids']['value'] = ($category instanceof InjectableFixture)
                ? $category->getName()
                : $category->getCategoryName();
        }

        $this->showAdvancedSettings();
        return $this->fillTabs($tabs, $element);
    }

    /**
     * Normalize data in DataFixture
     *
     * @param array $tabs
     * @return array
     */
    protected function normalizeDeprecateData(array $tabs)
    {
        if (!isset($tabs['variations'])) {
            return $tabs;
        }

        $variations = $tabs['variations'];

        $attributesData = [];
        if (isset($variations['configurable_attributes_data']['value'])) {
            foreach ($variations['configurable_attributes_data']['value'] as $key => $attribute) {
                $attributesData[$key] = [
                    'frontend_label' => $attribute['label']['value'],
                ];
                unset($attribute['label']);

                foreach ($attribute as $optionKey => $option) {
                    foreach ($option as $name => $field) {
                        $option[$name] = $field['value'];
                    }

                    $option['label'] = $option['option_label'];
                    unset($option['option_label']);

                    $attribute[$optionKey] = $option;
                }

                $attributesData[$key]['options'] = $attribute;
            }
        }

        $matrix = [];
        if (isset($variations['variations-matrix'])) {
            foreach ($variations['variations-matrix']['value'] as $key => $variation) {
                foreach ($variation['value'] as $name => $field) {
                    $matrix[$key][$name] = $field['value'];
                }
            }
        }

        $tabs['variations'] = [
            'configurable_attributes_data' => [
                'value' => [
                    'attributes_data' => $attributesData,
                    'matrix' => $matrix,
                ],
            ],
        ];
        unset($tabs['variations']['variations-matrix']);
        return $tabs;
    }
}
