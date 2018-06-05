<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Block\Product\Compare;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;
use Magento\Catalog\Test\Fixture\CatalogProductAttribute;
use Magento\Mtf\Client\Element\SimpleElement;

/**
 * Compare list product block.
 */
class ListCompare extends Block
{
    /**
     * Price displaying format.
     *
     * @var int
     */
    protected $priceFormat = 2;

    /**
     * Selector by product info.
     *
     * @var string
     */
    protected $productInfo = '//td[contains(@class, "cell product info")][%d]';

    /**
     * Selector by product attribute.
     *
     * @var string
     */
    protected $productAttribute = '//tr[th//*[normalize-space(text()) = "%s"]]';

    /**
     * Selector by name product.
     *
     * @var string
     */
    protected $nameSelector = './/*[contains(@class, "product-item-name")]/a';

    /**
     * Selector for search product via name.
     *
     * @var string
     */
    protected $productName = '[normalize-space(text()) = "%s"]';

    /**
     * Selector by price product.
     *
     * @var string
     */
    protected $priceSelector = './/div[contains(@class,"price-box")]';

    /**
     * Selector by sku product.
     *
     * @var string
     */
    protected $attributeSelector = './td[%d]/div';

    /**
     * Global attribute selector.
     *
     * @var string
     */
    protected $attribute = 'span.attribute';

    /**
     * Remove button selector.
     *
     * @var string
     */
    protected $removeButton = './/thead//td[%d]//a[contains(@class,"action delete")]';

    /**
     * Selector for empty message.
     *
     * @var string
     */
    protected $isEmpty = 'div.empty:last-child';

    /**
     * Selector for message block.
     *
     * @var string
     */
    protected $messageBlock = '#messages';

    /**
     * Get Product info.
     *
     * @param int $index
     * @param string $attributeKey
     * @param string $currency
     * @return string|array
     */
    public function getProductInfo($index, $attributeKey, $currency = ' $')
    {
        $infoBlock = $this->getCompareProductInfo($index);
        if ($attributeKey == 'price') {
            $price = $infoBlock->find($this->priceSelector, Locator::SELECTOR_XPATH)->getText();
            preg_match_all('`([a-z]+).*?([\d\.]+)`i', $price, $prices);
            if (!empty($prices[0])) {
                $resultPrice = [];
                foreach ($prices[1] as $key => $value) {
                    $resultPrice['price_' . lcfirst($value)] = $prices[2][$key];
                }
                return $resultPrice;
            }
            return trim($price, $currency);
        } else {
            return $infoBlock->find($this->nameSelector, Locator::SELECTOR_XPATH)->getText();
        }
    }

    /**
     * Get item compare product info.
     *
     * @param int $index
     * @return SimpleElement
     */
    protected function getCompareProductInfo($index)
    {
        return $this->_rootElement->find(sprintf($this->productInfo, $index), Locator::SELECTOR_XPATH);
    }

    /**
     * Get list of comparable product attributes.
     *
     * @return array
     */
    public function getComparableAttributes()
    {
        $rootElement = $this->_rootElement;
        $element = $this->nameSelector;
        $this->_rootElement->waitUntil(
            function () use ($rootElement, $element) {
                return $rootElement->find($element, Locator::SELECTOR_XPATH)->isVisible() ? true : null;
            }
        );

        $data = [];
        $attributes = $this->_rootElement->getElements($this->attribute);
        foreach ($attributes as $attribute) {
            $data[] = $attribute->getText();
        }
        return $data;
    }

    /**
     * Get item compare product attribute.
     *
     * @param string $key
     * @return SimpleElement
     */
    public function getCompareProductAttribute($key)
    {
        $rootElement = $this->_rootElement;
        $element = $this->nameSelector;
        $this->_rootElement->waitUntil(
            function () use ($rootElement, $element) {
                return $rootElement->find($element, Locator::SELECTOR_XPATH)->isVisible() ? true : null;
            }
        );
        return $this->_rootElement->find(sprintf($this->productAttribute, $key), Locator::SELECTOR_XPATH);
    }

    /**
     * Get item attribute.
     *
     * @param int $indexProduct
     * @param string $attributeKey
     * @return string
     */
    public function getProductAttribute($indexProduct, $attributeKey)
    {
        return trim(
            $this->getCompareProductAttribute($attributeKey)
                ->find(sprintf($this->attributeSelector, $indexProduct), Locator::SELECTOR_XPATH)->getText()
        );
    }

    /**
     * Remove product from compare product list.
     *
     * @param int $index [optional]
     * @return void
     */
    public function removeProduct($index = 1)
    {
        $this->_rootElement->find(sprintf($this->removeButton, $index), Locator::SELECTOR_XPATH)->click();
    }

    /**
     * Remove all products from compare product list.
     *
     * @return void
     */
    public function removeAllProducts()
    {
        $this->waitForElementVisible(sprintf($this->removeButton, 1), Locator::SELECTOR_XPATH);
        /** @var \Magento\Backend\Test\Block\Messages $messageBlock */
        $messageBlock = $this->blockFactory->create(
            'Magento\Backend\Test\Block\Messages',
            ['element' => $this->browser->find($this->messageBlock)]
        );

        while ($this->isProductVisible()) {
            $this->removeProduct();
            $messageBlock->waitSuccessMessage();
        }
    }

    /**
     * Visible product in compare product list.
     *
     * @param int $index [optional]
     * @return bool
     */
    public function isProductVisible($index = 1)
    {
        return $this->_rootElement->find(sprintf($this->removeButton, $index), Locator::SELECTOR_XPATH)->isVisible();
    }

    /**
     * Verify product is visible in compare product block.
     *
     * @param string $productName
     * @return bool
     */
    public function isProductVisibleInCompareBlock($productName = '')
    {
        $nameSelector = $this->nameSelector . sprintf($this->productName, $productName);
        return $this->_rootElement->find($nameSelector, Locator::SELECTOR_XPATH)->isVisible();
    }

    /**
     * Get empty message on compare product block.
     *
     * @return string
     */
    public function getEmptyMessage()
    {
        $rootElement = $this->_rootElement;
        $selector = $this->isEmpty;
        $this->_rootElement->waitUntil(
            function () use ($rootElement, $selector) {
                return $rootElement->find($selector)->isVisible() ? true : null;
            }
        );
        $isEmpty = $this->_rootElement->find($this->isEmpty);
        return $isEmpty->getText();
    }
}
