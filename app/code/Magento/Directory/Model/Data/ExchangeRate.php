<?php
/**
 * Data Model implementing the Address interface
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Directory\Model\Data;

/**
 * Class Exchange Rate
 *
 * @codeCoverageIgnore
 */
class ExchangeRate extends \Magento\Framework\Api\AbstractExtensibleObject implements
    \Magento\Directory\Api\Data\ExchangeRateInterface
{
    const KEY_CURRENCY_TO = 'currency_to';
    const KEY_RATE = 'rate';
    private const KEY_EXCHANGE_RATES = 'exchange_rates';

    /**
     * @inheritDoc
     */
    public function getCurrencyTo()
    {
        return $this->_get(self::KEY_CURRENCY_TO);
    }

    /**
     * @inheritDoc
     */
    public function setCurrencyTo($code)
    {
        return $this->setData(self::KEY_CURRENCY_TO, $code);
    }

    /**
     * @inheritDoc
     */
    public function getRate()
    {
        return $this->_get(self::KEY_RATE);
    }

    /**
     * @inheritDoc
     */
    public function setRate($rate)
    {
        return $this->setData(self::KEY_RATE, $rate);
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
        \Magento\Directory\Api\Data\ExchangeRateExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
