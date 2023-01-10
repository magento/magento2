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
 * @since 100.0.2
 */
interface SaleableInterface
{
    /**
     * Returns PriceInfo container of saleable item
     *
     * @return \Magento\Framework\Pricing\PriceInfoInterface
     */
    public function getPriceInfo();

    /**
     * Returns type identifier of saleable item
     *
     * @return array|string
     */
    public function getTypeId();

    /**
     * Returns identifier of saleable item
     *
     * @return int
     */
    public function getId();

    /**
     * Returns quantity of saleable item
     *
     * @return float
     */
    public function getQty();
}
