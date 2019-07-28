<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Request;

use Magento\Framework\Api\SimpleDataObjectConverter;
use Magento\Framework\Session\SessionManagerInterface;

/**
 * Persist data to session.
 */
class DataPersistor implements DataPersistorInterface
{
    /**
     * @var SessionManagerInterface
     */
    protected $session;

    /**
     * @param SessionManagerInterface $session
     */
    public function __construct(
        SessionManagerInterface $session
    ) {
        $this->session = $session;
    }

    /**
     * Store data by key
     *
     * @param string $key
     * @param mixed $data
     * @return void
     */
    public function set($key, $data)
    {
        $method = 'set' . SimpleDataObjectConverter::snakeCaseToUpperCamelCase($key) . 'Data';
        call_user_func_array([$this->session, $method], [$data]);
    }

    /**
     * Retrieve data by key
     *
     * @param string $key
     * @return mixed
     */
    public function get($key)
    {
        $method = 'get' . SimpleDataObjectConverter::snakeCaseToUpperCamelCase($key) . 'Data';
        return call_user_func_array([$this->session, $method], []);
    }

    /**
     * Clear data by key
     *
     * @param string $key
     * @return void
     */
    public function clear($key)
    {
        $method = 'uns' . SimpleDataObjectConverter::snakeCaseToUpperCamelCase($key) . 'Data';
        call_user_func_array([$this->session, $method], []);
    }
}
