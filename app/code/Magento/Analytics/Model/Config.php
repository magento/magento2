<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Analytics\Model;

use Magento\Framework\Config\DataInterface;

/**
 * Config of Analytics.
 * @since 2.2.0
 */
class Config implements ConfigInterface
{
    /**
     * @var DataInterface
     * @since 2.2.0
     */
    private $data;

    /**
     * @param DataInterface $data
     * @since 2.2.0
     */
    public function __construct(DataInterface $data)
    {
        $this->data = $data;
    }

    /**
     * Get config value by key.
     *
     * @param string|null $key
     * @param string|null $default
     * @return array
     * @since 2.2.0
     */
    public function get($key = null, $default = null)
    {
        return $this->data->get($key, $default);
    }
}
