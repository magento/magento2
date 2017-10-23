<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
