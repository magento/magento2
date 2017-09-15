<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Analytics\Model;

/**
 * Interface for Analytics Config.
 * @since 2.2.0
 */
interface ConfigInterface
{
    /**
     * Get config value by key.
     *
     * @param string|null $key
     * @param string|null $default
     * @return array
     * @since 2.2.0
     */
    public function get($key = null, $default = null);
}
