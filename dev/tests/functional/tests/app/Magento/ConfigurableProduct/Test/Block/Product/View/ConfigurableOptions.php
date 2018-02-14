<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
     * Selector for tier prices.
     *
     * @var string
     */
    private $tierPricesSelector = '.prices-tier li';

    /**
     * Product info main block.
     *
     * @var string
     */
    private $mainBlockSelector = '.product-info-main';

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

            $result[$title] = $optionData;
            // Select first attribute option to be able proceed with next attribute
            $this->selectOption($title, $optionData['options'][0]['title']);
        }

        return $result;
    }

    /**
     * Get configurable attributes options prices
     *
     * @param FixtureInterface $product
     * @return array
     */
    public function getOptionsPrices(FixtureInterface $product)
    {
        /** @var ConfigurableProduct $product */
        $attributesData = [];
        $productVariations = [];
        if ($product->hasData('configurable_attributes_data')) {
            $attributesData = $product->getConfigurableAttributesData()['attributes_data'];
            $productVariations = $product->getConfigurableAttributesData()['matrix'];
        }

        $productVariations = array_keys($productVariations);
        $result = [];
        foreach ($productVariations as $variation) {
            $variationOptions = explode(' ', $variation);
            //Select all options specified in variation
            $this->chooseOptions($variationOptions, $attributesData);
            $result[$variation]['price'] = $this->getOptionPrice();
            $tierPrices = $this->getOptionTierPrices();
            if (count($tierPrices) > 0) {
                $result[$variation]['tierPrices'] = $tierPrices;
            }
        }

        return $result;
    }

    /**
     * Get option price
     *
     * @return null|string
     */
    protected function getOptionPrice()
    {
        $priceBlock = $this->getPriceBlock();
        $price = ($priceBlock->isOldPriceVisible()) ? $priceBlock->getOldPrice() : $priceBlock->getPrice();
        return $price;
    }

    /**
     * Get tier prices of all variations.
     *
     * @return array
     */
    private function getOptionTierPrices()
    {
        $prices = [];
        $mainBlock = $this->browser->find($this->mainBlockSelector);
        $tierPricesNodes = $mainBlock->getElements($this->tierPricesSelector);
        foreach ($tierPricesNodes as $node) {
            preg_match('#^[^\d]+(\d+)[^\d]+(\d+(?:(?:,\d+)*)+(?:.\d+)*).*#i', $node->getText(), $matches);
            $prices[] = [
                'qty' => isset($matches[1]) ? $matches[1] : null,
                'price_qty' => isset($matches[2]) ? $matches[2] : null,
            ];
        }

        return $prices;
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

    /**
     * Select option from the select element.
     *
     * @param string $attributeTitle
     * @param string $optionTitle
     */
    protected function selectOption($attributeTitle, $optionTitle)
    {
        $this->_rootElement->find(sprintf($this->optionSelector, $attributeTitle), Locator::SELECTOR_XPATH, 'select')
            ->setValue($optionTitle);
    }

    /**
     * Choose options of the configurable product.
     *
     * @param array $variationOptions
     * @param array $attributesData
     * @return void
     */
    protected function chooseOptions(array $variationOptions, array $attributesData)
    {
        //Select all options specified in variation
        foreach ($variationOptions as $variationSelection) {
            list ($attribute, $option) = explode(':', $variationSelection);
            $attributeTitle = $attributesData[$attribute]['label'];
            $optionTitle = $attributesData[$attribute]['options'][$option]['label'];
            $this->selectOption($attributeTitle, $optionTitle);
        }
    }
}
