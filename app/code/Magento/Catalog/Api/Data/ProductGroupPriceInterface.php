<?php
/**
 * Group Price
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Catalog\Api\Data;

interface ProductGroupPriceInterface
{
    /**
     * Retrieve customer group id
     *
     * @return int
     */
    public function getCustomerGroupId();

    /**
     * Retrieve price value
     *
     * @return float
     */
    public function getValue();
}
