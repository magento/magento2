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
 * Class Available Currency
 *
 * @codeCoverageIgnore
 */
class AvailableCurrency extends \Magento\Framework\Api\AbstractExtensibleObject implements
    \Magento\Directory\Api\Data\AvailableCurrencyInterface
{
    const KEY_CODE = 'code';
    const KEY_NAME = 'name';
    const KEY_VALUE = 'value';
    const KEY_SYMBOL = 'symbol';

    /**
     * @inheritDoc
     */
    public function getCode()
    {
        return $this->_get(self::KEY_CODE);
    }

    /**
     * @inheritDoc
     */
    public function setCode($code)
    {
        return $this->setData(self::KEY_CODE, $code);
    }

    /**
     * @inheritDoc
     */
    public function getValue()
    {
        return $this->_get(self::KEY_VALUE);
    }

    /**
     * @inheritDoc
     */
    public function setValue($value)
    {
        return $this->setData(self::KEY_VALUE, $value);
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return $this->_get(self::KEY_NAME);
    }

    /**
     * @inheritDoc
     */
    public function setName($name)
    {
        return $this->setData(self::KEY_NAME, $name);
    }

    /**
     * @inheritDoc
     */
    public function getSymbol()
    {
        return $this->_get(self::KEY_SYMBOL);
    }

    /**
     * @inheritDoc
     */
    public function setSymbol($symbol)
    {
        return $this->setData(self::KEY_SYMBOL, $symbol);
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
