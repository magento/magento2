<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Api\Data;

interface AppliedTaxInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**#@+
     * Constants defined for keys of array, makes typos less likely
     */
    const KEY_TAX_RATE_KEY = 'tax_rate_key';

    const KEY_PERCENT = 'percent';

    const KEY_AMOUNT = 'amount';

    const KEY_RATES = 'rates';
    /**#@-*/

    /**
     * Get tax rate key
     *
     * @return string|null
     */
    public function getTaxRateKey();

    /**
     * Set tax rate key
     *
     * @param string $taxRateKey
     * @return $this
     */
    public function setTaxRateKey($taxRateKey);

    /**
     * Get percent
     *
     * @return float
     */
    public function getPercent();

    /**
     * Set percent
     *
     * @param float $percent
     * @return $this
     */
    public function setPercent($percent);

    /**
     * Get amount
     *
     * @return float
     */
    public function getAmount();

    /**
     * Get amount
     *
     * @param float $amount
     * @return $this
     */
    public function setAmount($amount);

    /**
     * Get rates
     *
     * @return \Magento\Tax\Api\Data\AppliedTaxRateInterface[]|null
     */
    public function getRates();

    /**
     * Set rates
     *
     * @param \Magento\Tax\Api\Data\AppliedTaxRateInterface[] $rates
     * @return $this
     */
    public function setRates(array $rates = null);
}
