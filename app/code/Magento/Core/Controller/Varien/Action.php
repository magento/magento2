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
 * @category    Magento
 * @package     Magento_Core
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Custom \Zend_Controller_Action class (formally)
 *
 * Allows dispatching before and after events for each controller action
 *
 * @category   Magento
 * @package    Magento_Core
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Core\Controller\Varien;

use Magento\App\Action\AbstractAction;

class Action extends \Magento\App\Action\AbstractAction
{
    const FLAG_NO_CHECK_INSTALLATION    = 'no-install-check';
    const FLAG_NO_DISPATCH              = 'no-dispatch';
    const FLAG_NO_PRE_DISPATCH          = 'no-preDispatch';
    const FLAG_NO_POST_DISPATCH         = 'no-postDispatch';
    const FLAG_NO_START_SESSION         = 'no-startSession';
    const FLAG_NO_DISPATCH_BLOCK_EVENT  = 'no-beforeGenerateLayoutBlocksDispatch';
    const FLAG_NO_COOKIES_REDIRECT      = 'no-cookies-redirect';

    const PARAM_NAME_SUCCESS_URL        = 'success_url';
    const PARAM_NAME_ERROR_URL          = 'error_url';
    const PARAM_NAME_REFERER_URL        = 'referer_url';
    const PARAM_NAME_BASE64_URL         = 'r64';
    const PARAM_NAME_URL_ENCODED        = 'uenc';

    /**
     * @var \Magento\ObjectManager
     */
    protected $_objectManager;

    /**
     * Real module name (like 'Magento_Module')
     *
     * @var string
     */
    protected $_realModuleName;

    /**
     * Action flags
     *
     * for example used to disable rendering default layout
     *
     * @var array
     */
    protected $_flags = array();

    /**
     * Action list where need check enabled cookie
     *
     * @var array
     */
    protected $_cookieCheckActions = array();

    /**
     * Namespace for session.
     * Should be defined for proper working session.
     *
     * @var string
     */
    protected $_sessionNamespace;

    /**
     * Whether layout is loaded
     *
     * @see self::loadLayout()
     * @var bool
     */
    protected $_isLayoutLoaded = false;

    /**
     * Title parts to be rendered in the page head title
     *
     * @see self::_title()
     * @var array
     */
    protected $_titles = array();

    /**
     * Whether the default title should be removed
     *
     * @see self::_title()
     * @var bool
     */
    protected $_removeDefaultTitle = false;

    /**
     * @var \Magento\App\FrontController
     */
    protected $_frontController = null;

    /**
     * @var \Magento\View\LayoutInterface
     */
    protected $_layout;

    /**
     * @var \Magento\Event\ManagerInterface
     */
    protected $_eventManager;

    /**
     * Should inherited page be rendered
     *
     * @var bool
     */
    protected $_isRenderInherited;

    /**
     * @param \Magento\Core\Controller\Varien\Action\Context $context
     */
    public function __construct(\Magento\Core\Controller\Varien\Action\Context $context)
    {
        parent::__construct($context->getRequest(), $context->getResponse());

        $this->_objectManager   = $context->getObjectManager();
        $this->_frontController = $context->getFrontController();
        $this->_layout          = $context->getLayout();
        $this->_eventManager    = $context->getEventManager();
        $this->_isRenderInherited = $context->isRenderInherited();
        $this->_frontController->setAction($this);

        $this->_construct();
    }

    protected function _construct()
    {
    }

    /**
     * Check is controller method exist
     *
     * @param string $action
     * @return bool
     */
    public function hasAction($action)
    {
        return method_exists($this, $this->getActionMethodName($action));
    }

    /**
     * Retrieve flag value
     *
     * @param   string $action
     * @param   string $flag
     * @return  bool
     */
    public function getFlag($action, $flag = '')
    {
        if ('' === $action) {
            $action = $this->getRequest()->getActionName();
        }
        if ('' === $flag) {
            return $this->_flags;
        } elseif (isset($this->_flags[$action][$flag])) {
            return $this->_flags[$action][$flag];
        } else {
            return false;
        }
    }

