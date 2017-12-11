<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Api\Data;

/**
 * Interface GrandTotalRatesInterface
 * @api
 * @since 100.0.2
 */
interface GrandTotalRatesInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**
     * Tax rate title
     *
     * @return string
     */
    public function getTitle();

    /**
     * Get tax percentage value
     *
     * @return string
     */
    public function getPercent();

    /**
     * Get tax amount value
     *
     * @return float|string
     */
    public function getAmount();

    /**
     * @param string $title
     * @return $this
     */
    public function setTitle($title);

    /**
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
     * @return \Magento\Tax\Api\Data\GrandTotalRatesExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Tax\Api\Data\GrandTotalRatesExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Magento\Tax\Api\Data\GrandTotalRatesExtensionInterface $extensionAttributes
    );
}
