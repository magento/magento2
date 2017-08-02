<?php
/**
 * Data Model implementing the Address interface
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Directory\Model\Data;

/**
 * Class Currency Information
 *
 * @codeCoverageIgnore
 * @since 2.0.0
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
     * @since 2.0.0
     */
    public function getBaseCurrencyCode()
    {
        return $this->_get(self::KEY_BASE_CURRENCY_CODE);
    }

    /**
     * @inheritDoc
     * @since 2.0.0
     */
    public function setBaseCurrencyCode($code)
    {
        return $this->setData(self::KEY_BASE_CURRENCY_CODE, $code);
    }

    /**
     * @inheritDoc
     * @since 2.0.0
     */
    public function getBaseCurrencySymbol()
    {
        return $this->_get(self::KEY_BASE_CURRENCY_SYMBOL);
    }

    /**
     * @inheritDoc
     * @since 2.0.0
     */
    public function setBaseCurrencySymbol($symbol)
    {
        return $this->setData(self::KEY_BASE_CURRENCY_SYMBOL, $symbol);
    }

    /**
     * @inheritDoc
     * @since 2.0.0
     */
    public function getDefaultDisplayCurrencyCode()
    {
        return $this->_get(self::KEY_DEFAULT_DISPLAY_CURRENCY_CODE);
    }

    /**
     * @inheritDoc
     * @since 2.0.0
     */
    public function setDefaultDisplayCurrencyCode($code)
    {
        return $this->setData(self::KEY_DEFAULT_DISPLAY_CURRENCY_CODE, $code);
    }

    /**
     * @inheritDoc
     * @since 2.0.0
     */
    public function getDefaultDisplayCurrencySymbol()
    {
        return $this->_get(self::KEY_DEFAULT_DISPLAY_CURRENCY_SYMBOL);
    }

    /**
     * @inheritDoc
     * @since 2.0.0
     */
    public function setDefaultDisplayCurrencySymbol($symbol)
    {
        return $this->setData(self::KEY_DEFAULT_DISPLAY_CURRENCY_SYMBOL, $symbol);
    }

    /**
     * @inheritDoc
     * @since 2.0.0
     */
    public function getAvailableCurrencyCodes()
    {
        return $this->_get(self::KEY_AVAILABLE_CURRENCY_CODES);
    }

    /**
     * @inheritDoc
     * @since 2.0.0
     */
    public function setAvailableCurrencyCodes(array $codes = null)
    {
        return $this->setData(self::KEY_AVAILABLE_CURRENCY_CODES, $codes);
    }

    /**
     * @inheritDoc
     * @since 2.0.0
     */
    public function getExchangeRates()
    {
        return $this->_get(self::KEY_EXCHANGE_RATES);
    }

    /**
     * @inheritDoc
     * @since 2.0.0
     */
    public function setExchangeRates(array $exchangeRates = null)
    {
        return $this->setData(self::KEY_EXCHANGE_RATES, $exchangeRates);
    }

    /**
     * @inheritDoc
     * @since 2.0.0
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * @inheritDoc
     * @since 2.0.0
     */
    public function setExtensionAttributes(
        \Magento\Directory\Api\Data\CurrencyInformationExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
