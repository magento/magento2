<?php
/**
 * Data Model implementing the Address interface
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Directory\Model\Data;

/**
 * Class Exchange Rate
 *
 * @codeCoverageIgnore
 * @since 2.0.0
 */
class ExchangeRate extends \Magento\Framework\Api\AbstractExtensibleObject implements
    \Magento\Directory\Api\Data\ExchangeRateInterface
{
    const KEY_CURRENCY_TO = 'currency_to';
    const KEY_RATE = 'rate';

    /**
     * @inheritDoc
     * @since 2.0.0
     */
    public function getCurrencyTo()
    {
        return $this->_get(self::KEY_CURRENCY_TO);
    }

    /**
     * @inheritDoc
     * @since 2.0.0
     */
    public function setCurrencyTo($code)
    {
        return $this->setData(self::KEY_CURRENCY_TO, $code);
    }

    /**
     * @inheritDoc
     * @since 2.0.0
     */
    public function getRate()
    {
        return $this->_get(self::KEY_RATE);
    }

    /**
     * @inheritDoc
     * @since 2.0.0
     */
    public function setRate($rate)
    {
        return $this->setData(self::KEY_RATE, $rate);
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
        \Magento\Directory\Api\Data\ExchangeRateExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
