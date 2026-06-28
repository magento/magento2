<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Checkout\Model\Cart;

/**
 * Interface RequestInfoFilterInterface used by composite and leafs to implement filtering
 * @api
 * @since 100.1.2
 */
interface RequestInfoFilterInterface
{
    /**
     * Filters the data object by an array of parameters
     *
     * @param \Magento\Framework\DataObject $params
     * @return RequestInfoFilterInterface
     * @since 100.1.2
     */
    public function filter(\Magento\Framework\DataObject $params);
}
