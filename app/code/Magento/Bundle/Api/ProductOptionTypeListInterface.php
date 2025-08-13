<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Bundle\Api;

/**
 * Interface ProductOptionTypeListInterface
 * @api
 * @since 100.0.2
 */
interface ProductOptionTypeListInterface
{
    /**
     * Get all types for options for bundle products
     *
     * @return \Magento\Bundle\Api\Data\OptionTypeInterface[]
     */
    public function getItems();
}
