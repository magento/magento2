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

namespace Magento\Catalog\Test\Block\Product\Compare;

use Mtf\Block\Block;
use Mtf\Client\Element;
use Mtf\Client\Element\Locator;

/**
 * Class ListCompare
 * Compare list product block
 */
class ListCompare extends Block
{
    /**
     * Selector by product info
     *
     * @var string
     */
    protected $productInfo = '//td[contains(@class, "cell product info")][%d]';

    /**
     * Selector by product attribute
     *
     * @var string
     */
    protected $productAttribute = '//tr[th//*[normalize-space(text()) = "%s"]]';

    /**
     * Selector by name product
     *
     * @var string
     */
    protected $nameSelector = './/*[contains(@class, "product-item-name")]/a';

    /**
     * Selector for search product via name
     *
     * @var string
     */
    protected $productName = '[normalize-space(text()) = "%s"]';

    /**
     * Selector by price product
     *
     * @var string
     */
    protected $priceSelector = './/div[contains(@class,"price-box")]';

    /**
     * Selector by sku product
     *
     * @var string
     */
    protected $attributeSelector = './td[%d]/div';

    /**
     * Remove button selector
     *
     * @var string
     */
    protected $removeButton = './/thead//td[%d]//a[contains(@class,"action delete")]';

    /**
     * Selector for empty message
     *
     * @var string
     */
    protected $isEmpty = 'p.empty';

    /**
     * Get product info
     *
     * @param int $index
     * @param string $attributeKey
     * @param string $currency
     * @return string
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
     * Get item compare product info
     *
     * @param int $index
     * @return Element
     */
    protected function getCompareProductInfo($index)
    {
        return $this->_rootElement->find(sprintf($this->productInfo, $index), Locator::SELECTOR_XPATH);
    }

    /**
     * Get item compare product attribute
     *
     * @param string $key
     * @return Element
     */
    protected function getCompareProductAttribute($key)
    {
        return $this->_rootElement->find(sprintf($this->productAttribute, $key), Locator::SELECTOR_XPATH);
    }

    /**
     * Get item attribute
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
     * Remove product from compare product list
     *
     * @param int $index [optional]
     * @return void
     */
    public function removeProduct($index = 1)
    {
        $this->_rootElement->find(sprintf($this->removeButton, $index), Locator::SELECTOR_XPATH)->click();
    }

    /**
     * Remove all products from compare product list
     *
     * @return void
     */
    public function removeAllProducts()
    {
        while ($this->isProductVisible()) {
            $this->removeProduct();
            $this->reinitRootElement();
        }
    }

    /**
     * Visible product in compare product list
     *
     * @param int $index [optional]
     * @return bool
     */
    public function isProductVisible($index = 1)
    {
        return $this->_rootElement->find(sprintf($this->removeButton, $index), Locator::SELECTOR_XPATH)->isVisible();
    }

    /**
     * Verify product is visible in compare product block
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
     * Get empty message on compare product block
     * Returns message absence of compared products or false, if the message isn't visible
     *
     * @return string|bool
     */
    public function getEmptyMessage()
    {
        $isEmpty = $this->_rootElement->find($this->isEmpty);
        if ($isEmpty->isVisible()) {
            return $isEmpty->getText();
        }
        return false;
    }
}
