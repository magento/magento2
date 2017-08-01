<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Api\Data;

/**
 * Interface OrderTaxDetailsAppliedTaxInterface
 * @api
 * @since 2.0.0
 */
interface OrderTaxDetailsAppliedTaxInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**
     * Get code
     *
     * @return string|null
     * @since 2.0.0
     */
    public function getCode();

    /**
     * Set code
     *
     * @param string $code
     * @return $this
     * @since 2.0.0
     */
    public function setCode($code);

    /**
     * Get Title
     *
     * @return string|null
     * @since 2.0.0
     */
    public function getTitle();

    /**
     * Set Title
     *
     * @param string $title
     * @return $this
     * @since 2.0.0
     */
    public function setTitle($title);

    /**
     * Get Tax Percent
     *
     * @return float|null
     * @since 2.0.0
     */
    public function getPercent();

    /**
     * Set Tax Percent
     *
     * @param float $percent
     * @return $this
     * @since 2.0.0
     */
    public function setPercent($percent);

    /**
     * Get tax amount
     *
     * @return float
     * @since 2.0.0
     */
    public function getAmount();

    /**
     * Set tax amount
     *
     * @param float $amount
     * @return $this
     * @since 2.0.0
     */
    public function setAmount($amount);

    /**
     * Get tax amount in base currency
     *
     * @return float
     * @since 2.0.0
     */
    public function getBaseAmount();

    /**
     * Set tax amount in base currency
     *
     * @param float $baseAmount
     * @return $this
     * @since 2.0.0
     */
    public function setBaseAmount($baseAmount);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Tax\Api\Data\OrderTaxDetailsAppliedTaxExtensionInterface|null
     * @since 2.0.0
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Tax\Api\Data\OrderTaxDetailsAppliedTaxExtensionInterface $extensionAttributes
     * @return $this
     * @since 2.0.0
     */
    public function setExtensionAttributes(
        \Magento\Tax\Api\Data\OrderTaxDetailsAppliedTaxExtensionInterface $extensionAttributes
    );
}
