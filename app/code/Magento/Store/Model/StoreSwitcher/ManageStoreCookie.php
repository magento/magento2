<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Store\Model\StoreSwitcher;

use Magento\Store\Model\StoreSwitcherInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Store\Api\StoreCookieManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\Store;

/**
 * Manage store cookie depending on what store view customer is.
 */
class ManageStoreCookie implements StoreSwitcherInterface
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
     * @param StoreCookieManagerInterface $storeCookieManager
     * @param HttpContext $httpContext
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        StoreCookieManagerInterface $storeCookieManager,
        HttpContext $httpContext,
        StoreManagerInterface $storeManager
    ) {
        $this->storeCookieManager = $storeCookieManager;
        $this->httpContext = $httpContext;
        $this->storeManager = $storeManager;
    }

    /**
     * @param StoreInterface $fromStore store where we came from
     * @param StoreInterface $targetStore store where to go to
     * @param string $redirectUrl original url requested for redirect after switching
     * @return string redirect url
     */
    public function switch(StoreInterface $fromStore, StoreInterface $targetStore, string $redirectUrl): string
    {
        $defaultStoreView = $this->storeManager->getDefaultStoreView();
        if ($defaultStoreView !== null) {
            if ($defaultStoreView->getId() === $targetStore->getId()) {
                $this->storeCookieManager->deleteStoreCookie($targetStore);
            } else {
                $this->httpContext->setValue(Store::ENTITY, $targetStore->getCode(), $defaultStoreView->getCode());
                $this->storeCookieManager->setStoreCookie($targetStore);
            }
        }

        return $redirectUrl;
    }
}
