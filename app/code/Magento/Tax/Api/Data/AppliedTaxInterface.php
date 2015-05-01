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
     * @api
     * @return string|null
     */
    public function getTaxRateKey();

    /**
     * Set tax rate key
     *
     * @api
     * @param string $taxRateKey
     * @return $this
     */
    public function setTaxRateKey($taxRateKey);

    /**
     * Get percent
     *
     * @api
     * @return float
     */
    public function getPercent();

    /**
     * Set percent
     *
     * @api
     * @param float $percent
     * @return $this
     */
    public function setPercent($percent);

    /**
     * Get amount
     *
     * @api
     * @return float
     */
    public function getAmount();

    /**
     * Get amount
     *
     * @api
     * @param float $amount
     * @return $this
     */
    public function setAmount($amount);

    /**
     * Get rates
     *
     * @api
     * @return \Magento\Tax\Api\Data\AppliedTaxRateInterface[]|null
     */
    public function getRates();

    /**
     * Set rates
     *
     * @api
     * @param \Magento\Tax\Api\Data\AppliedTaxRateInterface[] $rates
     * @return $this
     */
    public function setRates(array $rates = null);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @api
     * @return \Magento\Tax\Api\Data\AppliedTaxExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @api
     * @param \Magento\Tax\Api\Data\AppliedTaxExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(\Magento\Tax\Api\Data\AppliedTaxExtensionInterface $extensionAttributes);
}
