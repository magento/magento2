<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\App\Action\Plugin;

use Closure;
use Magento\Backend\App\BackendApp;
use Magento\Backend\App\BackendAppList;
use Magento\Backend\Model\Auth as BackendAuthModel;
use Magento\Backend\Model\UrlInterface as BackendUrlInterface;
use Magento\Framework\App\ActionFlag;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\Request\Http as RequestHttp;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class BackendActionAuthenticationPlugin
{
    const PARAM_BACKEND_APP = 'app';
    /**
     * @var BackendAuthModel
     */
    protected $_auth;

    /**
     * @var string[]
     */
    protected $_openActions = [
        'forgotpassword',
        'resetpassword',
        'resetpasswordpost',
        'logout',
        'refresh', // captcha refresh
    ];

    /**
     * @var BackendUrlInterface
     */
    protected $_url;

    /**
     * @var ResponseInterface
     */
    protected $_response;

    /**
     * @var ActionFlag
     */
    protected $_actionFlag;

    /**
     * @var MessageManagerInterface
     */
    protected $messageManager;

    /**
     * @var BackendUrlInterface
     */
    protected $backendUrl;

    /**
     * @var BackendAppList
     */
    protected $backendAppList;

    /**
     * @var RedirectFactory
     */
    protected $resultRedirectFactory;

    /**
     * @var Validator
     */
    protected $formKeyValidator;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var ForwardFactory
     */
    private $forwardFactory;

    /**
     * @param RequestInterface $request
     * @param BackendAuthModel $auth
     * @param BackendUrlInterface $url
     * @param ResponseInterface $response
     * @param ActionFlag $actionFlag
     * @param MessageManagerInterface $messageManager
     * @param BackendUrlInterface $backendUrl
     * @param RedirectFactory $resultRedirectFactory
     * @param BackendAppList $backendAppList
     * @param Validator $formKeyValidator
     * @param ForwardFactory $forwardFactory
     */
    public function __construct(
        RequestInterface $request,
        BackendAuthModel $auth,
        BackendUrlInterface $url,
        ResponseInterface $response,
        ActionFlag $actionFlag,
        MessageManagerInterface $messageManager,
        BackendUrlInterface $backendUrl,
        RedirectFactory $resultRedirectFactory,
        BackendAppList $backendAppList,
        Validator $formKeyValidator,
        ForwardFactory $forwardFactory
    ) {
        $this->_auth = $auth;
        $this->_url = $url;
        $this->_response = $response;
        $this->_actionFlag = $actionFlag;
        $this->messageManager = $messageManager;
        $this->backendUrl = $backendUrl;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->backendAppList = $backendAppList;
        $this->formKeyValidator = $formKeyValidator;
        $this->request = $request;
        $this->forwardFactory = $forwardFactory;
    }

    private function isCurrentActionOpen(): bool
    {
        return in_array($this->request->getActionName(), $this->_openActions);
    }

    /**
     * @param ActionInterface $subject
     * @param Closure $proceed
     *
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundExecute(ActionInterface $subject, Closure $proceed)
    {
        if ($this->isCurrentActionOpen()) {
            $this->_auth->getAuthStorage()->refreshAcl();
            return $proceed();
        }

        $this->reloadUser();
        if (!$this->_auth->isLoggedIn()) {
            $this->_processNotLoggedInUser($this->request);
            return $proceed();
        }

        $this->_auth->getAuthStorage()->prolong();

        $backendApp = $this->getBackendApp();

        if ($backendApp) {
            $resultRedirect = $this->resultRedirectFactory->create();
            $baseUrl = RequestHttp::getUrlNoScript($this->backendUrl->getBaseUrl());
            $baseUrl = $baseUrl . $backendApp->getStartupPage();
            return $resultRedirect->setUrl($baseUrl);
        }

        $this->_auth->getAuthStorage()->refreshAcl();
        return $proceed();
    }

    /**
     * Process not logged in user data
     *
     * @param RequestInterface $request
     */
    protected function _processNotLoggedInUser(RequestInterface $request)
    {
        $isRedirectNeeded = false;
        if ($request->getPost('login')) {
            if ($this->formKeyValidator->validate($request)) {
                if ($this->_performLogin($request)) {
                    $isRedirectNeeded = $this->_redirectIfNeededAfterLogin($request);
                }
            } else {
                $this->_actionFlag->set('', ActionInterface::FLAG_NO_DISPATCH, true);
                $this->_response->setRedirect($this->_url->getCurrentUrl());
                $this->messageManager->addErrorMessage(__('Invalid Form Key. Please refresh the page.'));
                $isRedirectNeeded = true;
            }
        }
        if (!$isRedirectNeeded && !$request->isForwarded()) {
            $request->setForwarded(true)
                ->setRouteName('adminhtml')
                ->setControllerName('auth')
                ->setDispatched(false);

            if ($request->getParam('isIframe')) {
                $request->setActionName('deniedIframe');
            } elseif ($request->getParam('isAjax')) {
                $request->setActionName('deniedJson');
            } else {
                $request->setActionName('login');
            }
        }
    }

    /**
     * Performs login, if user submitted login form
     *
     * @param RequestInterface $request
     * @return bool
     */
    protected function _performLogin(RequestInterface $request)
    {
        $outputValue = true;
        $postLogin = $request->getPost('login');
        $username = isset($postLogin['username']) ? $postLogin['username'] : '';
        $password = isset($postLogin['password']) ? $postLogin['password'] : '';
        $request->setPostValue('login', null);

        try {
            $this->_auth->login($username, $password);
        } catch (AuthenticationException $e) {
            if (!$request->getParam('messageSent')) {
                $this->messageManager->addErrorMessage($e->getMessage());
                $request->setParam('messageSent', true);
                $outputValue = false;
            }
        }
        return $outputValue;
    }

    /**
     * Checks, whether Magento requires redirection after successful admin login, and redirects user, if needed
     *
     * @param RequestInterface $request
     * @return bool
     */
    protected function _redirectIfNeededAfterLogin(RequestInterface $request)
    {
        $requestUri = null;

        // Checks, whether secret key is required for admin access or request uri is explicitly set
        if ($this->_url->useSecretKey()) {
            $requestUri = $this->_url->getUrl('*/*/*', ['_current' => true]);
        } elseif ($request) {
            $requestUri = $request->getRequestUri();
        }

        if (!$requestUri) {
            return false;
        }

        $this->_response->setRedirect($requestUri);
        $this->_actionFlag->set('', ActionInterface::FLAG_NO_DISPATCH, true);
        return true;
    }

    private function reloadUser(): void
    {
        if ($this->_auth->getUser()) {
            $this->_auth->getUser()->reload();
        }
    }

    /**
     * @return BackendApp|null
     */
    private function getBackendApp(): ?BackendApp
    {
        $backendApp = null;
        if ($this->request->getParam(self::PARAM_BACKEND_APP)) {
            $backendApp = $this->backendAppList->getCurrentApp();
        }
        return $backendApp;
    }
}
