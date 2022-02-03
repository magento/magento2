<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Api;

/**
 * Get product type ID by product ID.
 *
 * @api
 */
interface GetProductTypeByIdInterface
{
    /**
     * Retrieve product type by its product ID
     *
     * @param int $productId
     * @return string
     */
    public function execute(int $productId);
}