    /**
     * Setting flag value
     *
     * @param   string $action
     * @param   string $flag
     * @param   string $value
     * @return  \Magento\Core\Controller\Varien\Action
     */
    public function setFlag($action, $flag, $value)
    {
        if ('' === $action) {
            $action = $this->getRequest()->getActionName();
        }
        $this->_flags[$action][$flag] = $value;
        return $this;
    }

    /**
     * Retrieve current layout object
     *
     * @return \Magento\View\LayoutInterface
     */
    public function getLayout()
    {
        /** @var \Magento\Config\ScopeInterface $configScope */
        $configScope = $this->_objectManager->get('Magento\Config\ScopeInterface');
        $this->_layout->setArea($configScope->getCurrentScope());
        return $this->_layout;
    }

    /**
     * Load layout by handles(s)
     *
     * @param   string|null|bool $handles
     * @param   bool $generateBlocks
     * @param   bool $generateXml
     * @return  $this
     * @throws  \RuntimeException
     */
    public function loadLayout($handles = null, $generateBlocks = true, $generateXml = true)
    {
        if ($this->_isLayoutLoaded) {
            throw new \RuntimeException('Layout must be loaded only once.');
        }
        // if handles were specified in arguments load them first
        if (false !== $handles && '' !== $handles) {
            $this->getLayout()->getUpdate()->addHandle($handles ? $handles : 'default');
        }

        // add default layout handles for this action
        $this->addActionLayoutHandles();

        $this->loadLayoutUpdates();

        if (!$generateXml) {
            return $this;
        }
        $this->generateLayoutXml();

        if (!$generateBlocks) {
            return $this;
        }
        $this->generateLayoutBlocks();
        $this->_isLayoutLoaded = true;

        return $this;
    }

    /**
     * Retrieve the default layout handle name for the current action
     *
     * @return string
     */
    public function getDefaultLayoutHandle()
    {
        return strtolower($this->getFullActionName());
    }

    /**
     * Add layout handle by full controller action name
     *
     * @return \Magento\Core\Controller\Varien\Action
     */
    public function addActionLayoutHandles()
    {
        if (!$this->_isRenderInherited || !$this->addPageLayoutHandles()) {
            $this->getLayout()->getUpdate()->addHandle($this->getDefaultLayoutHandle());
        }
        return $this;
    }

    /**
     * Add layout updates handles associated with the action page
     *
     * @param array $parameters page parameters
     * @return bool
     */
    public function addPageLayoutHandles(array $parameters = array())
    {
        $handle = $this->getDefaultLayoutHandle();
        $pageHandles = array($handle);
        foreach ($parameters as $key => $value) {
            $pageHandles[] = $handle . '_' . $key . '_' . $value;
        }
        return $this->getLayout()->getUpdate()->addPageHandles(array_reverse($pageHandles));
    }

    /**
     * Load layout updates
     *
     * @return $this
     */
    public function loadLayoutUpdates()
    {
        \Magento\Profiler::start('LAYOUT');

        // dispatch event for adding handles to layout update
        $this->_eventManager->dispatch(
            'controller_action_layout_load_before',
            array('action' => $this, 'layout' => $this->getLayout())
        );

        // load layout updates by specified handles
        \Magento\Profiler::start('layout_load');
        $this->getLayout()->getUpdate()->load();
        \Magento\Profiler::stop('layout_load');

        \Magento\Profiler::stop('LAYOUT');
        return $this;
    }

    /**
     * Generate layout xml
     *
     * @return $this
     */
    public function generateLayoutXml()
    {
        \Magento\Profiler::start('LAYOUT');

        // dispatch event for adding text layouts
        if (!$this->getFlag('', self::FLAG_NO_DISPATCH_BLOCK_EVENT)) {
            $this->_eventManager->dispatch(
                'controller_action_layout_generate_xml_before',
                array('action' => $this, 'layout' => $this->getLayout())
            );
        }

        // generate xml from collected text updates
        \Magento\Profiler::start('layout_generate_xml');
        $this->getLayout()->generateXml();
        \Magento\Profiler::stop('layout_generate_xml');

        \Magento\Profiler::stop('LAYOUT');
        return $this;
    }

