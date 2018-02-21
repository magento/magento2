<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Swagger\Api;

/**
 * Swagger Schema Type.
 *
 * @api
 */
interface SchemaTypeInterface
{
    /**
     * Retrieve the available types of Swagger schema.
     *
     * @return string
     */
    public function getCode();

    /**
     * Get the URL path for the Swagger schema.
     *
     * @param  string|null $store
     * @return string
     */
    public function getSchemaUrlPath($store = null);
}
