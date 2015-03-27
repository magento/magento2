<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\Cache\Type;

/**
 * Deployment configuration for enabled cache types
 */
class CacheConfig
{
    /**
     * Deployment config key
     */
    const CACHE_KEY = 'cache_types';

    /**
     * Data
     *
     * @var array
     */
    protected $data = [];

    /**
     * Constructor
     *
     * @param array $data
     * @throws \InvalidArgumentException
     */
    public function __construct(array $data)
    {
        foreach ($data as $key => $value) {
            if (!preg_match('/^[a-z_]+$/i', $key)) {
                throw new \InvalidArgumentException("Invalid cache type key: {$key}");
            }
            $data[$key] = (int)$value;
        }
        $this->data = $data;
    }

    /**
     * Returns current key.
     *
     * @return string
     */
    public function getKey()
    {
        return self::CACHE_KEY;
    }

    /**
     * Return current Cache config.
     *
     * @return array|mixed
     */
    public function getData()
    {
        return $this->data;
    }
}
