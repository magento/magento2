<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Store\Model\StoreSwitcher;

use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Api\StoreResolverInterface;
use Magento\Store\Model\StoreSwitcherInterface;
use Magento\Framework\Url\Helper\Data as UrlHelper;

/**
 * Remove SID, from_store, store from target url.
 */
class CleanTargetUrl implements StoreSwitcherInterface
{
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
     * @param UrlHelper $urlHelper
     * @param \Magento\Framework\Session\Generic $session
     * @param \Magento\Framework\Session\SidResolverInterface $sidResolver
     */
    public function __construct(
        UrlHelper $urlHelper,
        \Magento\Framework\Session\Generic $session,
        \Magento\Framework\Session\SidResolverInterface $sidResolver
    ) {
        $this->urlHelper = $urlHelper;
        $this->session = $session;
        $this->sidResolver = $sidResolver;
    }

    /**
     * @param StoreInterface $fromStore store where we came from
     * @param StoreInterface $targetStore store where to go to
     * @param string $redirectUrl original url requested for redirect after switching
     * @return string redirect url
     */
    public function switch(StoreInterface $fromStore, StoreInterface $targetStore, string $redirectUrl): string
    {
        $targetUrl = $redirectUrl;
        $sidName = $this->sidResolver->getSessionIdQueryParam($this->session);
        $targetUrl = $this->urlHelper->removeRequestParam($targetUrl, $sidName);
        $targetUrl = $this->urlHelper->removeRequestParam($targetUrl, '___from_store');
        $targetUrl = $this->urlHelper->removeRequestParam($targetUrl, StoreResolverInterface::PARAM_NAME);

        return $targetUrl;
    }
}
