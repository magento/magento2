<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Controller
 * @subpackage Plugins
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/** Zend_Controller_Plugin_Abstract */
#require_once 'Zend/Controller/Plugin/Abstract.php';

/** Zend_Registry */
#require_once 'Zend/Registry.php';

/**
 * Manage a stack of actions
 *
 * @uses       Zend_Controller_Plugin_Abstract
 * @category   Zend
 * @package    Zend_Controller
 * @subpackage Plugins
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: ActionStack.php 20096 2010-01-06 02:05:09Z bkarwin $
 */
class Zend_Controller_Plugin_ActionStack extends Zend_Controller_Plugin_Abstract
{
    /** @var Zend_Registry */
    protected $_registry;

    /**
     * Registry key under which actions are stored
     * @var string
     */
    protected $_registryKey = 'Zend_Controller_Plugin_ActionStack';

    /**
     * Valid keys for stack items
     * @var array
     */
    protected $_validKeys = array(
        'module',
        'controller',
        'action',
        'params'
    );

    /**
     * Flag to determine whether request parameters are cleared between actions, or whether new parameters
     * are added to existing request parameters.
     *
     * @var Bool
     */
    protected $_clearRequestParams = false;

    /**
     * Constructor
     *
     * @param  Zend_Registry $registry
     * @param  string $key
     * @return void
     */
    public function __construct(Zend_Registry $registry = null, $key = null)
    {
        if (null === $registry) {
            $registry = Zend_Registry::getInstance();
        }
        $this->setRegistry($registry);

        if (null !== $key) {
            $this->setRegistryKey($key);
        } else {
            $key = $this->getRegistryKey();
        }

        $registry[$key] = array();
    }

    /**
     * Set registry object
     *
     * @param  Zend_Registry $registry
     * @return Zend_Controller_Plugin_ActionStack
     */
    public function setRegistry(Zend_Registry $registry)
    {
        $this->_registry = $registry;
        return $this;
    }

    /**
     * Retrieve registry object
     *
     * @return Zend_Registry
     */
    public function getRegistry()
    {
        return $this->_registry;
    }

    /**
     * Retrieve registry key
     *
     * @return string
     */
    public function getRegistryKey()
    {
        return $this->_registryKey;
    }

    /**
     * Set registry key
     *
     * @param  string $key
     * @return Zend_Controller_Plugin_ActionStack
     */
    public function setRegistryKey($key)
    {
        $this->_registryKey = (string) $key;
        return $this;
    }

    /**
     *  Set clearRequestParams flag
     *
     *  @param  bool $clearRequestParams
     *  @return Zend_Controller_Plugin_ActionStack
     */
    public function setClearRequestParams($clearRequestParams)
    {
        $this->_clearRequestParams = (bool) $clearRequestParams;
        return $this;
    }

    /**
     * Retrieve clearRequestParams flag
     *
     * @return bool
     */
    public function getClearRequestParams()
    {
        return $this->_clearRequestParams;
    }

    /**
     * Retrieve action stack
     *
     * @return array
     */
    public function getStack()
    {
        $registry = $this->getRegistry();
        $stack    = $registry[$this->getRegistryKey()];
        return $stack;
    }

    /**
     * Save stack to registry
     *
     * @param  array $stack
     * @return Zend_Controller_Plugin_ActionStack
     */
    protected function _saveStack(array $stack)
    {
        $registry = $this->getRegistry();
        $registry[$this->getRegistryKey()] = $stack;
        return $this;
    }

    /**
     * Push an item onto the stack
     *
     * @param  Zend_Controller_Request_Abstract $next
     * @return Zend_Controller_Plugin_ActionStack
     */
    public function pushStack(Zend_Controller_Request_Abstract $next)
    {
        $stack = $this->getStack();
        array_push($stack, $next);
        return $this->_saveStack($stack);
    }

    /**
     * Pop an item off the action stack
     *
     * @return false|Zend_Controller_Request_Abstract
     */
    public function popStack()
    {
        $stack = $this->getStack();
        if (0 == count($stack)) {
            return false;
        }

        $next = array_pop($stack);
        $this->_saveStack($stack);

        if (!$next instanceof Zend_Controller_Request_Abstract) {
            #require_once 'Zend/Controller/Exception.php';
            throw new Zend_Controller_Exception('ArrayStack should only contain request objects');
        }
        $action = $next->getActionName();
        if (empty($action)) {
            return $this->popStack($stack);
        }

        $request    = $this->getRequest();
        $controller = $next->getControllerName();
        if (empty($controller)) {
            $next->setControllerName($request->getControllerName());
        }

        $module = $next->getModuleName();
        if (empty($module)) {
            $next->setModuleName($request->getModuleName());
        }

        return $next;
    }

    /**
     * postDispatch() plugin hook -- check for actions in stack, and dispatch if any found
     *
     * @param  Zend_Controller_Request_Abstract $request
     * @return void
     */
    public function postDispatch(Zend_Controller_Request_Abstract $request)
    {
        // Don't move on to next request if this is already an attempt to
        // forward
        if (!$request->isDispatched()) {
            return;
        }

        $this->setRequest($request);
        $stack = $this->getStack();
        if (empty($stack)) {
            return;
        }
        $next = $this->popStack();
        if (!$next) {
            return;
        }

        $this->forward($next);
    }

    /**
     * Forward request with next action
     *
     * @param  array $next
     * @return void
     */
    public function forward(Zend_Controller_Request_Abstract $next)
    {
        $request = $this->getRequest();
        if ($this->getClearRequestParams()) {
            $request->clearParams();
        }

        $request->setModuleName($next->getModuleName())
                ->setControllerName($next->getControllerName())
                ->setActionName($next->getActionName())
                ->setParams($next->getParams())
                ->setDispatched(false);
    }
}
