<?php
/**
 * RSS Backend Authentication plugin
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Rss\App\Action\Plugin;

use Magento\Backend\App\AbstractAction;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Exception\AuthenticationException;

/**
 * Class BackendAuthentication
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class BackendAuthentication extends \Magento\Backend\App\Action\Plugin\Authentication
{
    /**
     * @var \Magento\Framework\HTTP\Authentication
     */
    protected $httpAuthentication;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Magento\Framework\AuthorizationInterface
     */
    protected $authorization;

    /**
     * @var array
     */
    protected $aclResources;

    /**
     * @param \Magento\Backend\Model\Auth $auth
     * @param \Magento\Backend\Model\UrlInterface $url
     * @param ResponseInterface $response
     * @param \Magento\Framework\App\ActionFlag $actionFlag
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Backend\Model\UrlInterface $backendUrl
     * @param \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory
     * @param \Magento\Backend\App\BackendAppList $backendAppList
     * @param \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
     * @param \Magento\Framework\HTTP\Authentication $httpAuthentication
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\AuthorizationInterface $authorization
     * @param array $aclResources
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Backend\Model\Auth $auth,
        \Magento\Backend\Model\UrlInterface $url,
        ResponseInterface $response,
        \Magento\Framework\App\ActionFlag $actionFlag,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Backend\Model\UrlInterface $backendUrl,
        \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory,
        \Magento\Backend\App\BackendAppList $backendAppList,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Magento\Framework\HTTP\Authentication $httpAuthentication,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\AuthorizationInterface $authorization,
        array $aclResources
    ) {
        $this->httpAuthentication = $httpAuthentication;
        $this->logger = $logger;
        $this->authorization = $authorization;
        $this->aclResources = $aclResources;
        parent::__construct(
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
    }

    /**
     * Replace standard admin login form with HTTP Basic authentication
     *
     * @param AbstractAction $subject
     * @param callable $proceed
     * @param RequestInterface $request
     * @return ResponseInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function aroundDispatch(AbstractAction $subject, \Closure $proceed, RequestInterface $request)
    {
        $resource = isset($this->aclResources[$request->getControllerName()])
            ? isset($this->aclResources[$request->getControllerName()][$request->getActionName()])
                ? $this->aclResources[$request->getControllerName()][$request->getActionName()]
                : $this->aclResources[$request->getControllerName()]
            : null;

        $type = $request->getParam('type');
        $resourceType = isset($this->aclResources[$type]) ? $this->aclResources[$type] : null;

        if (!$resource || !$resourceType) {
            return parent::aroundDispatch($subject, $proceed, $request);
        }

        $session = $this->_auth->getAuthStorage();

        // Try to login using HTTP-authentication
        if (!$session->isLoggedIn()) {
            list($login, $password) = $this->httpAuthentication->getCredentials();
            try {
                $this->_auth->login($login, $password);
            } catch (AuthenticationException $e) {
                $this->logger->critical($e);
            }
        }

        // Verify if logged in and authorized
        if (!$session->isLoggedIn() || !$this->authorization->isAllowed($resource)
            || !$this->authorization->isAllowed($resourceType)) {
            $this->httpAuthentication->setAuthenticationFailed('RSS Feeds');
            return $this->_response;
        }

        return parent::aroundDispatch($subject, $proceed, $request);
    }
}
