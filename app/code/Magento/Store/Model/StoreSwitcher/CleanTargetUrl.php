<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Store\Model\StoreSwitcher;

use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Api\StoreResolverInterface;
use Magento\Store\Model\StoreSwitcherInterface;
use Magento\Framework\Url\Helper\Data as UrlHelper;

/**
 * Remove SID, from_store, store from target url.
 *
 * Used in store-switching process in HTML frontend.
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class CleanTargetUrl implements StoreSwitcherInterface
{
    /**
     * @var UrlHelper
     */
    private $urlHelper;

    /**
     * @param UrlHelper $urlHelper
     * @param \Magento\Framework\Session\Generic $session
     * @param \Magento\Framework\Session\SidResolverInterface $sidResolver
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        UrlHelper $urlHelper,
        \Magento\Framework\Session\Generic $session,
        \Magento\Framework\Session\SidResolverInterface $sidResolver
    ) {
        $this->urlHelper = $urlHelper;
    }

    /**
     * Generate target URL to switch stores through other mechanism then via URL params.
     *
     * @param StoreInterface $fromStore store where we came from
     * @param StoreInterface $targetStore store where to go to
     * @param string $redirectUrl original url requested for redirect after switching
     * @return string redirect url
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function switch(StoreInterface $fromStore, StoreInterface $targetStore, string $redirectUrl): string
    {
        $targetUrl = $redirectUrl;
        $targetUrl = $this->urlHelper->removeRequestParam($targetUrl, '___from_store');
        $targetUrl = $this->urlHelper->removeRequestParam($targetUrl, StoreResolverInterface::PARAM_NAME);

        return $targetUrl;
    }
}
