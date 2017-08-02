<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Request;

use Magento\Framework\Session\SessionManagerInterface;

/**
 * Class \Magento\Framework\App\Request\DataPersistor
 *
 * @since 2.1.0
 */
class DataPersistor implements DataPersistorInterface
{
    /**
     * @var SessionManagerInterface
     * @since 2.1.0
     */
    protected $session;

    /**
     * @param SessionManagerInterface $session
     * @since 2.1.0
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
     * @since 2.1.0
     */
    public function set($key, $data)
    {
        $method = 'set' . ucfirst($key) . 'Data';
        call_user_func_array([$this->session, $method], [$data]);
    }

    /**
     * Retrieve data by key
     *
     * @param string $key
     * @return mixed
     * @since 2.1.0
     */
    public function get($key)
    {
        $method = 'get' . ucfirst($key) . 'Data';
        return call_user_func_array([$this->session, $method], []);
    }

    /**
     * Clear data by key
     *
     * @param string $key
     * @return void
     * @since 2.1.0
     */
    public function clear($key)
    {
        $method = 'uns' . ucfirst($key) . 'Data';
        call_user_func_array([$this->session, $method], []);
    }
}
