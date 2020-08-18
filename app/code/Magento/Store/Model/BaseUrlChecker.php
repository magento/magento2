<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Model;

use Magento\Store\Model\ScopeInterface;

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
     * BaseUrlChecker constructor.
     *
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
        $isValidPath = !isset($uri['path']) || strpos($requestUri, (string) $uri['path']) !== false;
        return $isValidSchema && $isValidHost && $isValidPath;
    }

    /**
     * Checks whether base URL verification is enabled or not.
     *
     * @return bool
     */
    public function isEnabled()
    {
        return $this->scopeConfig->isSetFlag(
            'web/url/redirect_to_base',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Checks whether frontend is completely secure or not.
     *
     * @return bool
     */
    public function isFrontendSecure()
    {
        $baseUrl = $this->scopeConfig->getValue(
            'web/unsecure/base_url',
            ScopeInterface::SCOPE_STORE
        );
        $baseUrlParts = explode('://', $baseUrl);
        $baseUrlProtocol = array_shift($baseUrlParts);
        $isSecure = $this->scopeConfig->isSetFlag(
            'web/secure/use_in_frontend',
            ScopeInterface::SCOPE_STORE
        );

        return $isSecure && $baseUrlProtocol == 'https';
    }
}
