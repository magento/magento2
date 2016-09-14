<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Api\Data;

/**
 * Interface CreditmemoCreationArgumentsInterface
 *
 * @api
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
     * Gets the items ids for return to stock.
     *
     * @return int[]|null
     */
    public function getReturnToStockItems();

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
     * Sets the items ids for return to stock.
     *
     * @param int[] $items
     * @return $this
     */
    public function setReturnToStockItems($items);

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
