<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model\Quote\Address\RateResult;

/**
 * Fields:
 * - carrier: carrier code
 * - carrierTitle: carrier title
 * - method: carrier method
 * - methodTitle: method title
 * - price: cost+handling
 * - cost: cost
 */
class Method extends AbstractResult
{
    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        array $data = []
    ) {
        $this->priceCurrency = $priceCurrency;
        parent::__construct($data);
    }

    /**
     * Round shipping carrier's method price
     *
     * @param string|float|int $price
     * @return $this
     */
    public function setPrice($price)
    {
        $this->setData('price', $this->priceCurrency->round($price));
        return $this;
    }
}