    /**
     * Generate layout blocks
     *
     * @return $this
     */
    public function generateLayoutBlocks()
    {
        \Magento\Profiler::start('LAYOUT');

        // dispatch event for adding xml layout elements
        if (!$this->getFlag('', self::FLAG_NO_DISPATCH_BLOCK_EVENT)) {
            $this->_eventManager->dispatch(
                'controller_action_layout_generate_blocks_before',
                array('action' => $this, 'layout' => $this->getLayout())
            );
        }

        // generate blocks from xml layout
        \Magento\Profiler::start('layout_generate_blocks');
        $this->getLayout()->generateElements();
        \Magento\Profiler::stop('layout_generate_blocks');

        if (!$this->getFlag('', self::FLAG_NO_DISPATCH_BLOCK_EVENT)) {
            $this->_eventManager->dispatch(
                'controller_action_layout_generate_blocks_after',
                array('action' => $this, 'layout' => $this->getLayout())
            );
        }

        \Magento\Profiler::stop('LAYOUT');
        return $this;
    }

    /**
     * Rendering layout
     *
     * @param   string $output
     * @return  \Magento\Core\Controller\Varien\Action
     */
    public function renderLayout($output = '')
    {
        if ($this->getFlag('', 'no-renderLayout')) {
            return;
        }

        \Magento\Profiler::start('LAYOUT');

        $this->_renderTitles();

        \Magento\Profiler::start('layout_render');

        if ('' !== $output) {
            $this->getLayout()->addOutputElement($output);
        }

        $this->_eventManager->dispatch('controller_action_layout_render_before');
        $this->_eventManager->dispatch('controller_action_layout_render_before_' . $this->getFullActionName());

        $output = $this->getLayout()->getOutput();
        $this->_objectManager->get('Magento\Core\Model\Translate')->processResponseBody($output);
        $this->getResponse()->appendBody($output);
        \Magento\Profiler::stop('layout_render');

        \Magento\Profiler::stop('LAYOUT');
        return $this;
    }

    /**
     * Dispatch action
     *
     * @param string $action
     */
    public function dispatch($action)
    {
        $this->getRequest()->setDispatched(true);
        try {
            $actionMethodName = $this->getActionMethodName($action);
            if (!method_exists($this, $actionMethodName)) {
                $actionMethodName = 'norouteAction';
            }

            $profilerKey = 'CONTROLLER_ACTION:' . $this->getFullActionName();
            \Magento\Profiler::start($profilerKey);

            \Magento\Profiler::start('predispatch');
            $this->preDispatch();
            \Magento\Profiler::stop('predispatch');

            if ($this->getRequest()->isDispatched()) {
                /**
                 * preDispatch() didn't change the action, so we can continue
                 */
                if (!$this->getFlag('', self::FLAG_NO_DISPATCH)) {
                    \Magento\Profiler::start('action_body');
                    $this->$actionMethodName();
                    \Magento\Profiler::stop('action_body');

                    \Magento\Profiler::start('postdispatch');
                    $this->postDispatch();
                    \Magento\Profiler::stop('postdispatch');
                }
            }

            \Magento\Profiler::stop($profilerKey);
        } catch (\Magento\App\Action\Exception $e) {
            // set prepared flags
            foreach ($e->getResultFlags() as $flagData) {
                list($action, $flag, $value) = $flagData;
                $this->setFlag($action, $flag, $value);
            }
            // call forward, redirect or an action
            list($method, $parameters) = $e->getResultCallback();
            switch ($method) {
                case \Magento\App\Action\Exception::RESULT_REDIRECT:
                    list($path, $arguments) = $parameters;
                    $this->_redirect($path, $arguments);
                    break;
                case \Magento\App\Action\Exception::RESULT_FORWARD:
                    list($action, $controller, $module, $params) = $parameters;
                    $this->_forward($action, $controller, $module, $params);
                    break;
                default:
                    $actionMethodName = $this->getActionMethodName($method);
                    $this->getRequest()->setActionName($method);
                    $this->$actionMethodName($method);
                    break;
            }
        }
    }

