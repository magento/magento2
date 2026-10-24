<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App\Request;

/**
 * @api
 * @since 100.1.0
 */
interface DataPersistorInterface
{
    /**
     * Store data by key
     *
     * @param string $key
     * @param mixed $data
     * @return void
     * @since 100.1.0
     */
    public function set($key, $data);

    /**
     * Retrieve data by key
     *
     * @param string $key
     * @return mixed
     * @since 100.1.0
     */
    public function get($key);

    /**
     * Clear data by key
     *
     * @param string $key
     * @return void
     * @since 100.1.0
     */
    public function clear($key);
}
