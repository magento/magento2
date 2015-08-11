<?php
/**
 * Data Model implementing the Address interface
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Directory\Model\Data;
use Magento\Directory\Api\Data\CurrencyInformationInterface;

/**
 * Class Currency Information
 *
 * @codeCoverageIgnore
 */
class CurrencyInformation extends \Magento\Framework\Api\AbstractExtensibleObject implements
    \Magento\Directory\Api\Data\CurrencyInformationInterface
{
    const KEY_BASE_CURRENCY_CODE = 'base_currency_code';
    const KEY_DEFAULT_DISPLAY_CURRENCY_CODE = 'default_display_currency_code';
    const KEY_AVAILABLE_CURRENCYCODES = 'available_currency_codes';
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
    public function getDefaultDisplayCurrencyCode()
    {
        return $this->_get(self::KEY_BASE_CURRENCY_CODE);
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
    public function getAvailableCurrencyCodes()
    {
        return $this->_get(self::KEY_AVAILABLE_CURRENCYCODES);
    }

    /**
     * @inheritDoc
     */
    public function setAvailableCurrencyCodes(array $codes = null)
    {
        return $this->setData(self::KEY_AVAILABLE_CURRENCYCODES, $codes);
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
        \Magento\Directory\Api\Data\CurrencyInformationInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
