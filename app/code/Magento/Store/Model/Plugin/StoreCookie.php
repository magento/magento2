<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Store\Model\Plugin;

use Magento\Store\Api\StoreCookieManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\Model\StoreIsInactiveException;
use Magento\Framework\Exception\NoSuchEntityException;
use \InvalidArgumentException;

/**
 * Class StoreCookie
 */
class StoreCookie
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var StoreCookieManagerInterface
     */
    protected $storeCookieManager;

    /**
     * @var StoreRepositoryInterface
     */
    protected $storeRepository;

    /**
     * @param StoreManagerInterface $storeManager
     * @param StoreCookieManagerInterface $storeCookieManager
     * @param StoreRepositoryInterface $storeRepository
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        StoreCookieManagerInterface $storeCookieManager,
        StoreRepositoryInterface $storeRepository
    ) {
        $this->storeManager = $storeManager;
        $this->storeCookieManager = $storeCookieManager;
        $this->storeRepository = $storeRepository;
    }

    /**
     * Delete cookie "store" if the store (a value in the cookie) does not exist or is inactive
     *
     * @param \Magento\Framework\App\FrontController $subject
     * @param callable $proceed
     * @param \Magento\Framework\App\RequestInterface $request
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundDispatch(
        \Magento\Framework\App\FrontController $subject,
        \Closure $proceed,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $defaultStore = $this->storeManager->getDefaultStoreView();
        $storeCodeFromCookie = $this->storeCookieManager->getStoreCodeFromCookie();
        if ($storeCodeFromCookie) {
            try {
                $this->storeRepository->getActiveStoreByCode($storeCodeFromCookie);
            } catch (StoreIsInactiveException $e) {
                $this->storeCookieManager->deleteStoreCookie($defaultStore);
            } catch (NoSuchEntityException $e) {
                $this->storeCookieManager->deleteStoreCookie($defaultStore);
            } catch (InvalidArgumentException $e) {
                $this->storeCookieManager->deleteStoreCookie($defaultStore);
            }
        }
        return $proceed($request);
    }
}
