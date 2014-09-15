<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\ConfigurableProduct\Test\Block\Adminhtml\Product;

use Mtf\Client\Element;
use Magento\Backend\Test\Block\Widget\FormTabs;
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
            $tabs['product-details']['category_ids']['value'] = ($category instanceof InjectableFixture )
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
                    'frontend_label' => $attribute['label']['value']
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
                    'matrix' => $matrix
                ]
            ]
        ];
        unset($tabs['variations']['variations-matrix']);
        return $tabs;
    }
}
