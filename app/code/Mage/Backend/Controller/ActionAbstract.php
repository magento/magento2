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
 * @package     Mage_Backend
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Geeneric backend controller
 */
abstract class Mage_Backend_Controller_ActionAbstract extends Mage_Core_Controller_Varien_Action
{
    /**
     * Name of "is URLs checked" flag
     */
    const FLAG_IS_URLS_CHECKED = 'check_url_settings';

    /**
     * Session namespace to refer in other places
     */
    const SESSION_NAMESPACE = 'adminhtml';

    /**
     * Array of actions which can be processed without secret key validation
     *
     * @var array
     */
    protected $_publicActions = array();

    /**
     * Namespace for session.
     *
     * @var string
     */
    protected $_sessionNamespace = self::SESSION_NAMESPACE;

    /**
     * @var Mage_Backend_Helper_Data
     */
    protected $_helper;

    /**
     * @var Mage_Backend_Model_Session
     */
    protected $_session;

    /**
     * @param Mage_Core_Controller_Request_Http $request
     * @param Mage_Core_Controller_Response_Http $response
     * @param Magento_ObjectManager $objectManager
     * @param Mage_Core_Controller_Varien_Front $frontController
     * @param Mage_Core_Model_Layout_Factory $layoutFactory
     * @param string $areaCode
     * @param array $invokeArgs
     */
    public function __construct(
        Mage_Core_Controller_Request_Http $request,
        Mage_Core_Controller_Response_Http $response,
        Magento_ObjectManager $objectManager,
        Mage_Core_Controller_Varien_Front $frontController,
        Mage_Core_Model_Layout_Factory $layoutFactory,
        $areaCode = null,
        array $invokeArgs = array()
    ) {
        parent::__construct($request, $response, $objectManager, $frontController, $layoutFactory, $areaCode);

        $this->_helper = isset($invokeArgs['helper']) ?
            $invokeArgs['helper'] :
            Mage::helper('Mage_Backend_Helper_Data');

        $this->_session = isset($invokeArgs['session']) ?
            $invokeArgs['session'] :
            Mage::getSingleton('Mage_Backend_Model_Session');
    }

    protected function _isAllowed()
    {
        return true;
    }

    /**
     * Retrieve adminhtml session model object
     *
     * @return Mage_Backend_Model_Session
     */
    protected function _getSession()
    {
        return $this->_session;
    }

    /**
     * Retrieve base adminhtml helper
     *
     * @return Mage_Backend_Helper_Data
     */
    protected function _getHelper()
    {
        return $this->_helper;
    }

    /**
     * Define active menu item in menu block
     * @param string $itemId current active menu item
     * @return Mage_Backend_Controller_ActionAbstract
     */
    protected function _setActiveMenu($itemId)
    {
        $this->getLayout()->getBlock('menu')->setActive($itemId);
        return $this;
    }

    /**
     * @param $label
     * @param $title
     * @param null $link
     * @return Mage_Backend_Controller_ActionAbstract
     */
    protected function _addBreadcrumb($label, $title, $link=null)
    {
        $this->getLayout()->getBlock('breadcrumbs')->addLink($label, $title, $link);
        return $this;
    }

    /**
     * @param Mage_Core_Block_Abstract $block
     * @return Mage_Backend_Controller_ActionAbstract
     */
    protected function _addContent(Mage_Core_Block_Abstract $block)
    {
        return $this->_moveBlockToContainer($block, 'content');
    }

    /**
     * @param Mage_Core_Block_Abstract $block
     * @return Mage_Backend_Controller_ActionAbstract
     */
    protected function _addLeft(Mage_Core_Block_Abstract $block)
    {
        return $this->_moveBlockToContainer($block, 'left');
    }

    /**
     * @param Mage_Core_Block_Abstract $block
     * @return Mage_Backend_Controller_ActionAbstract
     */
    protected function _addJs(Mage_Core_Block_Abstract $block)
    {
        return $this->_moveBlockToContainer($block, 'js');
    }

    /**
     * Set specified block as an anonymous child to specified container
     *
     * The block will be moved to the container from previous parent after all other elements
     *
     * @param Mage_Core_Block_Abstract $block
     * @param string $containerName
     * @return Mage_Backend_Controller_ActionAbstract
     */
    private function _moveBlockToContainer(Mage_Core_Block_Abstract $block, $containerName)
    {
        $this->getLayout()->setChild($containerName, $block->getNameInLayout(), '');
        return $this;
    }

