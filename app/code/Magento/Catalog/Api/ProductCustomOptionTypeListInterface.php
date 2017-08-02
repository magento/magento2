<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Api;

/**
 * @api
 * @since 2.0.0
 */
interface ProductCustomOptionTypeListInterface
{
    /**
     * Get custom option types
     *
     * @return \Magento\Catalog\Api\Data\ProductCustomOptionTypeInterface[]
     * @since 2.0.0
     */
    public function getItems();
}
