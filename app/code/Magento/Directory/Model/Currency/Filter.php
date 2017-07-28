<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Currency filter
 */
namespace Magento\Directory\Model\Currency;

use Magento\Framework\Pricing\PriceCurrencyInterface;

/**
 * Class \Magento\Directory\Model\Currency\Filter
 *
 * @since 2.0.0
 */
class Filter implements \Zend_Filter_Interface
{
    /**
     * Rate value
     *
     * @var float
     * @since 2.0.0
     */
    protected $_rate;

    /**
     * Currency object
     *
     * @var \Magento\Framework\CurrencyInterface
     * @since 2.0.0
     */
    protected $_currency;

    /**
     * @var \Magento\Framework\Locale\FormatInterface
     * @since 2.0.0
     */
    protected $_localeFormat;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     * @since 2.0.0
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\Locale\CurrencyInterface
     * @since 2.0.0
     */
    protected $_localeCurrency;

    /**
     * Price currency
     *
     * @var PriceCurrencyInterface
     * @since 2.0.0
     */
    protected $priceCurrency;

    /**
     * @param \Magento\Framework\Locale\FormatInterface $localeFormat
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Locale\CurrencyInterface $localeCurrency
     * @param PriceCurrencyInterface $priceCurrency
     * @param string $code
     * @param int $rate
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\Locale\FormatInterface $localeFormat,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Locale\CurrencyInterface $localeCurrency,
        PriceCurrencyInterface $priceCurrency,
        $code,
        $rate = 1
    ) {
        $this->_localeFormat = $localeFormat;
        $this->_storeManager = $storeManager;
        $this->_currency = $localeCurrency->getCurrency($code);
        $this->priceCurrency = $priceCurrency;
        $this->_rate = $rate;
    }

    /**
     * Set filter rate
     *
     * @param float $rate
     * @return void
     * @since 2.0.0
     */
    public function setRate($rate)
    {
        $this->_rate = $rate;
    }

    /**
     * Filter value
     *
     * @param float $value
     * @return string
     * @since 2.0.0
     */
    public function filter($value)
    {
        $value = $this->_localeFormat->getNumber($value);
        $value = $this->priceCurrency->round($this->_rate * $value);
        $value = sprintf("%f", $value);
        return $this->_currency->toCurrency($value);
    }
}