    /**
     * Controller predispatch method
     *
     * @return Mage_Backend_Controller_ActionAbstract
     */
    public function preDispatch()
    {
        Mage::app()->setCurrentStore('admin');

        Mage::dispatchEvent('adminhtml_controller_action_predispatch_start', array());
        parent::preDispatch();
        if (!$this->_processUrlKeys()) {
            return $this;
        }

        if ($this->getRequest()->isDispatched()
            && $this->getRequest()->getActionName() !== 'denied'
            && !$this->_isAllowed()) {
            $this->_forward('denied');
            $this->setFlag('', self::FLAG_NO_DISPATCH, true);
            return $this;
        }

        if ($this->_isUrlChecked()) {
            $this->setFlag('', self::FLAG_IS_URLS_CHECKED, true);
        }
        if (is_null(Mage::getSingleton('Mage_Backend_Model_Session')->getLocale())) {
            Mage::getSingleton('Mage_Backend_Model_Session')->setLocale(Mage::app()->getLocale()->getLocaleCode());
        }

        return $this;
    }

    /**
     * Check whether url is checked
     *
     * @return bool
     */
    protected function _isUrlChecked()
    {
        return !$this->getFlag('', self::FLAG_IS_URLS_CHECKED)
            && !$this->getRequest()->getParam('forwarded')
            && !$this->_getSession()->getIsUrlNotice(true)
            && !Mage::getConfig()->getNode('global/can_use_base_url');
    }

    /**
     * Check url keys. If non valid - redirect
     *
     * @return bool
     */
    public function _processUrlKeys()
    {
        $_isValidFormKey = true;
        $_isValidSecretKey = true;
        $_keyErrorMsg = '';
        if (Mage::getSingleton('Mage_Backend_Model_Auth_Session')->isLoggedIn()) {
            if ($this->getRequest()->isPost()) {
                $_isValidFormKey = $this->_validateFormKey();
                $_keyErrorMsg = Mage::helper('Mage_Backend_Helper_Data')
                    ->__('Invalid Form Key. Please refresh the page.');
            } elseif (Mage::getSingleton('Mage_Backend_Model_Url')->useSecretKey()) {
                $_isValidSecretKey = $this->_validateSecretKey();
                $_keyErrorMsg = Mage::helper('Mage_Backend_Helper_Data')
                    ->__('Invalid Secret Key. Please refresh the page.');
            }
        }
        if (!$_isValidFormKey || !$_isValidSecretKey) {
            $this->setFlag('', self::FLAG_NO_DISPATCH, true);
            $this->setFlag('', self::FLAG_NO_POST_DISPATCH, true);
            if ($this->getRequest()->getQuery('isAjax', false) || $this->getRequest()->getQuery('ajax', false)) {
                $this->getResponse()->setBody(Mage::helper('Mage_Core_Helper_Data')->jsonEncode(array(
                    'error' => true,
                    'message' => $_keyErrorMsg
                )));
            } else {
                $this->_redirect(Mage::getSingleton('Mage_Backend_Model_Url')->getStartupPageUrl());
            }
            return false;
        }
        return true;
    }

    /**
     * Fire predispatch events, execute extra logic after predispatch
     *
     * @return void
     */
    protected function _firePreDispatchEvents()
    {
        $this->_initAuthentication();
        parent::_firePreDispatchEvents();
    }

    /**
     * Start authentication process
     *
     * @return Mage_Backend_Controller_ActionAbstract
     */
    protected function _initAuthentication()
    {
        /** @var $auth Mage_Backend_Model_Auth */
        $auth = Mage::getSingleton('Mage_Backend_Model_Auth');

        $request = $this->getRequest();

        $requestedActionName = $request->getActionName();
        $openActions = array(
            'forgotpassword',
            'resetpassword',
            'resetpasswordpost',
            'logout',
            'refresh' // captcha refresh
        );
        if (in_array($requestedActionName, $openActions)) {
            $request->setDispatched(true);
        } else {
            if ($auth->getUser()) {
                $auth->getUser()->reload();
            }
            if (!$auth->isLoggedIn()) {
                $this->_processNotLoggedInUser($request);
            }
        }
        $auth->getAuthStorage()->refreshAcl();
        return $this;
    }

