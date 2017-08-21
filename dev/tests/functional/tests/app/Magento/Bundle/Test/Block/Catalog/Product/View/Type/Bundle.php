<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Bundle\Test\Block\Catalog\Product\View\Type;

use Magento\Bundle\Test\Fixture\BundleProduct;
use Magento\Catalog\Test\Page\Product\CatalogProductView;
use Magento\Bundle\Test\Block\Catalog\Product\View\Type\Option;
use Magento\Mtf\Client\Element\SimpleElement;
use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;
use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Mtf\Fixture\InjectableFixture;

/**
 * Class Bundle
 * Catalog bundle product info block
 */
class Bundle extends Block
{
    /**
     * Assigned product name.
     *
     * @var string
     */
    protected $assignedProductName = '.product-name';

    /**
     * Assigned product price.
     *
     * @var string
     */
    protected $assignedProductPrice = '.bundle-options-wrapper .price';

    /**
     * Selector for single option block
     *
     * @var string
     */
    protected $optionElement = './div[contains(@class,"option")][%d]';

    /**
     * Selector for title of option
     *
     * @var string
     */
    protected $title = './label/span';

    /**
     * Selector for required option
     *
     * @var string
     */
    protected $required = './self::*[contains(@class,"required")]';

    /**
     * Selector for select element of option
     *
     * @var string
     */
    protected $selectOption = './/div[@class="control"]/select';

    /**
     * Selector for label of option value element
     *
     * @var string
     */
    protected $optionLabel = './/div[@class="control"]//label[.//*[@class="product-name"]]';

    /**
     * Selector for option of select element
     *
     * @var string
     */
    protected $option = './/option[%d]';

    /**
     * Selector bundle option block for fill
     *
     * @var string
     */
    protected $bundleOptionBlock = './/div[label[span[contains(text(), "%s")]]]';

    /**
     *  Product fixture.
     *
     * @var FixtureInterface
     */
    private $product;

    /**
     * Option index.
     *
     * @var null|int
     */
    protected $optionIndex;

    /**
     * Fill bundle option on frontend add click "Add to cart" button
     *
     * @param BundleProduct $product
     * @param CatalogProductView $catalogProductView
     * @return void
     */
    public function addToCart(BundleProduct $product, CatalogProductView $catalogProductView)
    {
        $catalogProductView->getViewBlock()->fillOptions($product);
        $catalogProductView->getViewBlock()->clickAddToCart();
    }

    /**
     * Get product options
     *
     * @param FixtureInterface $product
     * @return array
     * @throws \Exception
     */
    public function getOptions(FixtureInterface $product)
    {
        /** @var BundleProduct  $product */
        $this->product = $product;
        $bundleSelections = $product->getBundleSelections();
        $bundleOptions = isset($bundleSelections['bundle_options']) ? $bundleSelections['bundle_options'] : [];

        $listFormOptions = $this->getListOptions();
        $formOptions = [];

        foreach ($bundleOptions as $index => $option) {
            $title = $option['title'];
            if (!isset($listFormOptions[$title])) {
                throw new \Exception("Can't find option: \"{$title}\"");
            }
            $this->optionIndex = $index;

            /** @var SimpleElement $optionElement */
            $optionElement = $listFormOptions[$title];
            $getTypeData = 'get' . $this->optionNameConvert($option['frontend_type']) . 'Data';

            $optionData = $this->$getTypeData($optionElement);
            $optionData['title'] = $title;
            $optionData['type'] = $option['frontend_type'];
            $optionData['is_require'] = $optionElement->find($this->required, Locator::SELECTOR_XPATH)->isVisible()
                ? 'Yes'
                : 'No';

            $formOptions[] = $optionData;
        }
        return $formOptions;
    }

    /**
     * Check if bundle option is visible.
     *
     * @param string $optionTitle
     * @return bool
     */
    public function isOptionVisible($optionTitle)
    {
        return isset($this->getListOptions()[$optionTitle]);
    }

    /**
     * Get list options
     *
     * @return array
     */
    protected function getListOptions()
    {
        $options = [];

        $count = 1;
        $optionElement = $this->_rootElement->find(sprintf($this->optionElement, $count), Locator::SELECTOR_XPATH);
        while ($optionElement->isVisible()) {
            $title = $optionElement->find($this->title, Locator::SELECTOR_XPATH)->getText();
            $options[$title] = $optionElement;

            ++$count;
            $optionElement = $this->_rootElement->find(sprintf($this->optionElement, $count), Locator::SELECTOR_XPATH);
        }
        return $options;
    }

    /**
     * Get data of "Drop-down" option
     *
     * @param SimpleElement $option
     * @return array
     */
    protected function getDropdownData(SimpleElement $option)
    {
        if ($this->isOneProductInStock($this->product)) {
            return ['options' => $this->getFlatTextData()];
        }
        $select = $option->find($this->selectOption, Locator::SELECTOR_XPATH, 'select');
        // Skip "Choose option ..."(option #1)
        return $this->getSelectOptionsData($select, 2);
    }

