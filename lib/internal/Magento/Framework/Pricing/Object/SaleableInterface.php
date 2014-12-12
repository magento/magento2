<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Framework\Pricing\Object;

/**
 * Interface SaleableInterface
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
