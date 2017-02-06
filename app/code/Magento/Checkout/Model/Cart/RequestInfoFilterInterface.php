<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Model\Cart;

/**
 * Interface RequestInfoFilterInterface used by composite and leafs to implement filtering
 */
interface RequestInfoFilterInterface
{
    /**
     * Filters the data object by an array of parameters
     *
     * @param \Magento\Framework\DataObject $params
     * @return RequestInfoFilterInterface
     */
    public function filter(\Magento\Framework\DataObject $params);
}
