<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Test\Block\Product\View;

use Magento\Catalog\Test\Block\Product\View\CustomOptions;
use Magento\ConfigurableProduct\Test\Fixture\ConfigurableProduct;
use Magento\ConfigurableProduct\Test\Fixture\ConfigurableProductInjectable;
use Mtf\Client\Element;
use Mtf\Client\Element\Locator;
use Mtf\Fixture\FixtureInterface;
use Mtf\Fixture\InjectableFixture;

/**
 * Class ConfigurableOptions
 * Form of configurable options product
 */
class ConfigurableOptions extends CustomOptions
{
    /**
     * Option selector
     *
     * @var string
     */
    protected $optionSelector = '//*[./label[contains(.,"%s")]]//select';

    /**
     * Get configurable product options
     *
     * @param FixtureInterface|null $product [optional]
     * @return array
     * @throws \Exception
     */
    public function getOptions(FixtureInterface $product)
    {
        if ($product instanceof InjectableFixture) {
            /** @var ConfigurableProductInjectable $product */
            $attributesData = $product->hasData('configurable_attributes_data')
                ? $product->getConfigurableAttributesData()['attributes_data']
                : [];
        } else {
            /** @var ConfigurableProduct $product */
            $attributesData = $product->getConfigurableAttributes();
            foreach ($attributesData as $key => $attributeData) {
                $attributeData['label'] = $attributeData['label']['value'];
                $attributeData['frontend_input'] = 'dropdown';

                $attributesData[$key] = $attributeData;
            }
        }

        $listOptions = $this->getListOptions();
        $result = [];

        foreach ($attributesData as $option) {
            $title = $option['label'];
            if (!isset($listOptions[$title])) {
                throw new \Exception("Can't find option: \"{$title}\"");
            }

            /** @var Element $optionElement */
            $optionElement = $listOptions[$title];
            $typeMethod = preg_replace('/[^a-zA-Z]/', '', $option['frontend_input']);
            $getTypeData = 'get' . ucfirst(strtolower($typeMethod)) . 'Data';

            $optionData = $this->$getTypeData($optionElement);
            $optionData['title'] = $title;
            $optionData['type'] = $option['frontend_input'];
            $optionData['is_require'] = $optionElement->find($this->required, Locator::SELECTOR_XPATH)->isVisible()
                ? 'Yes'
                : 'No';

            $result[$title] = $optionData;
            $this->_rootElement->find(sprintf($this->optionSelector, $title), Locator::SELECTOR_XPATH, 'select')
                ->setValue($optionData['options'][0]['title']);
        }

        return $result;
    }
}
