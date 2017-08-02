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
interface ProductManagementInterface
{
    /**
     * Provide the number of product count
     *
     * @param null|int $status
     * @return int
     * @since 2.0.0
     */
    public function getCount($status = null);
}