    /**
     * Retrieve action method name
     *
     * @param string $action
     * @return string
     */
    public function getActionMethodName($action)
    {
        return $action . 'Action';
    }

    /**
     * Start session if it is not restricted
     *
     * @return \Magento\Core\Controller\Varien\Action
     */
    protected function _startSession()
    {
        if (!$this->getFlag('', self::FLAG_NO_START_SESSION)) {
            $checkCookie = in_array($this->getRequest()->getActionName(), $this->_cookieCheckActions)
                && !$this->getRequest()->getParam('nocookie', false);
            $cookies = $this->_objectManager->get('Magento\Core\Model\Cookie')->get();
            /** @var $session \Magento\Core\Model\Session */
            $session = $this->_objectManager->get('Magento\Core\Model\Session')->start();

            if (empty($cookies)) {
                if ($session->getCookieShouldBeReceived()) {
                    $this->setFlag('', self::FLAG_NO_COOKIES_REDIRECT, true);
                    $session->unsCookieShouldBeReceived();
                    $session->setSkipSessionIdFlag(true);
                } elseif ($checkCookie) {
                    if (isset($_GET[$session->getSessionIdQueryParam()])
                        && $this->_objectManager->get('Magento\Core\Model\App')->getUseSessionInUrl()
                        && $this->_sessionNamespace != \Magento\Backend\Controller\AbstractAction::SESSION_NAMESPACE
                    ) {
                        $session->setCookieShouldBeReceived(true);
                    } else {
                        $this->setFlag('', self::FLAG_NO_COOKIES_REDIRECT, true);
                    }
                }
            }
        }
        return $this;
    }

    /**
     * Initialize area and design
     *
     * @return \Magento\Core\Controller\Varien\Action
     */
    protected function _initDesign()
    {
        $area = $this->_objectManager->get('Magento\Core\Model\App')->getArea($this->getLayout()->getArea());
        $area->load();
        $area->detectDesign($this->getRequest());
        return $this;
    }

    /**
     * Dispatch event before action
     *
     * @return null
     */
    public function preDispatch()
    {
        if (!$this->getFlag('', self::FLAG_NO_CHECK_INSTALLATION)) {
            if (!$this->_objectManager->get('Magento\App\State')->isInstalled()) {
                $this->setFlag('', self::FLAG_NO_DISPATCH, true);
                $this->_redirect('install');
                return;
            }
        }

        // Prohibit disabled store actions
        $storeManager = $this->_objectManager->get('Magento\Core\Model\StoreManager');
        if ($this->_objectManager->get('Magento\App\State') && !$storeManager->getStore()->getIsActive()) {
            $this->_objectManager->get('Magento\Core\Model\StoreManager')->throwStoreException();
        }

        if ($this->_rewrite()) {
            return;
        }

        // Start session
        $this->_startSession();

        // Load area and initialize design depend on loaded area
        $this->_initDesign();

        if ($this->getFlag('', self::FLAG_NO_COOKIES_REDIRECT)
            && $this->_objectManager->get('Magento\Core\Model\Store\Config')->getConfig('web/browser_capabilities/cookies')
        ) {
            $this->_forward('noCookies', 'index', 'core');
            return;
        }

        if ($this->getFlag('', self::FLAG_NO_PRE_DISPATCH)) {
            return;
        }

        $this->_firePreDispatchEvents();
    }

    /**
     * Fire predispatch events, execute extra logic after predispatch
     */
    protected function _firePreDispatchEvents()
    {
        $this->_eventManager->dispatch('controller_action_predispatch', array('controller_action' => $this));
        $this->_eventManager->dispatch('controller_action_predispatch_' . $this->getRequest()->getRouteName(),
            array('controller_action' => $this));
        $this->_eventManager->dispatch('controller_action_predispatch_' . $this->getFullActionName(),
            array('controller_action' => $this));
    }

    /**
     * Dispatches event after action
     */
    public function postDispatch()
    {
        if ($this->getFlag('', self::FLAG_NO_POST_DISPATCH)) {
            return;
        }

        $this->_eventManager->dispatch(
            'controller_action_postdispatch_' . $this->getFullActionName(),
            array('controller_action' => $this)
        );
        $this->_eventManager->dispatch(
            'controller_action_postdispatch_' . $this->getRequest()->getRouteName(),
            array('controller_action' => $this)
        );
        $this->_eventManager->dispatch('controller_action_postdispatch', array('controller_action' => $this));
    }

