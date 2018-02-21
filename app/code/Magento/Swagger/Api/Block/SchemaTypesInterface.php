<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Swagger\Api\Block;

use Magento\Swagger\Api\SchemaTypeInterface;

/**
 * Swagger Schema Types.
 *
 * @api
 */
interface SchemaTypesInterface
{
    /**
     * Retrieve the available types of Swagger schema.
     *
     * @return SchemaTypeInterface[]
     */
    public function getTypes();

    /**
     * Retrieve the default schema type for Swagger.
     *
     * @return SchemaTypeInterface
     */
    public function getDefault();
}
