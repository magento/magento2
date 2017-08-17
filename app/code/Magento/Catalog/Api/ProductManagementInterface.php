<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Api;

/**
 * @api
 */
interface ProductManagementInterface
{
    /**
     * Provide the number of product count
     *
     * @param null|int $status
     * @return int
     */
    public function getCount($status = null);
}