    /**
     * No route action
     *
     * @param null $coreRoute
     */
    public function norouteAction($coreRoute = null)
    {
        $status = $this->getRequest()->getParam('__status__');
        if (!$status instanceof \Magento\Object) {
            $status = new \Magento\Object();
        }

        $this->_eventManager->dispatch('controller_action_noroute', array('action' => $this, 'status' => $status));

        if ($status->getLoaded() !== true
            || $status->getForwarded() === true
            || !is_null($coreRoute)
        ) {
            $this->loadLayout(array('default', 'noRoute'));
            $this->renderLayout();
        } else {
            $status->setForwarded(true);
            $this->_forward(
                $status->getForwardAction(),
                $status->getForwardController(),
                $status->getForwardModule(),
                array('__status__' => $status));
        }
    }

    /**
     * No cookies action
     */
    public function noCookiesAction()
    {
        $redirect = new \Magento\Object();
        $this->_eventManager->dispatch('controller_action_nocookies', array(
            'action'    => $this,
            'redirect'  => $redirect
        ));

        $url = $redirect->getRedirectUrl();
        if ($url) {
            $this->_redirectUrl($url);
        } elseif ($redirect->getRedirect()) {
            $this->_redirect($redirect->getPath(), $redirect->getArguments());
        } else {
            $this->loadLayout(array('default', 'noCookie'));
            $this->renderLayout();
        }

        $this->getRequest()->setDispatched(true);
    }

    /**
     * Throw control to different action (control and module if was specified).
     *
     * @param string $action
     * @param string|null $controller
     * @param string|null $module
     * @param array|null $params
     */
    protected function _forward($action, $controller = null, $module = null, array $params = null)
    {
        $request = $this->getRequest();

        $request->initForward();

        if (isset($params)) {
            $request->setParams($params);
        }

        if (isset($controller)) {
            $request->setControllerName($controller);

            // Module should only be reset if controller has been specified
            if (isset($module)) {
                $request->setModuleName($module);
            }
        }

        $request->setActionName($action)
            ->setDispatched(false);
    }

    /**
     * Initializing layout messages by message storage(s), loading and adding messages to layout messages block
     *
     * @param string|array $messagesStorage
     * @throws \Magento\Core\Exception
     * @return \Magento\Core\Controller\Varien\Action
     */
    protected function _initLayoutMessages($messagesStorage)
    {
        if (!is_array($messagesStorage)) {
            $messagesStorage = array($messagesStorage);
        }
        foreach ($messagesStorage as $storageName) {
            $storage = $this->_objectManager->get($storageName);
            if ($storage) {
                $block = $this->getLayout()->getMessagesBlock();
                $block->addMessages($storage->getMessages(true));
                $block->setEscapeMessageFlag($storage->getEscapeMessages(true));
                $block->addStorageType($storageName);
            } else {
                throw new \Magento\Core\Exception(
                     __('Invalid messages storage "%1" for layout messages initialization', (string)$storageName)
                );
            }
        }
        return $this;
    }

    /**
     * Initializing layout messages by message storage(s), loading and adding messages to layout messages block
     *
     * @param string|array $messagesStorage
     * @return \Magento\Core\Controller\Varien\Action
     */
    public function initLayoutMessages($messagesStorage)
    {
        return $this->_initLayoutMessages($messagesStorage);
    }

    /**
     * Set redirect url into response
     *
     * @param   string $url
     * @return  \Magento\Core\Controller\Varien\Action
     */
    protected function _redirectUrl($url)
    {
        $this->getResponse()->setRedirect($url);
        return $this;
    }

    /**
     * Set redirect into response
     *
     * @param   string $path
     * @param   array $arguments
     * @return  \Magento\Core\Controller\Varien\Action
     */
    protected function _redirect($path, $arguments = array())
    {
        return $this->setRedirectWithCookieCheck($path, $arguments);
    }

