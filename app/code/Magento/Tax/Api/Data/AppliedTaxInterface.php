<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Api\Data;

/**
 * Applied tax interface.
 * @api
 * @since 2.0.0
 */
interface AppliedTaxInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**
     * Get tax rate key
     *
     * @return string|null
     * @since 2.0.0
     */
    public function getTaxRateKey();

    /**
     * Set tax rate key
     *
     * @param string $taxRateKey
     * @return $this
     * @since 2.0.0
     */
    public function setTaxRateKey($taxRateKey);

    /**
     * Get percent
     *
     * @return float
     * @since 2.0.0
     */
    public function getPercent();

    /**
     * Set percent
     *
     * @param float $percent
     * @return $this
     * @since 2.0.0
     */
    public function setPercent($percent);

    /**
     * Get amount
     *
     * @return float
     * @since 2.0.0
     */
    public function getAmount();

    /**
     * Get amount
     *
     * @param float $amount
     * @return $this
     * @since 2.0.0
     */
    public function setAmount($amount);

    /**
     * Get rates
     *
     * @return \Magento\Tax\Api\Data\AppliedTaxRateInterface[]|null
     * @since 2.0.0
     */
    public function getRates();

    /**
     * Set rates
     *
     * @param \Magento\Tax\Api\Data\AppliedTaxRateInterface[] $rates
     * @return $this
     * @since 2.0.0
     */
    public function setRates(array $rates = null);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Tax\Api\Data\AppliedTaxExtensionInterface|null
     * @since 2.0.0
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Tax\Api\Data\AppliedTaxExtensionInterface $extensionAttributes
     * @return $this
     * @since 2.0.0
     */
    public function setExtensionAttributes(\Magento\Tax\Api\Data\AppliedTaxExtensionInterface $extensionAttributes);
}
