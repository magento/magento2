<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Block\Product;

/**
 * This class is used to access the price related information from the storefront.
 */
class Price extends \Magento\Catalog\Test\Block\AbstractPriceBlock
{
    /**
     * Mapping for different type of price.
     *
     * @var array
     */
    protected $mapTypePrices = [
        'regular_price' => [
            'selector' => '.price-final_price .price',
        ],
        'actual_price' => [
            'selector' => '.actual-price .price',
        ],
        'special_price' => [
            'selector' => '.special-price .price',
        ],
        'old_price' => [
            'selector' => '.old-price .price-wrapper ',
        ],
        'price_from' => [
            'selector' => '.price-from .price',
        ],
        'price_to' => [
            'selector' => '.price-to .price',
        ],
        'price_excluding_tax' => [
            'selector' => '.price-excluding-tax .price'
        ],
        'price_including_tax' => [
            'selector' => '.price-including-tax .price'
        ],
        'old_price_from' => [
            'selector' => '.price-from .old-price .price-wrapper'
        ],
        'old_price_to' => [
            'selector' => '.price-to .old-price .price-wrapper'
        ],
    ];

    /**
     * This method returns the price represented by the block.
     *
     * @param string $currency
     * @return string|null
     */
    public function getPrice($currency = '$')
    {
        return $this->getTypePrice('regular_price', $currency);
    }

    /**
     * Get actual Price value on frontend.
     *
     * @param string $currency
     *
     * @return string|null
     */
    public function getActualPrice($currency = '$')
    {
        return $this->getTypePrice('actual_price', $currency);
    }

    /**
     * This method returns the special price represented by the block.
     *
     * @param string $currency
     * @return string|null
     */
    public function getSpecialPrice($currency = '$')
    {
        return $this->getTypePrice('special_price', $currency);
    }

    /**
     * This method returns the old price represented by the block.
     *
     * @param string $currency
     * @return string|null
     */
    public function getOldPrice($currency = '$')
    {
        return $this->getTypePrice('old_price', $currency);
    }

    /**
     * Get price from.
     *
     * @param string $currency
     * @return string|null
     */
    public function getPriceFrom($currency = '$')
    {
        return $this->getTypePrice('price_from', $currency);
    }

    /**
     * Get price to.
     *
     * @param string $currency
     * @return string|null
     */
    public function getPriceTo($currency = '$')
    {
        return $this->getTypePrice('price_to', $currency);
    }

    /**
     * Get currency symbol from price block on the product page.
     *
     * @return string
     */
    public function getCurrencySymbol()
    {
        $price = $this->getPrice('');
        preg_match('`(.*?)\d`', $price, $matches);
        return $matches[1];
    }

    /**
     * Get price excluding tax.
     *
     * @param string $currency
     * @return string|null
     */
    public function getPriceExcludingTax($currency = '$')
    {
        return $this->getTypePrice('price_excluding_tax', $currency);
    }

    /**
     * Get price including tax.
     *
     * @param string $currency
     * @return string|null
     */
    public function getPriceIncludingTax($currency = '$')
    {
        return $this->getTypePrice('price_including_tax', $currency);
    }

    /**
     * Get min old price
     *
     * @param string $currency
     * @return string|null
     */
    public function getOldPriceFrom($currency = '$')
    {
        return $this->getTypePrice('old_price_from', $currency);
    }

    /**
     * Get max old price
     *
     * @param string $currency
     * @return string|null
     */
    public function getOldPriceTo($currency = '$')
    {
        return $this->getTypePrice('old_price_to', $currency);
    }

    /**
     * This method returns if the regular price is visible.
     *
     * @return bool
     */
    public function isRegularPriceVisible()
    {
        return $this->getTypePriceElement('regular_price')->isVisible();
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
     * This method returns if the old price is visible.
     *
     * @return bool
     */
    public function isOldPriceVisible()
    {
        return $this->getTypePriceElement('old_price')->isVisible();
    }
}
