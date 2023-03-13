<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\App\FrontController\Plugin;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\FrontController;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseFactory;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\BaseUrlChecker;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class RequestPreprocessor
 */
class RequestPreprocessor
{
    /**
     * @var ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var ResponseFactory
     */
    protected $_responseFactory;

    /**
     * @var UrlInterface
     */
    protected $_url;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var BaseUrlChecker
     */
    private $baseUrlChecker;

    /**
     * @param StoreManagerInterface $storeManager
     * @param UrlInterface $url
     * @param ScopeConfigInterface $scopeConfig
     * @param ResponseFactory $responseFactory
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        UrlInterface $url,
        ScopeConfigInterface $scopeConfig,
        ResponseFactory $responseFactory
    ) {
        $this->_storeManager = $storeManager;
        $this->_url = $url;
        $this->_scopeConfig = $scopeConfig;
        $this->_responseFactory = $responseFactory;
    }

    /**
     * Auto-redirect to base url (without SID) if the requested url doesn't match it.
     *
     * By default this feature is enabled in configuration.
     *
     * @param FrontController $subject
     * @param callable $proceed
     * @param RequestInterface $request
     *
     * @return ResponseInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundDispatch(
        FrontController $subject,
        \Closure $proceed,
        RequestInterface $request
    ) {
        if ($this->isHttpsRedirect($request) || (!$request->isPost() && $this->getBaseUrlChecker()->isEnabled())) {
            $baseUrl = $this->_storeManager->getStore()->getBaseUrl(
                UrlInterface::URL_TYPE_WEB,
                $this->_storeManager->getStore()->isCurrentlySecure()
            );
            if ($baseUrl) {
                // phpcs:disable Magento2.Functions.DiscouragedFunction
                $uri = parse_url($baseUrl);
                if (!$this->getBaseUrlChecker()->execute($uri, $request)) {
                    $redirectUrl = $this->_url->getRedirectUrl(
                        $this->_url->getDirectUrl(ltrim($request->getPathInfo(), '/'), ['_nosid' => true])
                    );
                    $redirectCode = (int)$this->_scopeConfig->getValue(
                        'web/url/redirect_to_base',
                        ScopeInterface::SCOPE_STORE
                    ) !== 301 ? 302 : 301;

                    $response = $this->_responseFactory->create();
                    $response->setRedirect($redirectUrl, $redirectCode);
                    $response->setNoCacheHeaders();
                    return $response;
                }
            }
        }
        $request->setDispatched(false);

        return $proceed($request);
    }

    /**
     * Gets base URL checker.
     *
     * @return BaseUrlChecker
     * @deprecated 100.1.0
     */
    private function getBaseUrlChecker()
    {
        if ($this->baseUrlChecker === null) {
            $this->baseUrlChecker = ObjectManager::getInstance()->get(
                BaseUrlChecker::class
            );
        }

        return $this->baseUrlChecker;
    }

    /**
     * Check is request should be redirected, if https enabled.
     *
     * @param RequestInterface $request
     * @return bool
     */
    private function isHttpsRedirect(RequestInterface $request)
    {
        $result = false;
        if ($this->getBaseUrlChecker()->isFrontendSecure() && $request->isPost() && !$request->isSecure()) {
            $result = true;
        }

        return $result;
    }
}
