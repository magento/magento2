<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Tax\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

interface OrderTaxInterface extends ExtensibleDataInterface
{
    public const TAX_ID = 'tax_id';
    public const ORDER_ID = 'order_id';
    public const CODE = 'code';
    public const TITLE = 'title';
    public const PERCENT = 'percent';
    public const AMOUNT = 'amount';
    public const BASE_AMOUNT = 'base_amount';
    public const BASE_REAL_AMOUNT = 'base_real_amount';
    public const PRIORITY = 'priority';
    public const POSITION = 'position';
    public const PROCESS = 'process';
    public const ITEMS = 'items';

    /**
     * Get tax ID
     *
     * @return int|null
     */
    public function getTaxId();

    /**
     * Set tax ID
     *
     * @param int $taxId
     * @return $this
     */
    public function setTaxId($taxId);

    /**
     * Get order ID
     *
     * @return int|null
     */
    public function getOrderId();

    /**
     * Set order ID
     *
     * @param int $orderId
     * @return $this
     */
    public function setOrderId($orderId);

    /**
     * Get code
     *
     * @return string|null
     */
    public function getCode();

    /**
     * Set code
     *
     * @param string $code
     * @return $this
     */
    public function setCode($code);

    /**
     * Get title
     *
     * @return string|null
     */
    public function getTitle();

    /**
     * Set title
     *
     * @param string $title
     * @return $this
     */
    public function setTitle($title);

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
     * Set amount
     *
     * @param float $amount
     * @return $this
     */
    public function setAmount($amount);

    /**
     * Get tax amount in base currency
     *
     * @return float
     */
    public function getBaseAmount();

    /**
     * Set tax amount in base currency
     *
     * @param float $baseAmount
     * @return $this
     */
    public function setBaseAmount($baseAmount);

    /**
     * Get real tax amount in base currency
     *
     * @return float
     */
    public function getBaseRealAmount();

    /**
     * Set real tax amount in base currency
     *
     * @param float $baseRealAmount
     * @return $this
     */
    public function setBaseRealAmount($baseRealAmount);

    /**
     * Get priority
     *
     * @return int
     */
    public function getPriority();

    /**
     * Set priority
     *
     * @param int $priority
     * @return $this
     */
    public function setPriority($priority);

    /**
     * Get position
     *
     * @return int
     */
    public function getPosition();

    /**
     * Set position
     *
     * @param int $position
     * @return $this
     */
    public function setPosition($position);

    /**
     * Get process
     *
     * @return int
     */
    public function getProcess();

    /**
     * Set process
     *
     * @param int $process
     * @return $this
     */
    public function setProcess($process);

    /**
     * Get extension attributes object
     *
     * @return \Magento\Tax\Api\Data\OrderTaxExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set extension attributes object
     *
     * @param \Magento\Tax\Api\Data\OrderTaxExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Magento\Tax\Api\Data\OrderTaxExtensionInterface $extensionAttributes
    );
}
