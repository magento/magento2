<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Api;

interface ProductOptionTypeListInterface
{
    /**
     * Get all types for options for bundle products
     *
     * @return \Magento\Bundle\Api\Data\OptionTypeInterface[]
     */
    public function getItems();
}
