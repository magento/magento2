<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Block;

use Magento\Catalog\Test\Block\Product\View\CustomOptions;
use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Magento\Mtf\Block\Form;
use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Mtf\Fixture\InjectableFixture;

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

        /** @var CatalogProductSimple $product */
        if ($this->hasRender($typeId)) {
            $this->callRender($typeId, 'fillOptions', ['product' => $product]);
        }

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
                    'type' => $options[$attribute]['type'],
                    'title' => isset($options[$attribute]['title'])
                            ? $options[$attribute]['title']
                            : $attribute,
                    'value' => isset($options[$attribute]['options'][$option]['title'])
                            ? $options[$attribute]['options'][$option]['title']
                            : $option,
                ];
            }
        }

        return $result;
    }
}
