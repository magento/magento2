<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Test\Unit\Helper;

use Magento\Framework\App\RequestInterface;

/**
 * Test helper for RequestInterface
 *
 * This helper implements RequestInterface to provide
 * test-specific functionality without dependency injection issues.
 *
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class RequestInterfaceTestHelper implements RequestInterface
{
    /**
     * @var string
     */
    private $actionName = 'DELETE';

    /**
     * Constructor that optionally accepts action name
     *
     * @param string|null $actionName
     */
    public function __construct($actionName = 'DELETE')
    {
        $this->actionName = $actionName;
    }

    /**
     * Get module name
     *
     * @return string|null
     */
    public function getModuleName()
    {
        return null;
    }

    /**
     * Set module name
     *
     * @param string $name
     * @return $this
     */
    public function setModuleName($name)
    {
        return $this;
    }

    /**
     * Get controller name
     *
     * @return string|null
     */
    public function getControllerName()
    {
        return null;
    }

    /**
     * Set controller name
     *
     * @param string $name
     * @return $this
     */
    public function setControllerName($name)
    {
        return $this;
    }

    /**
     * Get action name
     *
     * @return string
     */
    public function getActionName()
    {
        return $this->actionName;
    }

    /**
     * Set action name
     *
     * @param string $name
     * @return $this
     */
    public function setActionName($name)
    {
        $this->actionName = $name;
        return $this;
    }

    /**
     * Get param
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function getParam($key, $default = null)
    {
        return $default;
    }

    /**
     * Set param
     *
     * @param string $key
     * @param mixed $value
     * @return $this
     */
    public function setParam($key, $value)
    {
        return $this;
    }

    /**
     * Get params
     *
     * @return array
     */
    public function getParams()
    {
        return [];
    }

    /**
     * Set params
     *
     * @param array $params
     * @return $this
     */
    public function setParams(array $params)
    {
        return $this;
    }

    /**
     * Has param
     *
     * @param string $key
     * @return bool
     */
    public function has($key)
    {
        return false;
    }

    /**
     * Is secure
     *
     * @return bool
     */
    public function isSecure()
    {
        return false;
    }

    /**
     * Init forward
     *
     * @return void
     */
    public function initForward()
    {
        // Mock implementation
    }

    /**
     * Set dispatched
     *
     * @param bool $flag
     * @return void
     */
    public function setDispatched($flag = true)
    {
        // Mock implementation
    }

    /**
     * Get cookie
     *
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function getCookie($name, $default = null)
    {
        return $default;
    }
}
