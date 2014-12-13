<?php
/**
 * RSS Backend Authentication plugin
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Rss\App\Action\Plugin;

use Magento\Backend\App\AbstractAction;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;

/**
 * Class BackendAuthentication
 * @package Magento\Rss\App\Action\Plugin
 */
class BackendAuthentication extends \Magento\Backend\App\Action\Plugin\Authentication
{
    /**
     * @var \Magento\Framework\HTTP\Authentication
     */
    protected $httpAuthentication;

    /**
     * @var \Magento\Framework\Logger
     */
    protected $logger;

    /**
     * @var \Magento\Framework\AuthorizationInterface
     */
    protected $authorization;

    /**
     * @var array
     */
    protected $aclResources = [
        'feed' => 'Magento_Rss::rss',
    ];

    /**
     * @param \Magento\Backend\Model\Auth $auth
     * @param \Magento\Backend\Model\UrlInterface $url
     * @param ResponseInterface $response
     * @param \Magento\Framework\App\ActionFlag $actionFlag
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Framework\HTTP\Authentication $httpAuthentication
     * @param \Magento\Framework\Logger $logger
     * @param \Magento\Framework\AuthorizationInterface $authorization
     */
    public function __construct(
        \Magento\Backend\Model\Auth $auth,
        \Magento\Backend\Model\UrlInterface $url,
        ResponseInterface $response,
        \Magento\Framework\App\ActionFlag $actionFlag,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\HTTP\Authentication $httpAuthentication,
        \Magento\Framework\Logger $logger,
        \Magento\Framework\AuthorizationInterface $authorization
    ) {
        $this->httpAuthentication = $httpAuthentication;
        $this->logger = $logger;
        $this->authorization = $authorization;
        parent::__construct($auth, $url, $response, $actionFlag, $messageManager);
    }

    /**
     * Replace standard admin login form with HTTP Basic authentication
     *
     * @param AbstractAction $subject
     * @param callable $proceed
     * @param RequestInterface $request
     * @return ResponseInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundDispatch(AbstractAction $subject, \Closure $proceed, RequestInterface $request)
    {
        $resource = isset($this->aclResources[$request->getControllerName()])
            ? isset($this->aclResources[$request->getControllerName()][$request->getActionName()])
                ? $this->aclResources[$request->getControllerName()][$request->getActionName()]
                : $this->aclResources[$request->getControllerName()]
            : null;

        if (!$resource) {
            return parent::aroundDispatch($subject, $proceed, $request);
        }

        $session = $this->_auth->getAuthStorage();

        // Try to login using HTTP-authentication
        if (!$session->isLoggedIn()) {
            list($login, $password) = $this->httpAuthentication->getCredentials();
            try {
                $this->_auth->login($login, $password);
            } catch (\Magento\Backend\Model\Auth\Exception $e) {
                $this->logger->logException($e);
            }
        }

        // Verify if logged in and authorized
        if (!$session->isLoggedIn() || !$this->authorization->isAllowed($resource)) {
            $this->httpAuthentication->setAuthenticationFailed('RSS Feeds');
            return $this->_response;
        }

        return parent::aroundDispatch($subject, $proceed, $request);
    }
}