    /**
     * Set redirect into response with session id in URL if it is enabled.
     * It allows to distinguish primordial request from browser with cookies disabled.
     *
     * @param   string $path
     * @param   array $arguments
     * @return  \Magento\Core\Controller\Varien\Action
     */
    public function setRedirectWithCookieCheck($path, array $arguments = array())
    {
        /** @var $session \Magento\Core\Model\Session */
        $session = $this->_objectManager->get('Magento\Core\Model\Session');
        if ($session->getCookieShouldBeReceived()
            && $this->_objectManager->get('Magento\Core\Model\App')->getUseSessionInUrl()
            && $this->_sessionNamespace != \Magento\Backend\Controller\AbstractAction::SESSION_NAMESPACE
        ) {
            $arguments += array('_query' => array(
                $session->getSessionIdQueryParam() => $session->getSessionId()
            ));
        }
        $this->getResponse()->setRedirect(
            $this->_objectManager->create('Magento\Core\Model\Url')->getUrl($path, $arguments)
        );
        return $this;
    }


    /**
     * Redirect to success page
     *
     * @param string $defaultUrl
     * @return \Magento\Core\Controller\Varien\Action
     */
    protected function _redirectSuccess($defaultUrl)
    {
        $successUrl = $this->getRequest()->getParam(self::PARAM_NAME_SUCCESS_URL);
        if (empty($successUrl)) {
            $successUrl = $defaultUrl;
        }
        if (!$this->_isUrlInternal($successUrl)) {
            $successUrl = $this->_objectManager->get('Magento\Core\Model\StoreManager')->getStore()->getBaseUrl();
        }
        $this->getResponse()->setRedirect($successUrl);
        return $this;
    }

    /**
     * Redirect to error page
     *
     * @param string $defaultUrl
     * @return  \Magento\Core\Controller\Varien\Action
     */
    protected function _redirectError($defaultUrl)
    {
        $errorUrl = $this->getRequest()->getParam(self::PARAM_NAME_ERROR_URL);
        if (empty($errorUrl)) {
            $errorUrl = $defaultUrl;
        }
        if (!$this->_isUrlInternal($errorUrl)) {
            $errorUrl = $this->_objectManager->get('Magento\Core\Model\StoreManager')->getStore()->getBaseUrl();
        }
        $this->getResponse()->setRedirect($errorUrl);
        return $this;
    }

    /**
     * Set referer url for redirect in response
     *
     * @param   string $defaultUrl
     * @return  \Magento\Core\Controller\Varien\Action
     */
    protected function _redirectReferer($defaultUrl=null)
    {

        $refererUrl = $this->_getRefererUrl();
        if (empty($refererUrl)) {
            $refererUrl = empty($defaultUrl)
                ? $this->_objectManager->get('Magento\Core\Model\StoreManager')->getBaseUrl()
                : $defaultUrl;
        }

        $this->getResponse()->setRedirect($refererUrl);
        return $this;
    }

    /**
     * Identify referer url via all accepted methods (HTTP_REFERER, regular or base64-encoded request param)
     *
     * @return string
     */
    protected function _getRefererUrl()
    {
        $refererUrl = $this->getRequest()->getServer('HTTP_REFERER');
        $url = $this->getRequest()->getParam(self::PARAM_NAME_REFERER_URL);
        if ($url) {
            $refererUrl = $url;
        }
        $url = $this->getRequest()->getParam(self::PARAM_NAME_BASE64_URL);
        if ($url) {
            $refererUrl = $this->_objectManager->get('Magento\Core\Helper\Data')->urlDecode($url);
        }
        $url = $this->getRequest()->getParam(self::PARAM_NAME_URL_ENCODED);
        if ($url) {
            $refererUrl = $this->_objectManager->get('Magento\Core\Helper\Data')->urlDecode($url);
        }

        if (!$this->_isUrlInternal($refererUrl)) {
            $refererUrl = $this->_objectManager->get('Magento\Core\Model\StoreManagerInterface')->getStore()->getBaseUrl();
        }
        return $refererUrl;
    }

