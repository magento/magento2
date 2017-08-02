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
 * @since 2.0.0
 */
interface ProductOptionTypeListInterface
{
    /**
     * Get all types for options for bundle products
     *
     * @return \Magento\Bundle\Api\Data\OptionTypeInterface[]
     * @since 2.0.0
     */
    public function getItems();
}
