<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Store\Model\Plugin;

use Magento\Framework\App\FrontController;
use Magento\Framework\App\RequestInterface;
use Magento\Store\Api\StoreCookieManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\Model\StoreIsInactiveException;
use Magento\Framework\Exception\NoSuchEntityException;
use InvalidArgumentException;

/**
 * Class StoreCookie
 */
class StoreCookie
{
    /**
     * @param StoreManagerInterface $storeManager
     * @param StoreCookieManagerInterface $storeCookieManager
     * @param StoreRepositoryInterface $storeRepository
     */
    public function __construct(
        protected readonly StoreManagerInterface $storeManager,
        protected readonly StoreCookieManagerInterface $storeCookieManager,
        protected readonly StoreRepositoryInterface $storeRepository
    ) {
    }

    /**
     * Delete cookie "store" if the store (a value in the cookie) does not exist or is inactive
     *
     * @param FrontController $subject
     * @param RequestInterface $request
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeDispatch(
        FrontController $subject,
        RequestInterface $request
    ) {
        $storeCodeFromCookie = $this->storeCookieManager->getStoreCodeFromCookie();
        if ($storeCodeFromCookie) {
            try {
                $this->storeRepository->getActiveStoreByCode($storeCodeFromCookie);
            } catch (StoreIsInactiveException $e) {
                $this->storeCookieManager->deleteStoreCookie($this->storeManager->getDefaultStoreView());
            } catch (NoSuchEntityException $e) {
                $this->storeCookieManager->deleteStoreCookie($this->storeManager->getDefaultStoreView());
            } catch (InvalidArgumentException $e) {
                $this->storeCookieManager->deleteStoreCookie($this->storeManager->getDefaultStoreView());
            }
        }
    }
}
