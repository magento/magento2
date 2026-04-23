<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */

namespace Magento\Backend\App;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Helper\Data as BackendHelper;
use Magento\Backend\Model\Auth;
use Magento\Backend\Model\Session;
use Magento\Backend\Model\UrlInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\Encryption\Helper\Security;

/**
 * Generic backend controller
 *
 * @deprecated 102.0.0 Use \Magento\Framework\App\ActionInterface
 *
 * phpcs:disable Magento2.Classes.AbstractApi
 * @api
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 100.0.2
 */
abstract class AbstractAction extends \Magento\Framework\App\Action\Action
{
    /**
     * Name of "is URLs checked" flag
     */
    public const FLAG_IS_URLS_CHECKED = 'check_url_settings';

    /**
     * Session namespace to refer in other places
     */
    public const SESSION_NAMESPACE = 'adminhtml';

    /**
     * Authorization level of a basic admin session
     */
    public const ADMIN_RESOURCE = 'Magento_Backend::admin';

    /**
     * Array of actions which can be processed without secret key validation
     *
     * @var array
     */
    protected $_publicActions = [];

    /**
     * Namespace for session.
     *
     * @var string
     */
    protected $_sessionNamespace = self::SESSION_NAMESPACE;

    /**
     * @var BackendHelper
     */
    protected $_helper;

    /**
     * @var Session
     */
    protected $_session;

    /**
     * @var AuthorizationInterface
     */
    protected $_authorization;

    /**
     * @var Auth
     */
    protected $_auth;

    /**
     * @var UrlInterface
     */
    protected $_backendUrl;

    /**
     * @var ResolverInterface
     */
    protected $_localeResolver;

    /**
     * @var bool
     */
    protected $_canUseBaseUrl;

    /**
     * @var FormKeyValidator
     */
    protected $_formKeyValidator;

    /**
     * @param Context $context
     */
    public function __construct(Context $context)
    {
        parent::__construct($context);
        $this->_authorization = $context->getAuthorization();
        $this->_auth = $context->getAuth();
        $this->_helper = $context->getHelper();
        $this->_backendUrl = $context->getBackendUrl();
        $this->_formKeyValidator = $context->getFormKeyValidator();
        $this->_localeResolver = $context->getLocaleResolver();
        $this->_canUseBaseUrl = $context->getCanUseBaseUrl();
        $this->_session = $context->getSession();
    }