    /**
     * Process not logged in user data
     *
     * @param Mage_Core_Controller_Request_Http $request
     */
    protected function _processNotLoggedInUser(Mage_Core_Controller_Request_Http $request)
    {
        $isRedirectNeeded = false;
        if ($request->getPost('login') && $this->_performLogin()) {
            $isRedirectNeeded = $this->_redirectIfNeededAfterLogin();
        }
        if (!$isRedirectNeeded && !$request->getParam('forwarded')) {
            if ($request->getParam('isIframe')) {
                $request->setParam('forwarded', true)
                    ->setControllerName('auth')
                    ->setActionName('deniedIframe')
                    ->setDispatched(false);
            } else if ($request->getParam('isAjax')) {
                $request->setParam('forwarded', true)
                    ->setControllerName('auth')
                    ->setActionName('deniedJson')
                    ->setDispatched(false);
            } else {
                $request->setParam('forwarded', true)
                    ->setRouteName('adminhtml')
                    ->setControllerName('auth')
                    ->setActionName('login')
                    ->setDispatched(false);
            }
        }
    }

    /**
     * Performs login, if user submitted login form
     *
     * @return boolean
     */
    protected function _performLogin()
    {
        $outputValue = true;
        $postLogin  = $this->getRequest()->getPost('login');
        $username   = isset($postLogin['username']) ? $postLogin['username'] : '';
        $password   = isset($postLogin['password']) ? $postLogin['password'] : '';
        $this->getRequest()->setPost('login', null);

        try {
            Mage::getSingleton('Mage_Backend_Model_Auth')->login($username, $password);
        } catch (Mage_Backend_Model_Auth_Exception $e) {
            if (!$this->getRequest()->getParam('messageSent')) {
                Mage::getSingleton('Mage_Backend_Model_Session')->addError($e->getMessage());
                $this->getRequest()->setParam('messageSent', true);
                $outputValue = false;
            }
        }
        return $outputValue;
    }

    /**
     * Checks, whether Magento requires redirection after successful admin login, and redirects user, if needed
     *
     * @return bool
     */
    protected function _redirectIfNeededAfterLogin()
    {
        $requestUri = null;

        /** @var $urlModel Mage_Backend_Model_Url */
        $urlModel = Mage::getSingleton('Mage_Backend_Model_Url');

        // Checks, whether secret key is required for admin access or request uri is explicitly set
        if ($urlModel->useSecretKey()) {
            $requestUri = $urlModel->getUrl('*/*/*', array('_current' => true));
        } elseif ($this->getRequest()) {
            $requestUri = $this->getRequest()->getRequestUri();
        }

        if (!$requestUri) {
            return false;
        }

        $this->getResponse()->setRedirect($requestUri);
        $this->setFlag('', Mage_Core_Controller_Varien_Action::FLAG_NO_DISPATCH, true);
        return true;
    }

    public function deniedAction()
    {
        $this->getResponse()->setHeader('HTTP/1.1', '403 Forbidden');
        if (!Mage::getSingleton('Mage_Backend_Model_Auth_Session')->isLoggedIn()) {
            $this->_redirect('*/auth/login');
            return;
        }
        $this->loadLayout(array('default', 'adminhtml_denied'));
        $this->renderLayout();
    }

    /**
     * Load layout by handles and verify user ACL
     *
     * @param string|null|bool|array $ids
     * @param bool $generateBlocks
     * @param bool $generateXml
     * @return Mage_Backend_Controller_ActionAbstract|Mage_Core_Controller_Varien_Action
     */
    public function loadLayout($ids = null, $generateBlocks = true, $generateXml = true)
    {
        parent::loadLayout($ids, false, $generateXml);
        Mage::getSingleton('Mage_Core_Model_Authorization')->filterAclNodes($this->getLayout()->getNode());
        if ($generateBlocks) {
            $this->generateLayoutBlocks();
            $this->_isLayoutLoaded = true;
        }
        $this->_initLayoutMessages('Mage_Backend_Model_Session');
        return $this;
    }

