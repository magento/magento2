<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App;

use Magento\Framework\ObjectManager\ResetAfterRequestInterface;

/**
 * Request processing flag that allows to stop request dispatching in action controller from an observer
 * Downside of this approach is temporal coupling and global communication.
 * Will be deprecated when Action Component is decoupled.
 *
 * Please use plugins to prevent action dispatching instead.
 *
 * @api
 * @since 100.0.2
 */
class ActionFlag implements ResetAfterRequestInterface
{
    /**
     * @var RequestInterface
     */
    protected $_request;

    /**
     * @var array
     */
    protected $_flags = [];

    /**
     * @param RequestInterface $request
     */
    public function __construct(\Magento\Framework\App\RequestInterface $request)
    {
        $this->_request = $request;
    }

    /**
     * Setting flag value
     *
     * @param string $action
     * @param string $flag
     * @param string $value
     * @return void
     */
    public function set($action, $flag, $value)
    {
        if ('' === $action) {
            $action = $this->_request->getActionName();
        }
        $this->_flags[$this->_getControllerKey()][$action][$flag] = $value;
    }

    /**
     * Retrieve flag value
     *
     * @param   string $action
     * @param   string $flag
     * @return  bool
     *
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function get($action, $flag = '')
    {
        if ('' === $action) {
            $action = $this->_request->getActionName();
        }
        if ('' === $flag) {
            return $this->_flags[$this->_getControllerKey()] ?? [];
        } elseif (isset($this->_flags[$this->_getControllerKey()][$action][$flag])) {
            return $this->_flags[$this->_getControllerKey()][$action][$flag];
        } else {
            return false;
        }
    }

    /**
     * Get controller key
     *
     * @return string
     */
    protected function _getControllerKey()
    {
        return $this->_request->getRouteName() . '_' . $this->_request->getControllerName();
    }

    /**
     * @inheritDoc
     */
    public function _resetState(): void
    {
        $this->_flags = [];
    }
}
