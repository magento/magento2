<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Api;

/**
 * Interface for entities which can be extended with extension attributes.
 *
 * @api
 */
interface ExtensibleDataInterface
{
    /**
     * Key for extension attributes object
     */
    const EXTENSION_ATTRIBUTES_KEY = 'extension_attributes';
}
