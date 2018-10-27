<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Model;

/**
 * Verifies that the requested URL matches to base URL of store.
 */
class BaseUrlChecker
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Performs verification.
     *
     * @param array $uri
     * @param \Magento\Framework\App\Request\Http $request
     * @return bool
     */
    public function execute($uri, $request)
    {
        $requestUri = $request->getRequestUri() ? $request->getRequestUri() : '/';
        $isValidSchema = !isset($uri['scheme']) || $uri['scheme'] === $request->getScheme();
        $isValidHost = !isset($uri['host']) || $uri['host'] === $request->getHttpHost();
        $isValidPath = !isset($uri['path']) || strpos($requestUri, $uri['path']) !== false;
        return $isValidSchema && $isValidHost && $isValidPath;
    }

    /**
     * Checks whether base URL verification is enabled or not.
     *
     * @return bool
     */
    public function isEnabled()
    {
        return (bool)$this->scopeConfig->getValue(
            'web/url/redirect_to_base',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Cheks whether frontend is completely secure or not.
     *
     * @return bool
     */
    public function isFrontendSecure()
    {
        $baseUrl = $this->scopeConfig->getValue(
            'web/unsecure/base_url',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $baseUrlParts = explode('://', $baseUrl);
        $baseUrlProtocol = array_shift($baseUrlParts);
        $isSecure = (bool) $this->scopeConfig->getValue(
            'web/secure/use_in_frontend',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        return $isSecure && $baseUrlProtocol == 'https';
    }
}
