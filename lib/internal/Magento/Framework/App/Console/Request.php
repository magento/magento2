<?php
/**
 * Console request
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Console;

/**
 * Class \Magento\Framework\App\Console\Request
 *
 * @since 2.0.0
 */
class Request implements \Magento\Framework\App\RequestInterface
{
    /**
     * @var array
     * @since 2.0.0
     */
    protected $params;

    /**
     * @param array $parameters
     * @since 2.0.0
     */
    public function __construct(array $parameters = [])
    {
        $data = getopt(null, $parameters);
        // It can happen that request comes from http (e.g. pub/cron.php), but it runs the console
        if ($data) {
            $this->setParams($data);
        } else {
            $this->setParams([]);
        }
    }

    /**
     * Retrieve module name
     *
     * @return void
     * @since 2.0.0
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
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.0.0
     */
    public function setModuleName($name)
    {
    }

    /**
     * Retrieve action name
     *
     * @return void
     * @since 2.0.0
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
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.0.0
     */
    public function getCookie($name, $default)
    {
    }

    /**
     * Stub to satisfy RequestInterface
     *
     * @return bool
     * @since 2.0.0
     */
    public function isSecure()
    {
        return false;
    }
}
