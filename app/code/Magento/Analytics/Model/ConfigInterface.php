<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Analytics\Model;

/**
 * Interface for Analytics Config.
 *
 * @deprecated 103.0.2
 * @see \Magento\Framework\Config\DataInterface
 */
interface ConfigInterface
{
    /**
     * Get config value by key.
     *
     * @param string|null $key
     * @param string|null $default
     * @return array
     */
    public function get($key = null, $default = null);
}
