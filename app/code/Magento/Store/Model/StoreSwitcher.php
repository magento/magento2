<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Store\Model;

use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Api\StoreResolverInterface;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Store\Api\StoreCookieManagerInterface;
use Magento\Framework\Url\Helper\Data as UrlHelper;

class StoreSwitcher
{
    /**
     * @var StoreCookieManagerInterface
     */
    private $storeCookieManager;

    /**
     * @var HttpContext
     */
    private $httpContext;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var UrlHelper
     */
    private $urlHelper;

    /**
     * @var \Magento\Framework\Session\Generic
     */
    private $session;

    /**
     * @var \Magento\Framework\Session\SidResolverInterface
     */
    private $sidResolver;

    /**
     * @param StoreCookieManagerInterface $storeCookieManager
     * @param HttpContext $httpContext
     * @param StoreManagerInterface $storeManager
     * @param UrlHelper $urlHelper
     * @param \Magento\Framework\Session\Generic $session
     * @param \Magento\Framework\Session\SidResolverInterface $sidResolver
     */
    public function __construct(
        StoreCookieManagerInterface $storeCookieManager,
        HttpContext $httpContext,
        StoreManagerInterface $storeManager,
        UrlHelper $urlHelper,
        \Magento\Framework\Session\Generic $session,
        \Magento\Framework\Session\SidResolverInterface $sidResolver
    ) {
        $this->storeCookieManager = $storeCookieManager;
        $this->httpContext = $httpContext;
        $this->storeManager = $storeManager;
        $this->urlHelper = $urlHelper;
        $this->session = $session;
        $this->sidResolver = $sidResolver;
    }

    /**
     * @param StoreInterface $fromStore store where we came from
     * @param StoreInterface $targetStore store where to go to
     * @param string $redirectUrl original url requested for redirect after switching
     * @return string url to be redirected after switching
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function switch(StoreInterface $fromStore, StoreInterface $targetStore, string $redirectUrl): string
    {
        $targetUrl = $redirectUrl;
        // Remove SID, ___from_store, ___store from url
        $sidName = $this->sidResolver->getSessionIdQueryParam($this->session);
        $targetUrl = $this->urlHelper->removeRequestParam($targetUrl, $sidName);
        $targetUrl = $this->urlHelper->removeRequestParam($targetUrl, '___from_store');
        $targetUrl = $this->urlHelper->removeRequestParam($targetUrl, StoreResolverInterface::PARAM_NAME);

        $defaultStoreView = $this->storeManager->getDefaultStoreView();
        if ($defaultStoreView->getId() == $targetStore->getId()) {
            $this->storeCookieManager->deleteStoreCookie($targetStore);
        } else {
            $this->httpContext->setValue(Store::ENTITY, $targetStore->getCode(), $defaultStoreView->getCode());
            $this->storeCookieManager->setStoreCookie($targetStore);
        }

        return $targetUrl;
    }
}
