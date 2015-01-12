<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Block\Product;

use Mtf\Block\Block;
use Mtf\Client\Element;
use Mtf\Client\Element\Locator;

/**
 * Class Price
 * This class is used to access the price related information from the storefront
 */
class Price extends Block
{
    /**
     * Mapping for different type of price
     *
     * @var array
     */
    protected $mapTypePrices = [
        'price' => [
            'selector' => '.price-container .price',
        ],
        'old_price' => [
            'selector' => '.old-price .price-wrapper',
        ],
        'map_old_price' => [
            'selector' => '.old-price .price',
        ],
        'actual_price' => [
            'selector' => '.actual-price .price',
        ],
        'special_price' => [
            'selector' => '.special-price .price',
        ],
        'final_price' => [
            'selector' => '.price-final_price .price',
        ],
        'price_from' => [
            'selector' => 'p.price-from .price',
        ],
        'price_to' => [
            'selector' => 'p.price-to .price',
        ],
    ];

    /**
     * 'Add to Cart' button
     *
     * @var string
     */
    protected $addToCart = '.action.tocart';

    /**
     * Minimum Advertised Price
     *
     * @var string
     */
    protected $priceMap = '.old.price .price .price';

    /**
     * 'Close' button
     *
     * @var string
     */
    protected $closeMap = '//main[@class="page-main"]//div[@class="ui-dialog-buttonset"]//button';

    /**
     * This method returns the price represented by the block
     *
     * @param string $currency
     * @return string
     */
    public function getPrice($currency = '$')
    {
        return $this->getTypePrice('price', $currency);
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
        return $this->getTypePrice('actual_price', $currency);
    }

    /**
     * This method returns the old price represented by the block
     *
     * @param string $currency
     * @return string
     */
    public function getOldPrice($currency = '$')
    {
        return $this->getTypePrice('old_price', $currency);
    }

    /**
     * This method returns the final price represented by the block
     *
     * @param string $currency
     * @return string
     */
    public function getFinalPrice($currency = '$')
    {
        return $this->getTypePrice('final_price', $currency);
    }

    /**
     * This method returns the old price represented by the block
     *
     * @param string $currency
     * @return string
     */
    public function getMapOldPrice($currency = '$')
    {
        return $this->getTypePrice('map_old_price', $currency);
    }

    /**
     * This method returns the special price represented by the block
     *
     * @param string $currency
     * @return string
     */
    public function getSpecialPrice($currency = '$')
    {
        return $this->getTypePrice('special_price', $currency);
    }

    /**
     * Price excluding tax
     *
     * @var string
     */
    protected $priceExcludingTax = '.price-excluding-tax span.price';

    /**
     * Price including tax
     *
     * @var string
     */
    protected $priceIncludingTax = '.price-including-tax span.price';

    /**
     * Get price from
     *
     * @param string $currency
     * @return string
     */
    public function getPriceFrom($currency = '$')
    {
        return $this->getTypePrice('price_from', $currency);
    }

    /**
     * Get price to
     *
     * @param string $currency
     * @return string
     */
    public function getPriceTo($currency = '$')
    {
        return $this->getTypePrice('price_to', $currency);
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
        $priceElement = $this->getTypePriceElement('special_price');
        if (!$priceElement->isVisible()) {
            $priceElement = $this->getTypePriceElement('price');
            if (!$priceElement->isVisible()) {
                $priceElement = $this->getTypePriceElement('old_price');
            }
        }
        // return the actual value of the price
        return $this->escape($priceElement->getText());
    }

    /**
     * This method returns the regular price represented by the block.
     *
     * @return string
     */
    public function getRegularPrice()
    {
        // either return the old price (implies special price display or a regular price
        $priceElement = $this->getTypePriceElement('old_price');
        if (!$priceElement->isVisible()) {
            $priceElement = $this->getTypePriceElement('price');
        }
        // return the actual value of the price
        $price = preg_replace('#[^\d\.\s]+#umis', '', $priceElement->getText());
        return number_format(trim($price), 2);
    }

    /**
     * This method returns if the regular price is visible.
     *
     * @return bool
     */
    public function isRegularPriceVisible()
    {
        return $this->getTypePriceElement('price')->isVisible();
    }

    /**
     * This method returns if the special price is visible.
     *
     * @return bool
     */
    public function isSpecialPriceVisible()
    {
        return $this->getTypePriceElement('special_price')->isVisible();
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
     * Get specify type price
     *
     * @param string $type
     * @param string $currency [optional]
     * @return string|null
     */
    protected function getTypePrice($type, $currency = '$')
    {
        $typePriceElement = $this->getTypePriceElement($type);
        return $typePriceElement->isVisible() ? $this->escape($typePriceElement->getText(), $currency) : null;
    }

    /**
     * Get specify type price element
     *
     * @param string $type
     * @return Element
     */
    protected function getTypePriceElement($type)
    {
        $mapTypePrice = $this->mapTypePrices[$type];
        return $this->_rootElement->find(
            $mapTypePrice['selector'],
            isset($mapTypePrice['strategy']) ? $mapTypePrice['strategy'] : Locator::SELECTOR_CSS
        );
    }

    /**
     * Escape currency and separator for price
     *
     * @param string $price
     * @param string $currency
     * @return string
     */
    protected function escape($price, $currency = '$')
    {
        return str_replace([',', $currency], '', $price);
    }

    /**
     * Get price excluding tax
     *
     * @param string $currency
     * @return string
     */
    public function getPriceExcludingTax($currency = '$')
    {
        return trim($this->_rootElement->find($this->priceExcludingTax)->getText(), $currency);
    }

    /**
     * Get price including tax
     *
     * @param string $currency
     * @return string
     */
    public function getPriceIncludingTax($currency = '$')
    {
        return trim($this->_rootElement->find($this->priceIncludingTax)->getText(), $currency);
    }
}
