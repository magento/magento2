<?php
/**
 * Data Model implementing the Address interface
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Directory\Model\Data;

/**
 * Class Currency Information
 *
 * @codeCoverageIgnore
 */
class CurrencyInformation extends \Magento\Framework\Api\AbstractExtensibleObject implements
    \Magento\Directory\Api\Data\CurrencyInformationInterface
{
    const KEY_BASE_CURRENCY_CODE = 'base_currency_code';
    const KEY_BASE_CURRENCY_SYMBOL = 'base_currency_symbol';
    const KEY_DEFAULT_DISPLAY_CURRENCY_CODE = 'default_display_currency_code';
    const KEY_DEFAULT_DISPLAY_CURRENCY_SYMBOL = 'default_display_currency_symbol';
    const KEY_AVAILABLE_CURRENCY_CODES = 'available_currency_codes';
    const KEY_EXCHANGE_RATES = 'exchange_rates';

    /**
     * @inheritDoc
     */
    public function getBaseCurrencyCode()
    {
        return $this->_get(self::KEY_BASE_CURRENCY_CODE);
    }

    /**
     * @inheritDoc
     */
    public function setBaseCurrencyCode($code)
    {
        return $this->setData(self::KEY_BASE_CURRENCY_CODE, $code);
    }

    /**
     * @inheritDoc
     */
    public function getBaseCurrencySymbol()
    {
        return $this->_get(self::KEY_BASE_CURRENCY_SYMBOL);
    }

    /**
     * @inheritDoc
     */
    public function setBaseCurrencySymbol($symbol)
    {
        return $this->setData(self::KEY_BASE_CURRENCY_SYMBOL, $symbol);
    }

    /**
     * @inheritDoc
     */
    public function getDefaultDisplayCurrencyCode()
    {
        return $this->_get(self::KEY_DEFAULT_DISPLAY_CURRENCY_CODE);
    }

    /**
     * @inheritDoc
     */
    public function setDefaultDisplayCurrencyCode($code)
    {
        return $this->setData(self::KEY_DEFAULT_DISPLAY_CURRENCY_CODE, $code);
    }

    /**
     * @inheritDoc
     */
    public function getDefaultDisplayCurrencySymbol()
    {
        return $this->_get(self::KEY_DEFAULT_DISPLAY_CURRENCY_SYMBOL);
    }

    /**
     * @inheritDoc
     */
    public function setDefaultDisplayCurrencySymbol($symbol)
    {
        return $this->setData(self::KEY_DEFAULT_DISPLAY_CURRENCY_SYMBOL, $symbol);
    }

    /**
     * @inheritDoc
     */
    public function getAvailableCurrencyCodes()
    {
        return $this->_get(self::KEY_AVAILABLE_CURRENCY_CODES);
    }

    /**
     * @inheritDoc
     */
    public function setAvailableCurrencyCodes(array $codes = null)
    {
        return $this->setData(self::KEY_AVAILABLE_CURRENCY_CODES, $codes);
    }

    /**
     * @inheritDoc
     */
    public function getExchangeRates()
    {
        return $this->_get(self::KEY_EXCHANGE_RATES);
    }

    /**
     * @inheritDoc
     */
    public function setExchangeRates(array $exchangeRates = null)
    {
        return $this->setData(self::KEY_EXCHANGE_RATES, $exchangeRates);
    }


    /**
     * @inheritDoc
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * @inheritDoc
     */
    public function setExtensionAttributes(
        \Magento\Directory\Api\Data\CurrencyInformationExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
