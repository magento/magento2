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

    /**
     * Update data
     *
     * @param string[] $data
     * @return array
     */
    protected function update(array $data)
    {
        // get rid of null values
        $data = $this->filterArray($data);
        if (empty($data)) {
            return $this->data;
        }

        $new = array_replace_recursive($this->data, $data);
        return $new;
    }

    /**
     * Filter an array recursively
     *
     * @param array $data
     * @return array
     */
    private function filterArray(array $data)
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = $this->filterArray($value);
            } elseif (!isset($value)) {
                unset($data[$key]);
            }
        }
        return $data;
    }

}