    /**
     * Dispatches the Action
     *
     * @param RequestInterface $request
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function dispatch(RequestInterface $request)
    {
        if ($request->isDispatched() && $request->getActionName() !== 'denied' && !$this->_isAllowed()) {
            $this->_response->setStatusHeader(403, '1.1', 'Forbidden');
            if (!$this->_auth->isLoggedIn()) {
                return $this->_redirect('*/auth/login');
            }

            $this->_view->loadLayout(['default', 'adminhtml_denied'], true, true, false);
            $this->_view->renderLayout();
            $this->_request->setDispatched(true);

            return $this->_response;
        }

        if ($this->_isUrlChecked()) {
            $this->_actionFlag->set('', self::FLAG_IS_URLS_CHECKED, true);
        }

        $this->_processLocaleSettings();

        // Need to preload isFirstPageAfterLogin (see https://github.com/magento/magento2/issues/15510)
        if ($this->_auth->isLoggedIn()) {
            $this->_auth->getAuthStorage()->isFirstPageAfterLogin();
        }

        return parent::dispatch($request);
    }

    /**
     * Check url keys. If non valid - redirect
     *
     * @return bool
     *
     * @see \Magento\Backend\App\Request\BackendValidator for default request validation.
     */
    public function _processUrlKeys()
    {
        $_isValidFormKey = true;
        $_isValidSecretKey = true;
        $_keyErrorMsg = '';
        if ($this->_auth->isLoggedIn()) {
            if ($this->getRequest()->isPost()) {
                $_isValidFormKey = $this->_formKeyValidator->validate($this->getRequest());
                $_keyErrorMsg = __('Invalid Form Key. Please refresh the page.');
            } elseif ($this->_backendUrl->useSecretKey()) {
                $_isValidSecretKey = $this->_validateSecretKey();
                $_keyErrorMsg = __('You entered an invalid Secret Key. Please refresh the page.');
            }
        }
        if (!$_isValidFormKey || !$_isValidSecretKey) {
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
            $this->_actionFlag->set('', self::FLAG_NO_POST_DISPATCH, true);
            if ($this->getRequest()->getQuery('isAjax', false) || $this->getRequest()->getQuery('ajax', false)) {
                $this->getResponse()->representJson(
                    $this->_objectManager->get(
                        \Magento\Framework\Json\Helper\Data::class
                    )->jsonEncode(
                        ['error' => true, 'message' => $_keyErrorMsg]
                    )
                );
            } else {
                $this->_redirect($this->_backendUrl->getStartupPageUrl());
            }
            return false;
        }
        return true;
    }

    /**
     * Generate url by route and parameters
     *
     * @param string $route
     * @param array $params
     * @return string
     */
    public function getUrl($route = '', $params = [])
    {
        return $this->_helper->getUrl($route, $params);
    }

    /**
     * Determines whether current user is allowed to access Action
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed(static::ADMIN_RESOURCE);
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
     * Returns instantiated Message\ManagerInterface.
     *
     * @return \Magento\Framework\Message\ManagerInterface
     */
    protected function getMessageManager()
    {
        return $this->messageManager;
    }

    /**
     * Define active menu item in menu block
     *
     * @param string $itemId current active menu item
     * @return $this
     */
    protected function _setActiveMenu($itemId)
    {
        /** @var $menuBlock \Magento\Backend\Block\Menu */
        $menuBlock = $this->_view->getLayout()->getBlock('menu');
        $menuBlock->setActive($itemId);
        $parents = $menuBlock->getMenuModel()->getParentItems($itemId);
        foreach ($parents as $item) {
            /** @var $item \Magento\Backend\Model\Menu\Item */
            $this->_view->getPage()->getConfig()->getTitle()->prepend($item->getTitle());
        }
        return $this;
    }

    /**
     * Adds element to Breadcrumbs block
     *
     * @param string $label
     * @param string $title
     * @param string|null $link
     * @return $this
     */
    protected function _addBreadcrumb($label, $title, $link = null)
    {
        $this->_view->getLayout()->getBlock('breadcrumbs')->addLink($label, $title, $link);
        return $this;
    }

    /**
     * Adds block to `content` block
     *
     * @param AbstractBlock $block
     * @return $this
     */
    protected function _addContent(AbstractBlock $block)
    {
        return $this->_moveBlockToContainer($block, 'content');
    }

    /**
     * Moves Block to `left` container
     *
     * @param AbstractBlock $block
     * @return $this
     */
    protected function _addLeft(AbstractBlock $block)
    {
        return $this->_moveBlockToContainer($block, 'left');
    }

    /**
     * Adds Block to `js` container
     *
     * @param AbstractBlock $block
     * @return $this
     */
    protected function _addJs(AbstractBlock $block)
    {
        return $this->_moveBlockToContainer($block, 'js');
    }

    /**
     * Set specified block as an anonymous child to specified container.
     *
     * @param AbstractBlock $block
     * @param string $containerName
     * @return $this
     */
    private function _moveBlockToContainer(AbstractBlock $block, $containerName)
    {
        $this->_view->getLayout()->setChild($containerName, $block->getNameInLayout(), '');
        return $this;
    }

    /**
     * Check whether url is checked
     *
     * @return bool
     */
    protected function _isUrlChecked()
    {
        return !$this->_actionFlag->get('', self::FLAG_IS_URLS_CHECKED)
            && !$this->getRequest()->isForwarded()
            && !$this->_getSession()->getIsUrlNotice(true)
            && !$this->_canUseBaseUrl;
    }

    /**
     * Set session locale, process force locale set through url params
     *
     * @return $this
     */
    protected function _processLocaleSettings()
    {
        $forceLocale = $this->getRequest()->getParam('locale', null);
        if ($this->_objectManager->get(\Magento\Framework\Validator\Locale::class)->isValid($forceLocale)) {
            $this->_getSession()->setSessionLocale($forceLocale);
        }

        if ($this->_getSession()->getLocale() === null) {
            $this->_getSession()->setLocale($this->_localeResolver->getLocale());
        }

        return $this;
    }

    /**
     * Set redirect into response
     *
     * @TODO MAGETWO-28356: Refactor controller actions to new ResultInterface
     * @param string $path
     * @param array $arguments
     * @return \Magento\Framework\App\ResponseInterface
     */
    protected function _redirect($path, $arguments = [])
    {
        $this->_getSession()->setIsUrlNotice($this->_actionFlag->get('', self::FLAG_IS_URLS_CHECKED));
        $this->getResponse()->setRedirect($this->getUrl($path, $arguments));
        return $this->getResponse();
    }

    /**
     * Forward to action
     *
     * @TODO MAGETWO-28356: Refactor controller actions to new ResultInterface
     * @param string $action
     * @param string|null $controller
     * @param string|null $module
     * @param array|null $params
     * @return void
     */
    protected function _forward($action, $controller = null, $module = null, ?array $params = null)
    {
        $this->_getSession()->setIsUrlNotice($this->_actionFlag->get('', self::FLAG_IS_URLS_CHECKED));
        parent::_forward($action, $controller, $module, $params);
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

        $secretKey = $this->getRequest()->getParam(UrlInterface::SECRET_KEY_PARAM_NAME, null);
        if (!$secretKey || !Security::compareStrings($secretKey, $this->_backendUrl->getSecretKey())) {
            return false;
        }
        return true;
    }
}
