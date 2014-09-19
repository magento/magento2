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

namespace Magento\ConfigurableProduct\Test\Block\Product;

use Magento\ConfigurableProduct\Test\Block\Product\View\ConfigurableOptions;
use Mtf\Fixture\FixtureInterface;
use Mtf\Fixture\InjectableFixture;
use Magento\ConfigurableProduct\Test\Fixture\ConfigurableProduct;
use Magento\ConfigurableProduct\Test\Fixture\ConfigurableProductInjectable;

/**
 * Class View
 * Product view block on frontend page
 */
class View extends \Magento\Catalog\Test\Block\Product\View
{
    /**
     * Get configurable options block
     *
     * @return ConfigurableOptions
     */
    public function getConfigurableOptionsBlock()
    {
        return $this->blockFactory->create(
            'Magento\ConfigurableProduct\Test\Block\Product\View\ConfigurableOptions',
            ['element' => $this->_rootElement]
        );
    }

    /**
     * Fill in the option specified for the product
     *
     * @param FixtureInterface $product
     * @return void
     */
    public function fillOptions(FixtureInterface $product)
    {
        if ($product instanceof InjectableFixture) {
            /** @var ConfigurableProductInjectable $product */
            $attributesData = $product->getConfigurableAttributesData()['attributes_data'];
            $checkoutData = $product->getCheckoutData();

            // Prepare attribute data
            foreach ($attributesData as $attributeKey => $attribute) {
                $attributesData[$attributeKey] = [
                    'type' => $attribute['frontend_input'],
                    'title' => $attribute['label'],
                    'options' => [],
                ];

                foreach ($attribute['options'] as $optionKey => $option) {
                    $attributesData[$attributeKey]['options'][$optionKey] = [
                        'title' => $option['label']
                    ];
                }
                $attributesData[$attributeKey]['options'] = array_values($attributesData[$attributeKey]['options']);
            }
            $attributesData = array_values($attributesData);
        } else {
            // TODO: Removed after refactoring(removed) old product fixture.
            /** @var ConfigurableProduct $product */
            $attributesData = $product->getConfigurableAttributes();
            $checkoutData = $product->getCheckoutData();

            // Prepare attributes data
            foreach ($attributesData as $attributeKey => $attribute) {
                $attributesData[$attributeKey] = [
                    'type' => 'dropdown',
                    'title' => $attribute['label']['value']
                ];

                unset($attribute['label']);
                foreach ($attribute as $optionKey => $option) {
                    $attributesData[$attributeKey]['options'][$optionKey] = [
                        'title' => $option['option_label']['value']
                    ];
                }
            }
        }

        $configurableCheckoutData = isset($checkoutData['options']['configurable_options'])
            ? $checkoutData['options']['configurable_options']
            : [];
        $checkoutOptionsData = $this->prepareCheckoutData($attributesData, $configurableCheckoutData);
        $this->getCustomOptionsBlock()->fillCustomOptions($checkoutOptionsData);

        parent::fillOptions($product);
    }

    /**
     * Return product options
     *
     * @param FixtureInterface $product [optional]
     * @return array
     */
    public function getOptions(FixtureInterface $product = null)
    {
        $options = [
            'configurable_options' => $this->getConfigurableOptionsBlock()->getOptions($product)
        ];
        $options += parent::getOptions($product);

        return $options;
    }
}
