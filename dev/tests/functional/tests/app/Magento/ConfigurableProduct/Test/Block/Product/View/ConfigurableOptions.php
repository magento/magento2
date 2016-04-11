<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Test\Block\Product\View;

use Magento\Catalog\Test\Block\Product\View\CustomOptions;
use Magento\ConfigurableProduct\Test\Fixture\ConfigurableProduct;
use Magento\Mtf\Client\Element;
use Magento\Mtf\Client\Locator;
use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Mtf\Fixture\InjectableFixture;
use Magento\Mtf\Client\Element\SimpleElement;

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
     * Selector for price block.
     *
     * @var string
     */
    protected $priceBlock = '//*[@class="product-info-main"]//*[contains(@class,"price-box")]';

    /**
     * Get configurable product options
     *
     * @param FixtureInterface|null $product [optional]
     * @return array
     * @throws \Exception
     */
    public function getOptions(FixtureInterface $product)
    {
        /** @var ConfigurableProduct $product */
        $attributesData = $product->hasData('configurable_attributes_data')
            ? $product->getConfigurableAttributesData()['attributes_data']
            : [];
        $listOptions = $this->getListOptions();
        $result = [];

        foreach ($attributesData as $option) {
            $title = $option['label'];
            if (!isset($listOptions[$title])) {
                throw new \Exception("Can't find option: \"{$title}\"");
            }

            /** @var SimpleElement $optionElement */
            $optionElement = $listOptions[$title];
            $typeMethod = preg_replace('/[^a-zA-Z]/', '', $option['frontend_input']);
            $getTypeData = 'get' . ucfirst(strtolower($typeMethod)) . 'Data';

            $optionData = $this->$getTypeData($optionElement);
            $optionData['title'] = $title;
            $optionData['type'] = $option['frontend_input'];
            $optionData['is_require'] = $optionElement->find($this->required, Locator::SELECTOR_XPATH)->isVisible()
                ? 'Yes'
                : 'No';

            foreach ($optionData['options'] as $key => $value) {
                $optionData['options'][$key]['price'] = $this->getOptionPrice($title, $value['title']);
            }
            $result[$title] = $optionData;
        }

        return $result;
    }

    /**
     * Get option price
     *
     * @param $attributeTitle
     * @param $optionTitle
     * @return null|string
     */
    protected function getOptionPrice($attributeTitle, $optionTitle)
    {
        $this->_rootElement->find(sprintf($this->optionSelector, $attributeTitle), Locator::SELECTOR_XPATH, 'select')
            ->setValue($optionTitle);
        return $this->getPriceBlock()->getPrice();
    }

    /**
     * Get block price.
     *
     * @return \Magento\Catalog\Test\Block\Product\Price
     */
    protected function getPriceBlock()
    {
        return $this->blockFactory->create(
            'Magento\Catalog\Test\Block\Product\Price',
            ['element' => $this->_rootElement->find($this->priceBlock, Locator::SELECTOR_XPATH)]
        );
    }
}
