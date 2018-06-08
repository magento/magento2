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

/**
 * Handles store switching procedure and detects url for final redirect after store switching.
 * It also cleans service data (from_store, to_store, SID) from current url.
 */
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
     * @var \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
     */
    private $cookieMetadataFactory;

    /**
     * @var \Magento\Framework\Stdlib\CookieManagerInterface
     */
    private $cookieManager;

    /**
     * @param StoreCookieManagerInterface $storeCookieManager
     * @param HttpContext $httpContext
     * @param StoreManagerInterface $storeManager
     * @param UrlHelper $urlHelper
     * @param \Magento\Framework\Session\Generic $session
     * @param \Magento\Framework\Session\SidResolverInterface $sidResolver
     * @param \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory
     * @param \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager
     */
    public function __construct(
        StoreCookieManagerInterface $storeCookieManager,
        HttpContext $httpContext,
        StoreManagerInterface $storeManager,
        UrlHelper $urlHelper,
        \Magento\Framework\Session\Generic $session,
        \Magento\Framework\Session\SidResolverInterface $sidResolver,
        \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager
    ) {
        $this->storeCookieManager = $storeCookieManager;
        $this->httpContext = $httpContext;
        $this->storeManager = $storeManager;
        $this->urlHelper = $urlHelper;
        $this->session = $session;
        $this->sidResolver = $sidResolver;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->cookieManager = $cookieManager;
    }

    /**
     * @param StoreInterface $fromStore store where we came from
     * @param StoreInterface $targetStore store where to go to
     * @param string $redirectUrl original url requested for redirect after switching
     * @return string url to be redirected after switching
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Stdlib\Cookie\CookieSizeLimitReachedException
     * @throws \Magento\Framework\Stdlib\Cookie\FailureToSendException
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
        if ($defaultStoreView !== null) {
            if ($defaultStoreView->getId() === $targetStore->getId()) {
                $this->storeCookieManager->deleteStoreCookie($targetStore);
            } else {
                $this->httpContext->setValue(Store::ENTITY, $targetStore->getCode(), $defaultStoreView->getCode());
                $this->storeCookieManager->setStoreCookie($targetStore);
            }
        }
        // set cookie to get customer data after store switching
        $publicCookieMetadata = $this->cookieMetadataFactory->createPublicCookieMetadata()
            ->setDurationOneYear()
            ->setPath('/')
            ->setSecure(false)
            ->setHttpOnly(false);
        $this->cookieManager->setPublicCookie(
            \Magento\Framework\App\PageCache\Version::COOKIE_NAME,
            'should_be_updated',
            $publicCookieMetadata
        );

        return $targetUrl;
    }
}
