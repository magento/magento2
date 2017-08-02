<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Api;

/**
 * Interface for entities which can be extended with extension attributes.
 *
 * @api
 * @since 2.0.0
 */
interface ExtensibleDataInterface
{
    /**
     * Key for extension attributes object
     */
    const EXTENSION_ATTRIBUTES_KEY = 'extension_attributes';
}