    /**
     * Get data of "Multiple select" option
     *
     * @param SimpleElement $option
     * @return array
     */
    protected function getMultipleselectData(SimpleElement $option)
    {
        $multiselect = $option->find($this->selectOption, Locator::SELECTOR_XPATH, 'multiselect');
        $data = $this->getSelectOptionsData($multiselect, 1);

        foreach ($data['options'] as $key => $option) {
            $option['title'] = trim(preg_replace('/^[\d]+ x/', '', $option['title']));
            $data['options'][$key] = $option;
        }

        return $data;
    }

    /**
     * Get data of "Radio buttons" option
     *
     * @param SimpleElement $option
     * @return array
     */
    protected function getRadiobuttonsData(SimpleElement $option)
    {
        $listOptions = [];
        $optionLabels = $option->getElements($this->optionLabel, Locator::SELECTOR_XPATH);

        foreach ($optionLabels as $optionLabel) {
            if ($optionLabel->isVisible()) {
                $listOptions[] = $this->parseOptionText($optionLabel->getText());
            }
        }

        return ['options' => $listOptions];
    }

    /**
     * Get data of "Checkbox" option
     *
     * @param SimpleElement $option
     * @return array
     */
    protected function getCheckboxData(SimpleElement $option)
    {
        $data =  $this->getRadiobuttonsData($option);

        foreach ($data['options'] as $key => $option) {
            $option['title'] = trim(preg_replace('/^[\d]+ x/', '', $option['title']));
            $data['options'][$key] = $option;
        }

        return $data;
    }

    /**
     * Get data from option of select and multiselect
     *
     * @param SimpleElement $element
     * @param int $firstOption
     * @return array
     */
    protected function getSelectOptionsData(SimpleElement $element, $firstOption = 1)
    {
        $listOptions = [];

        $count = $firstOption;
        $selectOption = $element->find(sprintf($this->option, $count), Locator::SELECTOR_XPATH);
        while ($selectOption->isVisible()) {
            $option = $this->parseOptionText($selectOption->getText());
            $selected = $selectOption->getAttribute('selected');
            if ($selected) {
                $option['selected'] = $selected;
            }
            $listOptions[] = $option;
            ++$count;
            $selectOption = $element->find(sprintf($this->option, $count), Locator::SELECTOR_XPATH);
        }

        return ['options' => $listOptions];
    }

    /**
     * Parse option text to title, price and optionally add selected attribute value.
     *
     * @param string $optionText
     * @return array
     */
    protected function parseOptionText($optionText)
    {
        preg_match('`^(.*?)\+ ?\$(\d.*?)$`sim', $optionText, $match);
        $optionPrice = isset($match[2]) ? str_replace(',', '', $match[2]) : 0;
        $optionTitle = isset($match[1]) ? trim($match[1]) : $optionText;
        $option = [
            'title' => $optionTitle,
            'price' => $optionPrice
        ];

        return $option;
    }

    /**
     * Fill bundle options
     *
     * @param array $bundleOptions
     * @return void
     */
    public function fillBundleOptions($bundleOptions)
    {
        foreach ($bundleOptions as $option) {
            $selector = sprintf($this->bundleOptionBlock, $option['title']);
            $useDefault = isset($option['use_default']) && strtolower($option['use_default']) == 'true' ? true : false;
            if (!$useDefault) {
                /** @var Option $optionBlock */
                $optionBlock = $this->blockFactory->create(
                    'Magento\Bundle\Test\Block\Catalog\Product\View\Type\Option\\'
                    . $this->optionNameConvert($option['frontend_type']),
                    ['element' => $this->_rootElement->find($selector, Locator::SELECTOR_XPATH)]
                );
                $optionBlock->fillOption($option['value']);
            }
        }
    }

    /**
     * Convert option name
     *
     * @param string $optionType
     * @return string
     */
    protected function optionNameConvert($optionType)
    {
        $trimmedOptionType = preg_replace('/[^a-zA-Z]/', '', $optionType);
        return ucfirst(strtolower($trimmedOptionType));
    }

    /**
     * Check count products with 'In Stock' status.
     *
     * @param BundleProduct $products
     * @return bool
     */
    private function isOneProductInStock(BundleProduct $products)
    {
        $result = [];
        $products = $products->getBundleSelections()['products'][$this->optionIndex];
        foreach ($products as $product) {
            $status = $product->getData()['quantity_and_stock_status']['is_in_stock'];
            if ($status == 'In Stock') {
                $result[] = $product;
            }
        }
        if (count($result) == 1) {
            return true;
        }
        return false;
    }

    /**
     * Return list options.
     *
     * @return array
     */
    private function getFlatTextData()
    {
        $productPrice = $this->_rootElement->find($this->assignedProductPrice)->getText();
        $productPrice = preg_replace("/[^0-9.,]/", '', $productPrice);
        $productName = $this->_rootElement->find($this->assignedProductName)->getText();
        $options[$productName] = [
            'title' => $productName,
            'price' => number_format($productPrice, 2)
        ];
        return $options;
    }
}
