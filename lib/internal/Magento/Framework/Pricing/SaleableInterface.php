<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Pricing;

/**
 * Interface SaleableInterface
 *
 * @api
 * @since 2.0.0
 */
interface SaleableInterface
{
    /**
     * Returns PriceInfo container of saleable item
     *
     * @return \Magento\Framework\Pricing\PriceInfoInterface
     * @since 2.0.0
     */
    public function getPriceInfo();

    /**
     * Returns type identifier of saleable item
     *
     * @return array|string
     * @since 2.0.0
     */
    public function getTypeId();

    /**
     * Returns identifier of saleable item
     *
     * @return int
     * @since 2.0.0
     */
    public function getId();

    /**
     * Returns quantity of saleable item
     *
     * @return float
     * @since 2.0.0
     */
    public function getQty();
}
