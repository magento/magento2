<?php
/***
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Router\PathConfigInterface;
use Magento\Framework\Url;
use Magento\Framework\Url\SecurityInfoInterface;

class PathConfig implements PathConfigInterface
{
    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param SecurityInfoInterface $urlSecurityInfo
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly SecurityInfoInterface $urlSecurityInfo,
        private readonly StoreManagerInterface $storeManager
    ) {
    }

    /**
     * {@inheritdoc}
     *
     * @param RequestInterface $request
     * @return string
     */
    public function getCurrentSecureUrl(RequestInterface $request)
    {
        $alias = $request->getAlias(Url::REWRITE_REQUEST_PATH_ALIAS) ?: $request->getPathInfo();
        return $this->storeManager->getStore()->getBaseUrl('link', true) . ltrim($alias, '/');
    }

    /**
     * {@inheritdoc}
     *
     * @param string $path
     * @return bool
     */
    public function shouldBeSecure($path)
    {
        return parse_url(
            $this->scopeConfig->getValue(
                Store::XML_PATH_UNSECURE_BASE_URL,
                ScopeInterface::SCOPE_STORE
            ),
            PHP_URL_SCHEME
        ) === 'https'
        || $this->scopeConfig->isSetFlag(
            Store::XML_PATH_SECURE_IN_FRONTEND,
            ScopeInterface::SCOPE_STORE
        ) && parse_url(
            $this->scopeConfig->getValue(
                Store::XML_PATH_SECURE_BASE_URL,
                ScopeInterface::SCOPE_STORE
            ),
            PHP_URL_SCHEME
        ) == 'https' && $this->urlSecurityInfo->isSecure($path);
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getDefaultPath()
    {
        $store = $this->storeManager->getStore();
        $value = $this->scopeConfig->getValue('web/default/front', ScopeInterface::SCOPE_STORE, $store);
        return $value;
    }
}