    /**
     * Check url to be used as internal
     *
     * @param   string $url
     * @return  bool
     */
    protected function _isUrlInternal($url)
    {
        if (strpos($url, 'http') !== false) {
            /**
             * Url must start from base secure or base unsecure url
             */
            /** @var $store \Magento\Core\Model\StoreManagerInterface */
            $store = $this->_objectManager->get('Magento\Core\Model\StoreManagerInterface')->getStore();
            if ((strpos($url, $store->getBaseUrl()) === 0)
                || (strpos($url, $store->getBaseUrl(\Magento\Core\Model\Store::URL_TYPE_LINK, true)) === 0)
            ) {
                return true;
            }
        }
        return false;
    }

    /**
     * Support for controllers rewrites
     *
     * Example of configuration:
     * <global>
     *   <routers>
     *     <core_module>
     *       <rewrite>
     *         <core_controller>
     *           <to>new_route/new_controller</to>
     *           <override_actions>true</override_actions>
     *           <actions>
     *             <core_action><to>new_module/new_controller/new_action</core_action>
     *           </actions>
     *         <core_controller>
     *       </rewrite>
     *     </core_module>
     *   </routers>
     * </global>
     *
     * This will override:
     * 1. core_module/core_controller/core_action to new_module/new_controller/new_action
     * 2. all other actions of core_module/core_controller to new_module/new_controller
     *
     * @return boolean true if rewrite happened
     */
    protected function _rewrite()
    {
        $route = $this->getRequest()->getRouteName();
        $controller = $this->getRequest()->getControllerName();
        $action = $this->getRequest()->getActionName();

        $rewrite = $this->_objectManager->get('Magento\Core\Model\Config')->getNode('global/routers/' . $route . '/rewrite/' . $controller);
        if (!$rewrite) {
            return false;
        }

        if (!($rewrite->actions && $rewrite->actions->$action) || $rewrite->is('override_actions')) {
            $rewriteTo = explode('/', (string)$rewrite->to);
            if (sizeof($rewriteTo) !== 2 || empty($rewriteTo[0]) || empty($rewriteTo[1])) {
                return false;
            }
            $rewriteTo[2] = $action;
        } else {
            $rewriteTo = explode('/', (string)$rewrite->actions->$action->to);
            if (sizeof($rewriteTo) !== 3 || empty($rewriteTo[0]) || empty($rewriteTo[1]) || empty($rewriteTo[2])) {
                return false;
            }
        }

        $this->_forward(
            $rewriteTo[2] === '*' ? $action : $rewriteTo[2],
            $rewriteTo[1] === '*' ? $controller : $rewriteTo[1],
            $rewriteTo[0] === '*' ? $route : $rewriteTo[0]
        );

        return true;
    }

    /**
     * Validate Form Key
     *
     * @return bool
     */
    protected function _validateFormKey()
    {
        if (!($formKey = $this->getRequest()->getParam('form_key', null))
            || $formKey != $this->_objectManager->get('Magento\Core\Model\Session')->getFormKey()
        ) {
            return false;
        }
        return true;
    }

    /**
     * Add an extra title to the end
     *
     * Usage examples:
     * $this->_title('foo')->_title('bar');
     * => bar / foo / <default title>
     *
     * @see self::_renderTitles()
     * @param string $text
     * @return \Magento\Core\Controller\Varien\Action
     */
    protected function _title($text)
    {
        $this->_titles[] = $text;
        return $this;
    }

    /**
     * Prepare titles in the 'head' layout block
     * Supposed to work only in actions where layout is rendered
     * Falls back to the default logic if there are no titles eventually
     *
     * @see self::loadLayout()
     * @see self::renderLayout()
     */
    protected function _renderTitles()
    {
        if ($this->_isLayoutLoaded && $this->_titles) {
            $titleBlock = $this->getLayout()->getBlock('head');
            if ($titleBlock) {
                if (!$this->_removeDefaultTitle) {
                    $title = trim($titleBlock->getTitle());
                    if ($title) {
                        array_unshift($this->_titles, $title);
                    }
                }
                $titleBlock->setTitle(array_reverse($this->_titles));
            }
        }
    }

