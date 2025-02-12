<?php
/**
 * Copyright 2011 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Controller\Adminhtml\Auth;

use Magento\Backend\App\Area\FrontNameResolver;
use Magento\Backend\App\BackendAppList;
use Magento\Backend\Model\UrlFactory;
use Magento\Framework\App\Action\HttpGetActionInterface as HttpGet;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPost;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Request\Http;

/**
 * @api
 * @since 100.0.2
 */
class Login extends \Magento\Backend\Controller\Adminhtml\Auth implements HttpGet, HttpPost
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var FrontNameResolver
     */
    private $frontNameResolver;

    /**
     * @var BackendAppList
     */
    private $backendAppList;

    /**
     * @var UrlFactory
     */
    private $backendUrlFactory;

    /**
     * @var Http
     */
    private $http;

    /**
     * Constructor
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param FrontNameResolver|null $frontNameResolver
     * @param BackendAppList|null $backendAppList
     * @param UrlFactory|null $backendUrlFactory
     * @param Http|null $http
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        ?FrontNameResolver $frontNameResolver = null,
        ?BackendAppList $backendAppList = null,
        ?UrlFactory $backendUrlFactory = null,
        ?Http $http = null
    ) {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
        $this->frontNameResolver = $frontNameResolver ?? ObjectManager::getInstance()->get(FrontNameResolver::class);
        $this->backendAppList = $backendAppList ?? ObjectManager::getInstance()->get(BackendAppList::class);
        $this->backendUrlFactory = $backendUrlFactory ?? ObjectManager::getInstance()->get(UrlFactory::class);
        $this->http = $http ?? ObjectManager::getInstance()->get(Http::class);
    }

    /**
     * Administrator login action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        if ($this->_auth->isLoggedIn()) {
            if ($this->_auth->getAuthStorage()->isFirstPageAfterLogin()) {
                $this->_auth->getAuthStorage()->setIsFirstPageAfterLogin(true);
            }
            return $this->getRedirect($this->_backendUrl->getStartupPageUrl());
        }

        $requestUrl = $this->getRequest()->getUri();

        if (!$requestUrl->isValid() || !$this->isValidBackendUri()) {
            return $this->getRedirect($this->getUrl('*'));
        }

        return $this->resultPageFactory->create();
    }

    /**
     * Get redirect response
     *
     * @param string $path
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    private function getRedirect($path)
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath($path);
        return $resultRedirect;
    }

    /**
     * Verify if correct backend uri requested.
     *
     * @return bool
     */
    private function isValidBackendUri(): bool
    {
        $requestUri = $this->getRequest()->getRequestUri();
        $backendApp = $this->backendAppList->getCurrentApp();
        //phpcs:ignore Magento2.Functions.DiscouragedFunction
        $baseUrl = parse_url($this->backendUrlFactory->create()->getBaseUrl(), PHP_URL_PATH);
        if (!$backendApp) {
            $backendFrontName = $this->frontNameResolver->getFrontName();
        } else {
            //In case of application authenticating through the admin login, the script name should be removed
            //from the path, because application has own script.
            $baseUrl = $this->http->getUrlNoScript($baseUrl);
            $backendFrontName = $backendApp->getCookiePath();
        }

        return strpos($requestUri, $baseUrl . $backendFrontName) === 0;
    }
}
