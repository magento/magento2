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
namespace Magento\Catalog\Test\Block\Product;

use Mtf\Block\Block;
use Mtf\Client\Element\Locator;

/**
 * Class Price
 * This class is used to access the price related information from the storefront
 */
class Price extends Block
{
    /**
     * This member holds the class name of the old price block.
     *
     * @var string
     */
    protected $oldPriceClass = '.old-price';

    /**
     * This member holds the class name of the price block that contains the actual price value.
     *
     * @var string
     */
    protected $priceClass = '.price';

    /**
     * This member holds the class name of the regular price block.
     *
     * @var string
     */
    protected $regularPriceClass = '.price-final_price';

    /**
     * This member holds the class name of the special price block.
     *
     * @var string
     */
    protected $specialPriceClass = '.special-price';

    /**
     * Minimum Advertised Price
     *
     * @var string
     */
    protected $priceMap = '.old.price .price .price';

    /**
     * Actual Price
     *
     * @var string
     */
    protected $actualPrice = '.actual.price .price';

    /**
     * 'Add to Cart' button
     *
     * @var string
     */
    protected $addToCart = '.action.tocart';

    /**
     * 'Close' button
     *
     * @var string
     */
    protected $closeMap = '//section[@class="page main"]//div[@class="ui-dialog-buttonset"]//button';

    /**
     * Price from selector
     *
     * @var string
     */
    protected $priceFromSelector = 'p.price-from span.price';

    /**
     * Price to selector
     *
     * @var string
     */
    protected $priceToSelector = 'p.price-to span.price';

    /**
     * Getting prices
     *
     * @param string $currency
     * @return array
     */
    public function getPrice($currency = '$')
    {
        //@TODO it have to rewrite when will be possibility to divide it to different blocks(by product type)
        $prices = explode("\n", trim($this->_rootElement->getText()));
        if (count($prices) === 1) {
            $prices[0] = str_replace(',', '', $prices[0]);
            return ['price_regular_price' => trim($prices[0], $currency)];
        }
        return $this->formatPricesData($prices, $currency);
    }

    /**
     * Formatting data prices
     *
     * @param array $prices
     * @param string $currency
     * @return array
     */
    private function formatPricesData(array $prices, $currency = '$')
    {
        $formatted = [];
        foreach ($prices as $price) {
            list($name, $price) = explode($currency, $price);
            $name = str_replace(' ', '_', trim(preg_replace('#[^0-9a-z]+#i', ' ', strtolower($name)), ' '));
            $formatted['price_' . $name] = str_replace(',', '', $price);
        }
        return $formatted;
    }

    /**
     * This method returns the effective price represented by the block.
     * If a special price is presented, it uses that.
     * Otherwise, the regular price is used.
     *
     * @return string
     */
    public function getEffectivePrice()
    {
        // if a special price is available, then return that
        $priceElement = $this->_rootElement->find($this->specialPriceClass, Locator::SELECTOR_CSS);
        if (!$priceElement->isVisible()) {
            $priceElement = $this->_rootElement->find($this->regularPriceClass, Locator::SELECTOR_CSS);
            if (!$priceElement->isVisible()) {
                $priceElement = $this->_rootElement->find($this->oldPriceClass, Locator::SELECTOR_CSS);
            }
        }
        // return the actual value of the price
        return $priceElement->find($this->priceClass, Locator::SELECTOR_CSS)->getText();
    }

    /**
     * This method returns the regular price represented by the block.
     *
     * @return string
     */
    public function getRegularPrice()
    {
        // either return the old price (implies special price display or a regular price
        $priceElement = $this->_rootElement->find($this->oldPriceClass, Locator::SELECTOR_CSS);
        if (!$priceElement->isVisible()) {
            $priceElement = $this->_rootElement->find($this->regularPriceClass, Locator::SELECTOR_CSS);
        }
        // return the actual value of the price
        $element = $priceElement->find($this->priceClass, Locator::SELECTOR_CSS);
        $price = preg_replace('#[^\d\.\s]+#umis', '', $element->getText());
        return number_format(trim($price), 2);
    }

    /**
     * This method returns the special price represented by the block.
     *
     * @return string
     */
    public function getSpecialPrice()
    {
        $element = $this->_rootElement->find($this->specialPriceClass, Locator::SELECTOR_CSS)
            ->find($this->priceClass, Locator::SELECTOR_CSS);
        $price = preg_replace('#[^\d\.\s]+#umis', '', $element->getText());
        return number_format(trim($price), 2);
    }

    /**
     * This method returns if the regular price is visible.
     *
     * @return bool
     */
    public function isRegularPriceVisible()
    {
        return $this->_rootElement->find($this->regularPriceClass, Locator::SELECTOR_CSS)->isVisible();
    }

    /**
     * This method returns if the special price is visible.
     *
     * @return bool
     */
    public function isSpecialPriceVisible()
    {
        return $this->_rootElement->find($this->specialPriceClass, Locator::SELECTOR_CSS)->isVisible();
    }

    /**
     * Get Minimum Advertised Price value
     *
     * @return array|string
     */
    public function getOldPrice()
    {
        return $this->_rootElement->find($this->priceMap, Locator::SELECTOR_CSS)->getText();
    }

    /**
     * Get actual Price value on frontend
     *
     * @param string $currency
     *
     * @return array|float
     */
    public function getActualPrice($currency = '$')
    {
        //@TODO it have to rewrite when will be possibility to divide it to different blocks(by product type)
        $prices = explode("\n", trim($this->_rootElement->find($this->actualPrice, Locator::SELECTOR_CSS)->getText()));
        if (count($prices) == 1) {
            return floatval(trim($prices[0], $currency));
        }
        return $this->formatPricesData($prices, $currency);
    }

    /**
     * Add product to shopping cart from MAP Block
     *
     * @return void
     */
    public function addToCartFromMap()
    {
        $this->_rootElement->find($this->addToCart, Locator::SELECTOR_CSS)->click();
    }

    /**
     * Close MAP Block
     *
     * @return void
     */
    public function closeMapBlock()
    {
        $this->_rootElement->find($this->closeMap, Locator::SELECTOR_XPATH)->click();
        $this->waitForElementNotVisible($this->closeMap, Locator::SELECTOR_XPATH);
    }

    /**
     * Get price from
     *
     * @param string $currency
     * @return string
     */
    public function getPriceFrom($currency = '$')
    {
        return trim($this->_rootElement->find($this->priceFromSelector)->getText(), $currency);
    }

    /**
     * Get price to
     *
     * @param string $currency
     * @return string
     */
    public function getPriceTo($currency = '$')
    {
        return trim($this->_rootElement->find($this->priceToSelector)->getText(), $currency);
    }
}
