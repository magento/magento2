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
 * @category    Mage
 * @package     Mage_Core
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Custom Zend_Controller_Action class (formally)
 *
 * Allows dispatching before and after events for each controller action
 *
 * @category   Mage
 * @package    Mage_Core
 * @author     Magento Core Team <core@magentocommerce.com>
 */
abstract class Mage_Core_Controller_Varien_Action extends Mage_Core_Controller_Varien_ActionAbstract
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

    const XML_PAGE_TYPE_RENDER_INHERITED = 'global/dev/page_type/render_inherited';

    /**
     * @var Mage_Core_Controller_Request_Http
     */
    protected $_request;

    /**
     * @var Mage_Core_Controller_Response_Http
     */
    protected $_response;

    /**
     * @var Magento_ObjectManager
     */
    protected $_objectManager;

    /**
     * Real module name (like 'Mage_Module')
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
     * Currently used area
     *
     * @var string
     */
    protected $_currentArea;

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
     * @var Mage_Core_Controller_Varien_Front
     */
    protected $_frontController = null;

    /**
     * @var Mage_Core_Model_Layout_Factory
     */
    protected $_layoutFactory;

    /**
     * Constructor
     *
     * @param Mage_Core_Controller_Request_Http $request
     * @param Mage_Core_Controller_Response_Http $response
     * @param string $areaCode
     * @param Magento_ObjectManager $objectManager
     * @param Mage_Core_Controller_Varien_Front $frontController
     * @param Mage_Core_Model_Layout_Factory $layoutFactory
     */
    public function __construct(
        Mage_Core_Controller_Request_Http $request,
        Mage_Core_Controller_Response_Http $response,
        $areaCode = null,
        Magento_ObjectManager $objectManager,
        Mage_Core_Controller_Varien_Front $frontController,
        Mage_Core_Model_Layout_Factory $layoutFactory
    ) {
        parent::__construct($request, $response, $areaCode);

        $this->_objectManager   = $objectManager;
        $this->_frontController = $frontController;
        $this->_layoutFactory   = $layoutFactory;
        $this->_frontController->setAction($this);

        $this->_construct();
    }

    protected function _construct()
    {
    }

    /**
     * Set currently used area code
     * @param string $areaCode
     * @return Mage_Core_Controller_Varien_Action
     */
    public function setCurrentArea($areaCode)
    {
        Mage::getConfig()->setCurrentAreaCode($areaCode);
        $this->_currentArea = $areaCode;
        return $this;
    }

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
     * @return  Mage_Core_Controller_Varien_Action
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
     * @return Mage_Core_Model_Layout
     */
    public function getLayout()
    {
        return $this->_layoutFactory->createLayout(array('area' => $this->_currentArea));
    }

    /**
     * Load layout by handles(s)
     *
     * @param   string|null|bool $handles
     * @param   bool $generateBlocks
     * @param   bool $generateXml
     * @return  Mage_Core_Controller_Varien_Action
     */
    public function loadLayout($handles = null, $generateBlocks = true, $generateXml = true)
    {
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
     * @return Mage_Core_Controller_Varien_Action
     */
    public function addActionLayoutHandles()
    {
        $renderInherited = (string) Mage::app()->getConfig()->getNode(self::XML_PAGE_TYPE_RENDER_INHERITED);
        if (!$renderInherited || !$this->addPageLayoutHandles()) {
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

    public function loadLayoutUpdates()
    {
        Magento_Profiler::start('LAYOUT');

        // dispatch event for adding handles to layout update
        Mage::dispatchEvent(
            'controller_action_layout_load_before',
            array('action' => $this, 'layout' => $this->getLayout())
        );

        // load layout updates by specified handles
        Magento_Profiler::start('layout_load');
        $this->getLayout()->getUpdate()->load();
        Magento_Profiler::stop('layout_load');

        Magento_Profiler::stop('LAYOUT');
        return $this;
    }

    public function generateLayoutXml()
    {
        Magento_Profiler::start('LAYOUT');

        // dispatch event for adding text layouts
        if (!$this->getFlag('', self::FLAG_NO_DISPATCH_BLOCK_EVENT)) {
            Mage::dispatchEvent(
                'controller_action_layout_generate_xml_before',
                array('action' => $this, 'layout' => $this->getLayout())
            );
        }

        // generate xml from collected text updates
        Magento_Profiler::start('layout_generate_xml');
        $this->getLayout()->generateXml();
        Magento_Profiler::stop('layout_generate_xml');

        Magento_Profiler::stop('LAYOUT');
        return $this;
    }

    public function generateLayoutBlocks()
    {
        Magento_Profiler::start('LAYOUT');

        // dispatch event for adding xml layout elements
        if(!$this->getFlag('', self::FLAG_NO_DISPATCH_BLOCK_EVENT)) {
            Mage::dispatchEvent(
                'controller_action_layout_generate_blocks_before',
                array('action' => $this, 'layout' => $this->getLayout())
            );
        }

        // generate blocks from xml layout
        Magento_Profiler::start('layout_generate_blocks');
        $this->getLayout()->generateElements();
        Magento_Profiler::stop('layout_generate_blocks');

        if (!$this->getFlag('', self::FLAG_NO_DISPATCH_BLOCK_EVENT)) {
            Mage::dispatchEvent(
                'controller_action_layout_generate_blocks_after',
                array('action' => $this, 'layout' => $this->getLayout())
            );
        }

        Magento_Profiler::stop('LAYOUT');
        return $this;
    }

    /**
     * Rendering layout
     *
     * @param   string $output
     * @return  Mage_Core_Controller_Varien_Action
     */
    public function renderLayout($output = '')
    {
        if ($this->getFlag('', 'no-renderLayout')) {
            return;
        }

        if ($this->_frontController->getNoRender()) {
            return;
        }

        Magento_Profiler::start('LAYOUT');

        $this->_renderTitles();

        Magento_Profiler::start('layout_render');

        if ('' !== $output) {
            $this->getLayout()->addOutputElement($output);
        }

        Mage::dispatchEvent('controller_action_layout_render_before');
        Mage::dispatchEvent('controller_action_layout_render_before_' . $this->getFullActionName());

        $this->getLayout()->setDirectOutput(false);

        $output = $this->getLayout()->getOutput();
        Mage::getSingleton('Mage_Core_Model_Translate_Inline')->processResponseBody($output);
        $this->getResponse()->appendBody($output);
        Magento_Profiler::stop('layout_render');

        Magento_Profiler::stop('LAYOUT');
        return $this;
    }

    public function dispatch($action)
    {
        $this->getRequest()->setDispatched(true);
        try {
            $actionMethodName = $this->getActionMethodName($action);
            if (!method_exists($this, $actionMethodName)) {
                $actionMethodName = 'norouteAction';
            }

            $profilerKey = 'CONTROLLER_ACTION:' . $this->getFullActionName();
            Magento_Profiler::start($profilerKey);

            Mage::getConfig()->setCurrentAreaCode($this->_currentArea);

            Magento_Profiler::start('predispatch');
            $this->preDispatch();
            Magento_Profiler::stop('predispatch');

            if ($this->getRequest()->isDispatched()) {
                /**
                 * preDispatch() didn't change the action, so we can continue
                 */
                if (!$this->getFlag('', self::FLAG_NO_DISPATCH)) {
                    Magento_Profiler::start('action_body');
                    $this->$actionMethodName();
                    Magento_Profiler::stop('action_body');

                    Magento_Profiler::start('postdispatch');
                    $this->postDispatch();
                    Magento_Profiler::stop('postdispatch');
                }
            }

            Magento_Profiler::stop($profilerKey);
        } catch (Mage_Core_Controller_Varien_Exception $e) {
            // set prepared flags
            foreach ($e->getResultFlags() as $flagData) {
                list($action, $flag, $value) = $flagData;
                $this->setFlag($action, $flag, $value);
            }
            // call forward, redirect or an action
            list($method, $parameters) = $e->getResultCallback();
            switch ($method) {
                case Mage_Core_Controller_Varien_Exception::RESULT_REDIRECT:
                    list($path, $arguments) = $parameters;
                    $this->_redirect($path, $arguments);
                    break;
                case Mage_Core_Controller_Varien_Exception::RESULT_FORWARD:
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
     * @return Mage_Core_Controller_Varien_Action
     */
    protected function _startSession()
    {
        if (!$this->getFlag('', self::FLAG_NO_START_SESSION)) {
            $checkCookie = in_array($this->getRequest()->getActionName(), $this->_cookieCheckActions)
                && !$this->getRequest()->getParam('nocookie', false);
            $cookies = Mage::getSingleton('Mage_Core_Model_Cookie')->get();
            /** @var $session Mage_Core_Model_Session */
            $session = Mage::getSingleton('Mage_Core_Model_Session',
                array('data' => array('name' => $this->_sessionNamespace)))
                ->start();

            if (empty($cookies)) {
                if ($session->getCookieShouldBeReceived()) {
                    $this->setFlag('', self::FLAG_NO_COOKIES_REDIRECT, true);
                    $session->unsCookieShouldBeReceived();
                    $session->setSkipSessionIdFlag(true);
                } elseif ($checkCookie) {
                    if (isset($_GET[$session->getSessionIdQueryParam()]) && Mage::app()->getUseSessionInUrl()
                        && $this->_sessionNamespace != Mage_Backend_Controller_ActionAbstract::SESSION_NAMESPACE
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
     * @return Mage_Core_Controller_Varien_Action
     */
    protected function _initDesign()
    {
        $area = Mage::app()->getArea($this->getLayout()->getArea());
        $area->load();
        $this->_initDefaultTheme();
        $area->detectDesign($this->getRequest());
        return $this;
    }

    /**
     * Initialize theme
     *
     * @return Mage_Core_Controller_Varien_Action
     */
    protected function _initDefaultTheme()
    {
        Mage::getDesign()->setDefaultDesignTheme();
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
            if (!Mage::isInstalled()) {
                $this->setFlag('', self::FLAG_NO_DISPATCH, true);
                $this->_redirect('install');
                return;
            }
        }

        // Prohibit disabled store actions
        if (Mage::isInstalled() && !Mage::app()->getStore()->getIsActive()) {
            Mage::app()->throwStoreException();
        }

        if ($this->_rewrite()) {
            return;
        }

        // Start session
        $this->_startSession();

        // Load area and initialize design depend on loaded area
        $this->_initDesign();

        if ($this->getFlag('', self::FLAG_NO_COOKIES_REDIRECT)
            && Mage::getStoreConfig('web/browser_capabilities/cookies')
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
        Mage::dispatchEvent('controller_action_predispatch', array('controller_action' => $this));
        Mage::dispatchEvent('controller_action_predispatch_' . $this->getRequest()->getRouteName(),
            array('controller_action' => $this));
        Mage::dispatchEvent('controller_action_predispatch_' . $this->getFullActionName(),
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

        Mage::dispatchEvent(
            'controller_action_postdispatch_' . $this->getFullActionName(),
            array('controller_action' => $this)
        );
        Mage::dispatchEvent(
            'controller_action_postdispatch_' . $this->getRequest()->getRouteName(),
            array('controller_action' => $this)
        );
        Mage::dispatchEvent('controller_action_postdispatch', array('controller_action' => $this));
    }

    public function norouteAction($coreRoute = null)
    {
        $status = $this->getRequest()->getParam('__status__');
        if (!$status instanceof Varien_Object) {
            $status = new Varien_Object();
        }

        Mage::dispatchEvent('controller_action_noroute', array('action' => $this, 'status' => $status));

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

    public function noCookiesAction()
    {
        $redirect = new Varien_Object();
        Mage::dispatchEvent('controller_action_nocookies', array(
            'action'    => $this,
            'redirect'  => $redirect
        ));

        if ($url = $redirect->getRedirectUrl()) {
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
     * @return Mage_Core_Controller_Varien_Action
     */
    protected function _initLayoutMessages($messagesStorage)
    {
        if (!is_array($messagesStorage)) {
            $messagesStorage = array($messagesStorage);
        }
        foreach ($messagesStorage as $storageName) {
            $storage = Mage::getSingleton($storageName);
            if ($storage) {
                $block = $this->getLayout()->getMessagesBlock();
                $block->addMessages($storage->getMessages(true));
                $block->setEscapeMessageFlag($storage->getEscapeMessages(true));
                $block->addStorageType($storageName);
            } else {
                Mage::throwException(
                     Mage::helper('Mage_Core_Helper_Data')->__('Invalid messages storage "%s" for layout messages initialization', (string)$storageName)
                );
            }
        }
        return $this;
    }

    /**
     * Initializing layout messages by message storage(s), loading and adding messages to layout messages block
     *
     * @param string|array $messagesStorage
     * @return Mage_Core_Controller_Varien_Action
     */
    public function initLayoutMessages($messagesStorage)
    {
        return $this->_initLayoutMessages($messagesStorage);
    }

    /**
     * Set redirect url into response
     *
     * @param   string $url
     * @return  Mage_Core_Controller_Varien_Action
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
     * @return  Mage_Core_Controller_Varien_Action
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
     * @return  Mage_Core_Controller_Varien_Action
     */
    public function setRedirectWithCookieCheck($path, array $arguments = array())
    {
        /** @var $session Mage_Core_Model_Session */
        $session = Mage::getSingleton('Mage_Core_Model_Session',
            array('data' => array('name' => $this->_sessionNamespace)));
        if ($session->getCookieShouldBeReceived() && Mage::app()->getUseSessionInUrl()
            && $this->_sessionNamespace != Mage_Backend_Controller_ActionAbstract::SESSION_NAMESPACE
        ) {
            $arguments += array('_query' => array(
                $session->getSessionIdQueryParam() => $session->getSessionId()
            ));
        }
        $this->getResponse()->setRedirect(Mage::getUrl($path, $arguments));
        return $this;
    }


    /**
     * Redirect to success page
     *
     * @param string $defaultUrl
     * @return Mage_Core_Controller_Varien_Action
     */
    protected function _redirectSuccess($defaultUrl)
    {
        $successUrl = $this->getRequest()->getParam(self::PARAM_NAME_SUCCESS_URL);
        if (empty($successUrl)) {
            $successUrl = $defaultUrl;
        }
        if (!$this->_isUrlInternal($successUrl)) {
            $successUrl = Mage::app()->getStore()->getBaseUrl();
        }
        $this->getResponse()->setRedirect($successUrl);
        return $this;
    }

    /**
     * Redirect to error page
     *
     * @param string $defaultUrl
     * @return  Mage_Core_Controller_Varien_Action
     */
    protected function _redirectError($defaultUrl)
    {
        $errorUrl = $this->getRequest()->getParam(self::PARAM_NAME_ERROR_URL);
        if (empty($errorUrl)) {
            $errorUrl = $defaultUrl;
        }
        if (!$this->_isUrlInternal($errorUrl)) {
            $errorUrl = Mage::app()->getStore()->getBaseUrl();
        }
        $this->getResponse()->setRedirect($errorUrl);
        return $this;
    }

    /**
     * Set referer url for redirect in response
     *
     * @param   string $defaultUrl
     * @return  Mage_Core_Controller_Varien_Action
     */
    protected function _redirectReferer($defaultUrl=null)
    {

        $refererUrl = $this->_getRefererUrl();
        if (empty($refererUrl)) {
            $refererUrl = empty($defaultUrl) ? Mage::getBaseUrl() : $defaultUrl;
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
            $refererUrl = Mage::helper('Mage_Core_Helper_Data')->urlDecode($url);
        }
        $url = $this->getRequest()->getParam(self::PARAM_NAME_URL_ENCODED);
        if ($url) {
            $refererUrl = Mage::helper('Mage_Core_Helper_Data')->urlDecode($url);
        }

        if (!$this->_isUrlInternal($refererUrl)) {
            $refererUrl = Mage::app()->getStore()->getBaseUrl();
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
            if ((strpos($url, Mage::app()->getStore()->getBaseUrl()) === 0)
                || (strpos($url, Mage::app()->getStore()->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK, true)) === 0)
            ) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get real module name (like 'Mage_Module')
     *
     * @return  string
     */
    protected function _getRealModuleName()
    {
        if (empty($this->_realModuleName)) {
            $class = get_class($this);
            $this->_realModuleName = substr(
                $class,
                0,
                strpos(strtolower($class), '_' . strtolower($this->getRequest()->getControllerName() . 'Controller'))
            );
        }
        return $this->_realModuleName;
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

        $rewrite = Mage::getConfig()->getNode('global/routers/' . $route . '/rewrite/' . $controller);
        if (!$rewrite) {
            return false;
        }

        if (!($rewrite->actions && $rewrite->actions->$action) || $rewrite->is('override_actions')) {
            $t = explode('/', (string)$rewrite->to);
            if (sizeof($t) !== 2 || empty($t[0]) || empty($t[1])) {
                return false;
            }
            $t[2] = $action;
        } else {
            $t = explode('/', (string)$rewrite->actions->$action->to);
            if (sizeof($t) !== 3 || empty($t[0]) || empty($t[1]) || empty($t[2])) {
                return false;
            }
        }

        $this->_forward(
            $t[2] === '*' ? $action : $t[2],
            $t[1] === '*' ? $controller : $t[1],
            $t[0] === '*' ? $route : $t[0]
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
            || $formKey != Mage::getSingleton('Mage_Core_Model_Session')->getFormKey()
        ) {
            return false;
        }
        return true;
    }

    /**
     * Add an extra title to the end or one from the end, or remove all
     *
     * Usage examples:
     * $this->_title('foo')->_title('bar');
     * => bar / foo / <default title>
     *
     * $this->_title()->_title('foo')->_title('bar');
     * => bar / foo
     *
     * $this->_title('foo')->_title(false)->_title('bar');
     * bar / <default title>
     *
     * @see self::_renderTitles()
     * @param string|false|-1|null $text
     * @param bool $resetIfExists
     * @return Mage_Core_Controller_Varien_Action
     */
    protected function _title($text = null, $resetIfExists = true)
    {
        if (is_string($text)) {
            $this->_titles[] = $text;
        } elseif (-1 === $text) {
            if (empty($this->_titles)) {
                $this->_removeDefaultTitle = true;
            } else {
                array_pop($this->_titles);
            }
        } elseif (empty($this->_titles) || $resetIfExists) {
            if (false === $text) {
                $this->_removeDefaultTitle = false;
                $this->_titles = array();
            } elseif (null === $text) {
                $this->_removeDefaultTitle = true;
                $this->_titles = array();
            }
        }
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
                $titleBlock->setTitle(implode(' / ', array_reverse($this->_titles)));
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
        $filterInput = new Zend_Filter_LocalizedToNormalized(array(
            'date_format' => Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT)
        ));
        $filterInternal = new Zend_Filter_NormalizedToLocalized(array(
            'date_format' => Varien_Date::DATE_INTERNAL_FORMAT
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
        $filterInput = new Zend_Filter_LocalizedToNormalized(array(
            'date_format' => Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT)
        ));
        $filterInternal = new Zend_Filter_NormalizedToLocalized(array(
            'date_format' => Varien_Date::DATETIME_INTERNAL_FORMAT
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
     * @return Mage_Core_Controller_Varien_Action
     */
    protected function _prepareDownloadResponse(
        $fileName,
        $content,
        $contentType = 'application/octet-stream',
        $contentLength = null)
    {
        $isFile = false;
        $file   = null;
        if (is_array($content)) {
            if (!isset($content['type']) || !isset($content['value'])) {
                return $this;
            }
            if ($content['type'] == 'filename') {
                $isFile         = true;
                $file           = $content['value'];
                $contentLength  = filesize($file);
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

                $ioAdapter = new Varien_Io_File();
                if (!$ioAdapter->fileExists($file)) {
                    Mage::throwException(Mage::helper('Mage_Core_Helper_Data')->__('File not found'));
                }
                $ioAdapter->open(array('path' => $ioAdapter->dirname($file)));
                $ioAdapter->streamOpen($file, 'r');
                while ($buffer = $ioAdapter->streamRead()) {
                    print $buffer;
                }
                $ioAdapter->streamClose();
                if (!empty($content['rm'])) {
                    $ioAdapter->rm($file);
                }

                exit(0);
            } else {
                $this->getResponse()->setBody($content);
            }
        }
        return $this;
    }
}
