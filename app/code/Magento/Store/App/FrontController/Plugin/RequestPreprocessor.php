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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Store\App\FrontController\Plugin;

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
     * @var \Magento\Framework\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @param \Magento\Framework\StoreManagerInterface $storeManager
     * @param \Magento\Framework\UrlInterface $url
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\App\ResponseFactory $responseFactory
     */
    public function __construct(
        \Magento\Framework\StoreManagerInterface $storeManager,
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
        if (!$request->isPost() && $this->_isBaseUrlCheckEnabled()) {
            $baseUrl = $this->_storeManager->getStore()->getBaseUrl(
                \Magento\Framework\UrlInterface::URL_TYPE_WEB,
                $this->_storeManager->getStore()->isCurrentlySecure()
            );
            if ($baseUrl) {
                $uri = parse_url($baseUrl);
                if (!$this->_isBaseUrlCorrect($uri, $request)) {
                    $redirectUrl = $this->_url->getRedirectUrl(
                        $this->_url->getUrl(ltrim($request->getPathInfo(), '/'), array('_nosid' => true))
                    );
                    $redirectCode = (int)$this->_scopeConfig->getValue(
                        'web/url/redirect_to_base',
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                    ) !== 301 ? 302 : 301;

                    $response = $this->_responseFactory->create();
                    $response->setRedirect($redirectUrl, $redirectCode);
                    return $response;
                }
            }
        }
        $request->setDispatched(false);

        return $proceed($request);
    }

    /**
     * Is base url check enabled
     *
     * @return bool
     */
    protected function _isBaseUrlCheckEnabled()
    {
        return (bool)$this->_scopeConfig->getValue(
            'web/url/redirect_to_base',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Check if base url enabled
     *
     * @param array $uri
     * @param \Magento\Framework\App\Request\Http $request
     * @return bool
     */
    protected function _isBaseUrlCorrect($uri, $request)
    {
        $requestUri = $request->getRequestUri() ? $request->getRequestUri() : '/';
        return (!isset(
            $uri['scheme']
        ) || $uri['scheme'] === $request->getScheme()) && (!isset(
            $uri['host']
        ) || $uri['host'] === $request->getHttpHost()) && (!isset(
            $uri['path']
        ) || strpos(
            $requestUri,
            $uri['path']
        ) !== false);
    }
}
