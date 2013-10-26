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
 * @package     Magento_Backend
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Backend\Controller;

/**
 * Generic backend controller
 *
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 */
abstract class AbstractAction extends \Magento\Core\Controller\Varien\Action
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
     * @var \Magento\Backend\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $_session;

    /**
     * @var \Magento\AuthorizationInterface
     */
    protected $_authorization;

    /**
     * @var \Magento\Core\Model\Translate
     */
    protected $_translator;

    /**
     * @var \Magento\Backend\Model\Auth
     */
    protected $_auth;

    /**
     * @var \Magento\Backend\Model\Url
     */
    protected $_backendUrl;

    /**
     * @var \Magento\Core\Model\LocaleInterface
     */
    protected $_locale;

    /**
     * @param \Magento\Backend\Controller\Context $context
     */
    public function __construct(\Magento\Backend\Controller\Context $context)
    {
        parent::__construct($context);
        $this->_helper = $context->getHelper();
        $this->_session = $context->getSession();
        $this->_authorization = $context->getAuthorization();
        $this->_translator = $context->getTranslator();
        $this->_auth = $context->getAuth();
        $this->_backendUrl = $context->getBackendUrl();
        $this->_locale = $context->getLocale();
    }

    protected function _isAllowed()
    {
        return true;
    }

    /**
     * Retrieve adminhtml session model object
     *
     * @return \Magento\Backend\Model\Session
     */
    protected function _getSession()
    {
        return $this->_session;
    }

    /**
     * Retrieve base adminhtml helper
     *
     * @return \Magento\Backend\Helper\Data
     */
    protected function _getHelper()
    {
        return $this->_helper;
    }

    /**
     * Define active menu item in menu block
     * @param string $itemId current active menu item
     * @return \Magento\Backend\Controller\AbstractAction
     */
    protected function _setActiveMenu($itemId)
    {
        /** @var $menuBlock \Magento\Backend\Block\Menu */
        $menuBlock = $this->getLayout()->getBlock('menu');
        $menuBlock->setActive($itemId);
        $parents = $menuBlock->getMenuModel()->getParentItems($itemId);
        $parents = array_reverse($parents);
        foreach ($parents as $item) {
            /** @var $item \Magento\Backend\Model\Menu\Item */
            array_unshift($this->_titles, $item->getTitle());
        }
        return $this;
    }

    /**
     * @param $label
     * @param $title
     * @param null $link
     * @return \Magento\Backend\Controller\AbstractAction
     */
    protected function _addBreadcrumb($label, $title, $link=null)
    {
        $this->getLayout()->getBlock('breadcrumbs')->addLink($label, $title, $link);
        return $this;
    }

    /**
     * @param \Magento\Core\Block\AbstractBlock $block
     * @return \Magento\Backend\Controller\AbstractAction
     */
    protected function _addContent(\Magento\Core\Block\AbstractBlock $block)
    {
        return $this->_moveBlockToContainer($block, 'content');
    }

    /**
     * @param \Magento\Core\Block\AbstractBlock $block
     * @return \Magento\Backend\Controller\AbstractAction
     */
    protected function _addLeft(\Magento\Core\Block\AbstractBlock $block)
    {
        return $this->_moveBlockToContainer($block, 'left');
    }

    /**
     * @param \Magento\Core\Block\AbstractBlock $block
     * @return \Magento\Backend\Controller\AbstractAction
     */
    protected function _addJs(\Magento\Core\Block\AbstractBlock $block)
    {
        return $this->_moveBlockToContainer($block, 'js');
    }

    /**
     * Set specified block as an anonymous child to specified container
     *
     * The block will be moved to the container from previous parent after all other elements
     *
     * @param \Magento\Core\Block\AbstractBlock $block
     * @param string $containerName
     * @return \Magento\Backend\Controller\AbstractAction
     */
    private function _moveBlockToContainer(\Magento\Core\Block\AbstractBlock $block, $containerName)
    {
        $this->getLayout()->setChild($containerName, $block->getNameInLayout(), '');
        return $this;
    }

    /**
     * Controller predispatch method
     *
     * @return \Magento\Backend\Controller\AbstractAction
     */
    public function preDispatch()
    {
        /** @var $storeManager \Magento\Core\Model\StoreManager */
        $storeManager = $this->_objectManager->get('Magento\Core\Model\StoreManager');
        $storeManager->setCurrentStore('admin');

        $this->_eventManager->dispatch('adminhtml_controller_action_predispatch_start', array());
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

        $this->_processLocaleSettings();

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
            && !$this->_objectManager->get('Magento\Core\Model\Config')->getNode('global/can_use_base_url');
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
        if ($this->_auth->isLoggedIn()) {
            if ($this->getRequest()->isPost()) {
                $_isValidFormKey = $this->_validateFormKey();
                $_keyErrorMsg = __('Invalid Form Key. Please refresh the page.');
            } elseif ($this->_backendUrl->useSecretKey()) {
                $_isValidSecretKey = $this->_validateSecretKey();
                $_keyErrorMsg = __('You entered an invalid Secret Key. Please refresh the page.');
            }
        }
        if (!$_isValidFormKey || !$_isValidSecretKey) {
            $this->setFlag('', self::FLAG_NO_DISPATCH, true);
            $this->setFlag('', self::FLAG_NO_POST_DISPATCH, true);
            if ($this->getRequest()->getQuery('isAjax', false) || $this->getRequest()->getQuery('ajax', false)) {
                $this->getResponse()->setBody($this->_objectManager->get('Magento\Core\Helper\Data')->jsonEncode(array(
                    'error' => true,
                    'message' => $_keyErrorMsg
                )));
            } else {
                $this->_redirect($this->_backendUrl->getStartupPageUrl());
            }
            return false;
        }
        return true;
    }

    /**
     * Set session locale,
     * process force locale set through url params
     *
     * @return \Magento\Backend\Controller\AbstractAction
     */
    protected function _processLocaleSettings()
    {
        $forceLocale = $this->getRequest()->getParam('locale', null);
        if ($this->_objectManager->get('Magento\Core\Model\Locale\Validator')->isValid($forceLocale)) {
            $this->_getSession()->setSessionLocale($forceLocale);
        }

        if (is_null($this->_getSession()->getLocale())) {
            $this->_getSession()->setLocale($this->_locale->getLocaleCode());
        }

        return $this;
    }

    /**
     * Fire predispatch events, execute extra logic after predispatch
     */
    protected function _firePreDispatchEvents()
    {
        $this->_initAuthentication();
        parent::_firePreDispatchEvents();
    }

    /**
     * Start authentication process
     *
     * @return \Magento\Backend\Controller\AbstractAction
     */
    protected function _initAuthentication()
    {
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
            if ($this->_auth->getUser()) {
                $this->_auth->getUser()->reload();
            }
            if (!$this->_auth->isLoggedIn()) {
                $this->_processNotLoggedInUser($request);
            }
        }
        $this->_auth->getAuthStorage()->refreshAcl();
        return $this;
    }

    /**
     * Process not logged in user data
     *
     * @param \Magento\App\RequestInterface $request
     */
    protected function _processNotLoggedInUser(\Magento\App\RequestInterface $request)
    {
        $isRedirectNeeded = false;
        if ($request->getPost('login') && $this->_performLogin()) {
            $isRedirectNeeded = $this->_redirectIfNeededAfterLogin();
        }
        if (!$isRedirectNeeded && !$request->getParam('forwarded')) {
            if ($request->getParam('isIframe')) {
                $request->setParam('forwarded', true)
                    ->setRouteName('adminhtml')
                    ->setControllerName('auth')
                    ->setActionName('deniedIframe')
                    ->setDispatched(false);
            } elseif ($request->getParam('isAjax')) {
                $request->setParam('forwarded', true)
                    ->setRouteName('adminhtml')
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
            $this->_auth->login($username, $password);
        } catch (\Magento\Backend\Model\Auth\Exception $e) {
            if (!$this->getRequest()->getParam('messageSent')) {
                $this->_session->addError($e->getMessage());
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

        // Checks, whether secret key is required for admin access or request uri is explicitly set
        if ($this->_backendUrl->useSecretKey()) {
            $requestUri = $this->_backendUrl->getUrl('*/*/*', array('_current' => true));
        } elseif ($this->getRequest()) {
            $requestUri = $this->getRequest()->getRequestUri();
        }

        if (!$requestUri) {
            return false;
        }

        $this->getResponse()->setRedirect($requestUri);
        $this->setFlag('', \Magento\Core\Controller\Varien\Action::FLAG_NO_DISPATCH, true);
        return true;
    }

    public function deniedAction()
    {
        $this->getResponse()->setHeader('HTTP/1.1', '403 Forbidden');
        if (!$this->_auth->isLoggedIn()) {
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
     * @return \Magento\Backend\Controller\AbstractAction|\Magento\Core\Controller\Varien\Action
     */
    public function loadLayout($ids = null, $generateBlocks = true, $generateXml = true)
    {
        parent::loadLayout($ids, false, $generateXml);
        $this->_objectManager->get('Magento\Core\Model\Layout\Filter\Acl')
            ->filterAclNodes($this->getLayout()->getNode());
        if ($generateBlocks) {
            $this->generateLayoutBlocks();
            $this->_isLayoutLoaded = true;
        }
        $this->_initLayoutMessages('Magento\Backend\Model\Session');
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
     * @return \Magento\Backend\Controller\AbstractAction
     */
    protected function _redirectReferer($defaultUrl = null)
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
     * @return \Magento\Backend\Controller\AbstractAction
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
    public function getUrl($route = '', $params=array())
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

        $secretKey = $this->getRequest()->getParam(\Magento\Backend\Model\Url::SECRET_KEY_PARAM_NAME, null);
        if (!$secretKey || $secretKey != $this->_backendUrl->getSecretKey()) {
            return false;
        }
        return true;
    }

    /**
     * Render specified template
     *
     * @param string $tplName
     * @param array $data parameters required by template
     */
    protected function _outTemplate($tplName, $data = array())
    {
        $this->_initLayoutMessages('Magento\Backend\Model\Session');
        $block = $this->getLayout()->createBlock('Magento\Backend\Block\Template')->setTemplate("{$tplName}.phtml");
        foreach ($data as $index => $value) {
            $block->assign($index, $value);
        }
        $html = $block->toHtml();
        $this->_objectManager->get('Magento\Core\Model\Translate')->processResponseBody($html);
        $this->getResponse()->setBody($html);
    }

    /**
     * Declare headers and content file in response for file download
     *
     * @param string $fileName
     * @param string|array $content set to null to avoid starting output, $contentLength should be set explicitly in
     * that case
     * @param string $contentType
     * @param int $contentLength    explicit content length, if strlen($content) isn't applicable
     * @return \Magento\Backend\Controller\AbstractAction
     */
    protected function _prepareDownloadResponse($fileName, $content, $contentType = 'application/octet-stream',
        $contentLength = null
    ) {
        if ($this->_auth->getAuthStorage()->isFirstPageAfterLogin()) {
            $this->_redirect($this->_backendUrl->getStartupPageUrl());
            return $this;
        }
        return parent::_prepareDownloadResponse($fileName, $content, $contentType, $contentLength);
    }
}
