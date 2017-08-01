<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Model\Cart;

/**
 * Interface RequestInfoFilterInterface used by composite and leafs to implement filtering
 * @api
 * @since 2.2.0
 */
interface RequestInfoFilterInterface
{
    /**
     * Filters the data object by an array of parameters
     *
     * @param \Magento\Framework\DataObject $params
     * @return RequestInfoFilterInterface
     * @since 2.2.0
     */
    public function filter(\Magento\Framework\DataObject $params);
}
