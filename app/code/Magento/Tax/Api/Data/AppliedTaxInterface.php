<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Api\Data;

/**
 * Applied tax interface.
 * @api
 */
interface AppliedTaxInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
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

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Tax\Api\Data\AppliedTaxExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Tax\Api\Data\AppliedTaxExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(\Magento\Tax\Api\Data\AppliedTaxExtensionInterface $extensionAttributes);
}
