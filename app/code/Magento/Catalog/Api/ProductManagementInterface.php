<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Catalog\Api;

/**
 * @api
 * @since 100.0.2
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
