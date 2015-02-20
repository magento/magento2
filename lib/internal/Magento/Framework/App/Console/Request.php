<?php
/**
 * Console request
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Console;

class Request implements \Magento\Framework\App\RequestInterface
{
    /**
     * @var array
     */
    protected $params;

    /**
     * @param array $parameters
     */
    public function __construct(array $parameters = [])
    {
        $this->setParam($parameters);
    }

    /**
     * Initialize console parameters
     *
     * @param array $parameters
     * @return void
     */
    public function setParam($parameters)
    {
        $this->params = getopt(null, $parameters);
    }

    /**
     * Retrieve module name
     *
     * @return void
     */
    public function getModuleName()
    {
        return;
    }

    /**
     * Set Module name
     *
     * @param string $name
     * @return void
     */
    public function setModuleName($name)
    {
    }

    /**
     * Retrieve action name
     *
     * @return void
     */
    public function getActionName()
    {
        return;
    }

    /**
     * Set action name
     *
     * @param string $name
     * @return void
     */
    public function setActionName($name)
    {
    }

    /**
     * Retrieve param by key
     *
     * @param string $key
     * @param mixed $defaultValue
     * @return mixed
     */
    public function getParam($key, $defaultValue = null)
    {
        if (isset($this->params[$key])) {
            return $this->params[$key];
        }
        return $defaultValue;
    }

    /**
     * Retrieve all params as array
     *
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * Set params from key value array
     *
     * @param array $data
     * @return $this
     */
    public function setParams(array $data)
    {
        $this->params = $data;
        return $this;
    }

    /**
     * Stub to satisfy RequestInterface
     *
     * @param null|string $name
     * @param null|string $default
     * @return null|string|void
     */
    public function getCookie($name, $default)
    {
    }
}
