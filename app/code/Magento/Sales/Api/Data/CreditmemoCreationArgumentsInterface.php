<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Api\Data;

/**
 * Interface CreditmemoCreationArgumentsInterface
 *
 * @api
 * @since 2.1.3
 */
interface CreditmemoCreationArgumentsInterface
{
    /**
     * Gets the credit memo shipping amount.
     *
     * @return float|null Credit memo shipping amount.
     * @since 2.1.3
     */
    public function getShippingAmount();

    /**
     * Gets the credit memo positive adjustment.
     *
     * @return float|null Credit memo positive adjustment.
     * @since 2.1.3
     */
    public function getAdjustmentPositive();

    /**
     * Gets the credit memo negative adjustment.
     *
     * @return float|null Credit memo negative adjustment.
     * @since 2.1.3
     */
    public function getAdjustmentNegative();

    /**
     * Sets the credit memo shipping amount.
     *
     * @param float $amount
     * @return $this
     * @since 2.1.3
     */
    public function setShippingAmount($amount);

    /**
     * Sets the credit memo positive adjustment.
     *
     * @param float $amount
     * @return $this
     * @since 2.1.3
     */
    public function setAdjustmentPositive($amount);

    /**
     * Sets the credit memo negative adjustment.
     *
     * @param float $amount
     * @return $this
     * @since 2.1.3
     */
    public function setAdjustmentNegative($amount);

    /**
     * Gets existing extension attributes.
     *
     * @return \Magento\Sales\Api\Data\CreditmemoCreationArgumentsExtensionInterface|null
     * @since 2.1.3
     */
    public function getExtensionAttributes();

    /**
     * Sets extension attributes.
     *
     * @param \Magento\Sales\Api\Data\CreditmemoCreationArgumentsExtensionInterface $extensionAttributes
     *
     * @return $this
     * @since 2.1.3
     */
    public function setExtensionAttributes(
        \Magento\Sales\Api\Data\CreditmemoCreationArgumentsExtensionInterface $extensionAttributes
    );
}
