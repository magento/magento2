<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\App\FrontController\Plugin;

/**
 * Class RequestPreprocessor
 */
class RequestPreprocessor
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Framework\App\ResponseFactory
     */
    protected $_responseFactory;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $_url;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Store\Model\BaseUrlChecker
     */
    private $baseUrlChecker;

    /**
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\UrlInterface $url
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\App\ResponseFactory $responseFactory
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\UrlInterface $url,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\ResponseFactory $responseFactory
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
     * @param \Magento\Framework\App\FrontController $subject
     * @param callable $proceed
     * @param \Magento\Framework\App\RequestInterface $request
     *
     * @return \Magento\Framework\App\ResponseInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundDispatch(
        \Magento\Framework\App\FrontController $subject,
        \Closure $proceed,
        \Magento\Framework\App\RequestInterface $request
    ) {
        if ($this->isHttpsRedirect($request) || (!$request->isPost() && $this->getBaseUrlChecker()->isEnabled())) {
            $baseUrl = $this->_storeManager->getStore()->getBaseUrl(
                \Magento\Framework\UrlInterface::URL_TYPE_WEB,
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
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE
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
     * @return \Magento\Store\Model\BaseUrlChecker
     * @deprecated 100.1.0
     */
    private function getBaseUrlChecker()
    {
        if ($this->baseUrlChecker === null) {
            $this->baseUrlChecker = \Magento\Framework\App\ObjectManager::getInstance()->get(
                \Magento\Store\Model\BaseUrlChecker::class
            );
        }

        return $this->baseUrlChecker;
    }

    /**
     * Check is request should be redirected, if https enabled.
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @return bool
     */
    private function isHttpsRedirect(\Magento\Framework\App\RequestInterface $request)
    {
        $result = false;
        if ($this->getBaseUrlChecker()->isFrontendSecure() && $request->isPost() && !$request->isSecure()) {
            $result = true;
        }

        return $result;
    }
}
