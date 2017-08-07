<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Model;

/**
 * Verifies that the requested URL matches to base URL of store.
 * @since 2.1.0
 */
class BaseUrlChecker
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     * @since 2.1.0
     */
    private $scopeConfig;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @since 2.1.0
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
     * @since 2.1.0
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
     * @since 2.1.0
     */
    public function isEnabled()
    {
        return (bool) $this->scopeConfig->getValue(
            'web/url/redirect_to_base',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
}