    /**
     * Convert dates in array from localized to internal format
     *
     * @param   array $array
     * @param   array $dateFields
     * @return  array
     */
    protected function _filterDates($array, $dateFields)
    {
        if (empty($dateFields)) {
            return $array;
        }
        $filterInput = new \Zend_Filter_LocalizedToNormalized(array(
            'date_format' => $this->_objectManager->get('Magento\Core\Model\LocaleInterface')
                ->getDateFormat(\Magento\Core\Model\LocaleInterface::FORMAT_TYPE_SHORT)
        ));
        $filterInternal = new \Zend_Filter_NormalizedToLocalized(array(
            'date_format' => \Magento\Date::DATE_INTERNAL_FORMAT
        ));

        foreach ($dateFields as $dateField) {
            if (array_key_exists($dateField, $array) && !empty($dateField)) {
                $array[$dateField] = $filterInput->filter($array[$dateField]);
                $array[$dateField] = $filterInternal->filter($array[$dateField]);
            }
        }
        return $array;
    }

    /**
     * Convert dates with time in array from localized to internal format
     *
     * @param   array $array
     * @param   array $dateFields
     * @return  array
     */
    protected function _filterDateTime($array, $dateFields)
    {
        if (empty($dateFields)) {
            return $array;
        }
        $filterInput = new \Zend_Filter_LocalizedToNormalized(array(
            'date_format' => $this->_objectManager->get('Magento\Core\Model\LocaleInterface')
                ->getDateTimeFormat(\Magento\Core\Model\LocaleInterface::FORMAT_TYPE_SHORT)
        ));
        $filterInternal = new \Zend_Filter_NormalizedToLocalized(array(
            'date_format' => \Magento\Date::DATETIME_INTERNAL_FORMAT
        ));

        foreach ($dateFields as $dateField) {
            if (array_key_exists($dateField, $array) && !empty($dateField)) {
                $array[$dateField] = $filterInput->filter($array[$dateField]);
                $array[$dateField] = $filterInternal->filter($array[$dateField]);
            }
        }
        return $array;
    }

    /**
     * Declare headers and content file in response for file download
     *
     * @param string $fileName
     * @param string|array $content set to null to avoid starting output, $contentLength should be set explicitly in
     *                              that case
     * @param string $contentType
     * @param int $contentLength    explicit content length, if strlen($content) isn't applicable
     * @throws \Magento\Core\Exception
     * @return \Magento\Core\Controller\Varien\Action
     */
    protected function _prepareDownloadResponse(
        $fileName,
        $content,
        $contentType = 'application/octet-stream',
        $contentLength = null
    ) {
        /** @var \Magento\Filesystem $filesystem */
        $filesystem = $this->_objectManager->create('Magento\Filesystem');
        $isFile = false;
        $file   = null;
        if (is_array($content)) {
            if (!isset($content['type']) || !isset($content['value'])) {
                return $this;
            }
            if ($content['type'] == 'filename') {
                $isFile         = true;
                $file           = $content['value'];
                $contentLength  = $filesystem->getFileSize($file);
            }
        }

        $this->getResponse()
            ->setHttpResponseCode(200)
            ->setHeader('Pragma', 'public', true)
            ->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true)
            ->setHeader('Content-type', $contentType, true)
            ->setHeader('Content-Length', is_null($contentLength) ? strlen($content) : $contentLength, true)
            ->setHeader('Content-Disposition', 'attachment; filename="'.$fileName.'"', true)
            ->setHeader('Last-Modified', date('r'), true);

        if (!is_null($content)) {
            if ($isFile) {
                $this->getResponse()->clearBody();
                $this->getResponse()->sendHeaders();

                if (!$filesystem->isFile($file)) {
                    throw new \Magento\Core\Exception(__('File not found'));
                }
                $stream = $filesystem->createAndOpenStream($file, 'r');
                while ($buffer = $stream->read(1024)) {
                    print $buffer;
                }
                flush();
                $stream->close();
                if (!empty($content['rm'])) {
                    $filesystem->delete($file);
                }

                exit(0);
            } else {
                $this->getResponse()->setBody($content);
            }
        }
        return $this;
    }
}
