<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model\Cart;

/**
 * @codeCoverageIgnore
 * @since 2.0.0
 */
class Currency extends \Magento\Framework\Model\AbstractExtensibleModel implements
    \Magento\Quote\Api\Data\CurrencyInterface
{
    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getGlobalCurrencyCode()
    {
        return $this->getData(self::KEY_GLOBAL_CURRENCY_CODE);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getBaseCurrencyCode()
    {
        return $this->getData(self::KEY_BASE_CURRENCY_CODE);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getStoreCurrencyCode()
    {
        return $this->getData(self::KEY_STORE_CURRENCY_CODE);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getQuoteCurrencyCode()
    {
        return $this->getData(self::KEY_QUOTE_CURRENCY_CODE);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getStoreToBaseRate()
    {
        return $this->getData(self::KEY_STORE_TO_BASE_RATE);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getStoreToQuoteRate()
    {
        return $this->getData(self::KEY_STORE_TO_QUOTE_RATE);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getBaseToGlobalRate()
    {
        return $this->getData(self::KEY_BASE_TO_GLOBAL_RATE);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getBaseToQuoteRate()
    {
        return $this->getData(self::KEY_BASE_TO_QUOTE_RATE);
    }

    /**
     * Set global currency code
     *
     * @param string $globalCurrencyCode
     * @return $this
     * @since 2.0.0
     */
    public function setGlobalCurrencyCode($globalCurrencyCode)
    {
        return $this->setData(self::KEY_GLOBAL_CURRENCY_CODE, $globalCurrencyCode);
    }

    /**
     * Set base currency code
     *
     * @param string $baseCurrencyCode
     * @return $this
     * @since 2.0.0
     */
    public function setBaseCurrencyCode($baseCurrencyCode)
    {
        return $this->setData(self::KEY_BASE_CURRENCY_CODE, $baseCurrencyCode);
    }

    /**
     * Set store currency code
     *
     * @param string $storeCurrencyCode
     * @return $this
     * @since 2.0.0
     */
    public function setStoreCurrencyCode($storeCurrencyCode)
    {
        return $this->setData(self::KEY_STORE_CURRENCY_CODE, $storeCurrencyCode);
    }

    /**
     * Set quote currency code
     *
     * @param string $quoteCurrencyCode
     * @return $this
     * @since 2.0.0
     */
    public function setQuoteCurrencyCode($quoteCurrencyCode)
    {
        return $this->setData(self::KEY_QUOTE_CURRENCY_CODE, $quoteCurrencyCode);
    }

    /**
     * Set store currency to base currency rate
     *
     * @param float $storeToBaseRate
     * @return $this
     * @since 2.0.0
     */
    public function setStoreToBaseRate($storeToBaseRate)
    {
        return $this->setData(self::KEY_STORE_TO_BASE_RATE, $storeToBaseRate);
    }

    /**
     * Set store currency to quote currency rate
     *
     * @param float $storeToQuoteRate
     * @return $this
     * @since 2.0.0
     */
    public function setStoreToQuoteRate($storeToQuoteRate)
    {
        return $this->setData(self::KEY_STORE_TO_QUOTE_RATE, $storeToQuoteRate);
    }

    /**
     * Set base currency to global currency rate
     *
     * @param float $baseToGlobalRate
     * @return $this
     * @since 2.0.0
     */
    public function setBaseToGlobalRate($baseToGlobalRate)
    {
        return $this->setData(self::KEY_BASE_TO_GLOBAL_RATE, $baseToGlobalRate);
    }

    /**
     * Set base currency to quote currency rate
     *
     * @param float $baseToQuoteRate
     * @return $this
     * @since 2.0.0
     */
    public function setBaseToQuoteRate($baseToQuoteRate)
    {
        return $this->setData(self::KEY_BASE_TO_QUOTE_RATE, $baseToQuoteRate);
    }

    /**
     * {@inheritdoc}
     *
     * @return \Magento\Quote\Api\Data\CurrencyExtensionInterface|null
     * @since 2.0.0
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * {@inheritdoc}
     *
     * @param \Magento\Quote\Api\Data\CurrencyExtensionInterface $extensionAttributes
     * @return $this
     * @since 2.0.0
     */
    public function setExtensionAttributes(\Magento\Quote\Api\Data\CurrencyExtensionInterface $extensionAttributes)
    {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
