<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Api\Data;

/**
 * Interface CreditmemoCreationArgumentsInterface
 */
interface CreditmemoCreationArgumentsInterface
{
    /**
     * Gets the credit memo shipping amount.
     *
     * @return float|null Credit memo shipping amount.
     */
    public function getShippingAmount();

    /**
     * Gets the credit memo positive adjustment.
     *
     * @return float|null Credit memo positive adjustment.
     */
    public function getAdjustmentPositive();

    /**
     * Gets the credit memo negative adjustment.
     *
     * @return float|null Credit memo negative adjustment.
     */
    public function getAdjustmentNegative();

    /**
     * Sets the credit memo shipping amount.
     *
     * @param float $amount
     * @return $this
     */
    public function setShippingAmount($amount);

    /**
     * Sets the credit memo positive adjustment.
     *
     * @param float $amount
     * @return $this
     */
    public function setAdjustmentPositive($amount);

    /**
     * Sets the credit memo negative adjustment.
     *
     * @param float $amount
     * @return $this
     */
    public function setAdjustmentNegative($amount);

    /**
     * Gets existing extension attributes.
     *
     * @return \Magento\Sales\Api\Data\CreditmemoCreationArgumentsExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Sets extension attributes.
     *
     * @param \Magento\Sales\Api\Data\CreditmemoCreationArgumentsExtensionInterface $extensionAttributes
     *
     * @return $this
     */
    public function setExtensionAttributes(
        \Magento\Sales\Api\Data\CreditmemoCreationArgumentsExtensionInterface $extensionAttributes
    );
}
