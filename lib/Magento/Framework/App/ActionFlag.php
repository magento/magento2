<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Framework\App;

class ActionFlag
{
    /**
     * @var RequestInterface
     */
    protected $_request;

    /**
     * @var array
     */
    protected $_flags = array();

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
     * @param   string $action
     * @param   string $flag
     * @param   string $value
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
            return isset(
                $this->_flags[$this->_getControllerKey()]
            ) ? $this->_flags[$this->_getControllerKey()] : array();
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
        return $this->_request->getRequestedRouteName() . '_' . $this->_request->getRequestedControllerName();
    }
}