    /**
     * No route action
     *
     * @param null $coreRoute
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function norouteAction($coreRoute = null)
    {
        $this->getResponse()->setHeader('HTTP/1.1', '404 Not Found');
        $this->getResponse()->setHeader('Status', '404 File not found');
        $this->loadLayout(array('default', 'adminhtml_noroute'));
        $this->renderLayout();
    }

    /**
     * Set referrer url for redirect in response
     *
     * Is overridden here to set defaultUrl to admin url
     *
     * @param   string $defaultUrl
     * @return Mage_Backend_Controller_ActionAbstract
     */
    protected function _redirectReferer($defaultUrl=null)
    {
        $defaultUrl = empty($defaultUrl) ? $this->getUrl('*') : $defaultUrl;
        parent::_redirectReferer($defaultUrl);
        return $this;
    }

    /**
     * Set redirect into response
     *
     * @param   string $path
     * @param   array $arguments
     * @return Mage_Backend_Controller_ActionAbstract
     */
    protected function _redirect($path, $arguments=array())
    {
        $this->_getSession()->setIsUrlNotice($this->getFlag('', self::FLAG_IS_URLS_CHECKED));
        $this->getResponse()->setRedirect($this->getUrl($path, $arguments));
        return $this;
    }

    protected function _forward($action, $controller = null, $module = null, array $params = null)
    {
        $this->_getSession()->setIsUrlNotice($this->getFlag('', self::FLAG_IS_URLS_CHECKED));
        return parent::_forward($action, $controller, $module, $params);
    }

    /**
     * Generate url by route and parameters
     *
     * @param   string $route
     * @param   array $params
     * @return  string
     */
    public function getUrl($route='', $params=array())
    {
        return $this->_getHelper()->getUrl($route, $params);
    }

    /**
     * Validate Secret Key
     *
     * @return bool
     */
    protected function _validateSecretKey()
    {
        if (is_array($this->_publicActions) && in_array($this->getRequest()->getActionName(), $this->_publicActions)) {
            return true;
        }

        if (!($secretKey = $this->getRequest()->getParam(Mage_Backend_Model_Url::SECRET_KEY_PARAM_NAME, null))
            || $secretKey != Mage::getSingleton('Mage_Backend_Model_Url')->getSecretKey()) {
            return false;
        }
        return true;
    }

    /**
     * Translate a phrase
     *
     * @return string
     * @SuppressWarnings(PHPMD.ShortMethodName)
     */
    public function __()
    {
        $args = func_get_args();
        $expr = new Mage_Core_Model_Translate_Expr(array_shift($args), $this->_getRealModuleName());
        array_unshift($args, $expr);
        return $this->_objectManager->get('Mage_Core_Model_Translate')->translate($args);
    }

    /**
     * Render specified template
     *
     * @param string $tplName
     * @param array $data parameters required by template
     */
    protected function _outTemplate($tplName, $data = array())
    {
        $this->_initLayoutMessages('Mage_Backend_Model_Session');
        $block = $this->getLayout()->createBlock('Mage_Backend_Block_Template')->setTemplate("$tplName.phtml");
        foreach ($data as $index => $value) {
            $block->assign($index, $value);
        }
        $html = $block->toHtml();
        Mage::getSingleton('Mage_Core_Model_Translate_Inline')->processResponseBody($html);
        $this->getResponse()->setBody($html);
    }

    /**
     * Declare headers and content file in response for file download
     *
     * @param string $fileName
     * @param string|array $content set to null to avoid starting output, $contentLength should be set explicitly in
     *                              that case
     * @param string $contentType
     * @param int $contentLength    explicit content length, if strlen($content) isn't applicable
     * @return Mage_Backend_Controller_ActionAbstract
     */
    protected function _prepareDownloadResponse($fileName, $content, $contentType = 'application/octet-stream',
        $contentLength = null
    ) {
        $session = Mage::getSingleton('Mage_Backend_Model_Auth_Session');
        if ($session->isFirstPageAfterLogin()) {
            $this->_redirect(Mage::getSingleton('Mage_Backend_Model_Url')->getStartupPageUrl());
            return $this;
        }
        return parent::_prepareDownloadResponse($fileName, $content, $contentType, $contentLength);
    }
}
