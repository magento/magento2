<?php
/**
 * Configuration validation schema locator
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Config;

/**
 * Config schema locator interface.
 *
 * @api
 * @since 2.0.0
 */
interface SchemaLocatorInterface
{
    /**
     * Get path to merged config schema
     *
     * @return string|null
     * @since 2.0.0
     */
    public function getSchema();

    /**
     * Get path to per file validation schema
     *
     * @return string|null
     * @since 2.0.0
     */
    public function getPerFileSchema();
}
