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

namespace Magento\Catalog\Test\Block;

use Mtf\Block\Form;
use Mtf\Fixture\FixtureInterface;
use Mtf\Fixture\InjectableFixture;
use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Magento\Catalog\Test\Block\Product\View\CustomOptions;

/**
 * Class AbstractConfigureBlock
 * Product configure block
 */
abstract class AbstractConfigureBlock extends Form
{
    /**
     * Custom options CSS selector
     *
     * @var string
     */
    protected $customOptionsSelector;

    /**
     * This method returns the custom options block
     *
     * @return CustomOptions
     */
    public function getCustomOptionsBlock()
    {
        return $this->blockFactory->create(
            'Magento\Catalog\Test\Block\Product\View\CustomOptions',
            ['element' => $this->_rootElement->find($this->customOptionsSelector)]
        );
    }

    /**
     * Fill in the option specified for the product
     *
     * @param FixtureInterface $product
     * @return void
     *
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function fillOptions(FixtureInterface $product)
    {
        $dataConfig = $product->getDataConfig();
        $typeId = isset($dataConfig['type_id']) ? $dataConfig['type_id'] : null;
        $checkoutData = null;

        if ($product instanceof InjectableFixture) {
            /** @var CatalogProductSimple $product */
            $checkoutData = $product->getCheckoutData();
            $checkoutCustomOptions = isset($checkoutData['options']['custom_options'])
                ? $checkoutData['options']['custom_options']
                : [];
            $customOptions = $product->hasData('custom_options')
                ? $product->getDataFieldConfig('custom_options')['source']->getCustomOptions()
                : [];

            $checkoutCustomOptions = $this->prepareCheckoutData($customOptions, $checkoutCustomOptions);
            $this->getCustomOptionsBlock()->fillCustomOptions($checkoutCustomOptions);
        }

        /** @var CatalogProductSimple $product */
        if ($this->hasRender($typeId)) {
            $this->callRender($typeId, 'fillOptions', ['product' => $product]);
        }
    }

    /**
     * Set quantity
     *
     * @param int $qty
     * @return void
     */
    abstract public function setQty($qty);

    /**
     * Replace index fields to name fields in checkout data
     *
     * @param array $options
     * @param array $checkoutData
     * @return array
     */
    protected function prepareCheckoutData(array $options, array $checkoutData)
    {
        $result = [];

        foreach ($checkoutData as $checkoutOption) {
            $attribute = str_replace('attribute_key_', '', $checkoutOption['title']);
            $option = str_replace('option_key_', '', $checkoutOption['value']);

            if (isset($options[$attribute])) {
                $result[] = [
                    'type' => strtolower(preg_replace('/[^a-z]/i', '', $options[$attribute]['type'])),
                    'title' => isset($options[$attribute]['title'])
                        ? $options[$attribute]['title']
                        : $attribute,
                    'value' => isset($options[$attribute]['options'][$option]['title'])
                        ? $options[$attribute]['options'][$option]['title']
                        : $option
                ];
            }
        }

        return $result;
    }
}
