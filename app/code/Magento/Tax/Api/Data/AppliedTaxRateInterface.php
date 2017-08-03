<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Api\Data;

/**
 * Applied tax rate interface.
 * @api
 * @since 2.0.0
 */
interface AppliedTaxRateInterface extends \Magento\Framework\Api\ExtensibleDataInterface
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
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Tax\Api\Data\AppliedTaxRateExtensionInterface|null
     * @since 2.0.0
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Tax\Api\Data\AppliedTaxRateExtensionInterface $extensionAttributes
     * @return $this
     * @since 2.0.0
     */
    public function setExtensionAttributes(\Magento\Tax\Api\Data\AppliedTaxRateExtensionInterface $extensionAttributes);
}
