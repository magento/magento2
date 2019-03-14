<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Swagger\Api\Data;

use Magento\Framework\View\Element\Block\ArgumentInterface;

/**
 * Swagger Schema Type.
 *
 * @api
 * @since 100.2.4
 */
interface SchemaTypeInterface extends ArgumentInterface
{
    /**
     * Retrieve the available types of Swagger schema.
     *
     * @return string
     * @since 100.2.4
     */
    public function getCode();

    /**
     * Get the URL path for the Swagger schema.
     *
     * @param  string|null $store
     * @return string
     * @since 100.2.4
     */
    public function getSchemaUrlPath($store = null);
}
