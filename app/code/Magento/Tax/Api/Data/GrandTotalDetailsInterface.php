<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Api\Data;

/**
 * Interface GrandTotalDetailsInterface
 * @api
 * @since 2.0.0
 */
interface GrandTotalDetailsInterface
{
    /**
     * Get tax amount value
     *
     * @return float|string
     * @since 2.0.0
     */
    public function getAmount();

    /**
     * @param string|float $amount
     * @return $this
     * @since 2.0.0
     */
    public function setAmount($amount);

    /**
     * Applied tax rates info
     *
     * @return \Magento\Tax\Api\Data\GrandTotalRatesInterface[]
     * @since 2.0.0
     */
    public function getRates();

    /**
     * @param \Magento\Tax\Api\Data\GrandTotalRatesInterface[] $rates
     * @return $this
     * @since 2.0.0
     */
    public function setRates($rates);

    /**
     * Details group identifier
     *
     * @return int
     * @since 2.0.0
     */
    public function getGroupId();

    /**
     * @param int $id
     * @return $this
     * @since 2.0.0
     */
    public function setGroupId($id);
}
