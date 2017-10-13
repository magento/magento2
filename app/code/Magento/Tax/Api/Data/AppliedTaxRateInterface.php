<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Api\Data;

/**
 * Applied tax rate interface.
 * @api
 * @since 100.0.2
 */
interface AppliedTaxRateInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**
     * Get code
     *
     * @return string|null
     */
    public function getCode();

    /**
     * Get Title
     *
     * @return string|null
     */
    public function getTitle();

    /**
     * Get Tax Percent
     *
     * @return float|null
     */
    public function getPercent();

    /**
     * Get tax amount value
     *
     * @return float|string
     */
    public function getAmount();

    /**
     * Set code
     *
     * @param string $code
     * @return $this
     */
    public function setCode($code);

    /**
     * Set Title
     *
     * @param string $title
     * @return $this
     */
    public function setTitle($title);

    /**
     * Set Tax Percent
     *
     * @param float $percent
     * @return $this
     */
    public function setPercent($percent);

    /**
     * @param string|float $amount
     * @return $this
     */
    public function setAmount($amount);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Tax\Api\Data\AppliedTaxRateExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Tax\Api\Data\AppliedTaxRateExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(\Magento\Tax\Api\Data\AppliedTaxRateExtensionInterface $extensionAttributes);
}
