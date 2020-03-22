<?php
/**
 * RSS Backend Authentication plugin
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Rss\App\Action\Plugin;

use Closure;
use Magento\Backend\App\BackendAppList;
use Magento\Backend\Model\Auth;
use Magento\Backend\Model\UrlInterface;
use Magento\Framework\App\ActionFlag;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\HTTP\Authentication;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * @FIXME Plugins should never inherit after other plugins o.O
 *
 * @api
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 100.0.2
 */
class BackendAuthentication extends \Magento\Backend\App\Action\Plugin\BackendActionAuthenticationPlugin
{
    /**
     * @var Authentication
     */
    protected $httpAuthentication;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var AuthorizationInterface
     */
    protected $authorization;

    /**
     * @var array
     */
    protected $aclResources;
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @param RequestInterface $request
     * @param Auth $auth
     * @param UrlInterface $url
     * @param ResponseInterface $response
     * @param ActionFlag $actionFlag
     * @param MessageManagerInterface $messageManager
     * @param UrlInterface $backendUrl
     * @param RedirectFactory $resultRedirectFactory
     * @param BackendAppList $backendAppList
     * @param FormKeyValidator $formKeyValidator
     * @param Authentication $httpAuthentication
     * @param LoggerInterface $logger
     * @param AuthorizationInterface $authorization
     * @param array $aclResources
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        RequestInterface $request,
        Auth $auth,
        UrlInterface $url,
        ResponseInterface $response,
        ActionFlag $actionFlag,
        MessageManagerInterface $messageManager,
        UrlInterface $backendUrl,
        RedirectFactory $resultRedirectFactory,
        BackendAppList $backendAppList,
        FormKeyValidator $formKeyValidator,
        Authentication $httpAuthentication,
        LoggerInterface $logger,
        AuthorizationInterface $authorization,
        array $aclResources
    ) {
        $this->httpAuthentication = $httpAuthentication;
        $this->logger = $logger;
        $this->authorization = $authorization;
        $this->aclResources = $aclResources;
        parent::__construct(
            $request,
            $auth,
            $url,
            $response,
            $actionFlag,
            $messageManager,
            $backendUrl,
            $resultRedirectFactory,
            $backendAppList,
            $formKeyValidator
        );
        $this->request = $request;
    }

    /**
     * Replace standard admin login form with HTTP Basic authentication
     *
     * @param ActionInterface $subject
     * @param Closure $proceed
     * @return ResponseInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function aroundExecute(ActionInterface $subject, Closure $proceed)
    {
        $resource = isset($this->aclResources[$this->request->getControllerName()])
            ? isset($this->aclResources[$this->request->getControllerName()][$this->request->getActionName()])
                ? $this->aclResources[$this->request->getControllerName()][$this->request->getActionName()]
                : $this->aclResources[$this->request->getControllerName()]
            : null;

        $type = $this->request->getParam('type');
        $resourceType = isset($this->aclResources[$type]) ? $this->aclResources[$type] : null;

        if (!$resource || !$resourceType) {
            return parent::aroundExecute($subject, $proceed);
        }

        $session = $this->auth->getAuthStorage();

        // Try to login using HTTP-authentication
        if (!$session->isLoggedIn()) {
            list($login, $password) = $this->httpAuthentication->getCredentials();
            try {
                $this->auth->login($login, $password);
            } catch (AuthenticationException $e) {
                $this->logger->critical($e);
            }
        }

        // Verify if logged in and authorized
        if (!$session->isLoggedIn() || !$this->authorization->isAllowed($resource)
            || !$this->authorization->isAllowed($resourceType)) {
            $this->httpAuthentication->setAuthenticationFailed('RSS Feeds');
            return $this->response;
        }

        return parent::aroundExecute($subject, $proceed);
    }
}
