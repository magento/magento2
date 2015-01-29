<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model\Cart;

/**
 * @codeCoverageIgnore
 */
class Currency extends \Magento\Framework\Model\AbstractExtensibleModel implements
    \Magento\Quote\Api\Data\CurrencyInterface
{
    /**
     * {@inheritdoc}
     */
    public function getGlobalCurrencyCode()
    {
        return $this->getData('global_currency_code');
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseCurrencyCode()
    {
        return $this->getData('base_currency_code');
    }

    /**
     * {@inheritdoc}
     */
    public function getStoreCurrencyCode()
    {
        return $this->getData('store_currency_code');
    }

    /**
     * {@inheritdoc}
     */
    public function getQuoteCurrencyCode()
    {
        return $this->getData('quote_currency_code');
    }

    /**
     * {@inheritdoc}
     */
    public function getStoreToBaseRate()
    {
        return $this->getData('store_to_base_rate');
    }

    /**
     * {@inheritdoc}
     */
    public function getStoreToQuoteRate()
    {
        return $this->getData('store_to_quote_rate');
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseToGlobalRate()
    {
        return $this->getData('base_to_global_rate');
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseToQuoteRate()
    {
        return $this->getData('base_to_quote_rate');
    }
}
