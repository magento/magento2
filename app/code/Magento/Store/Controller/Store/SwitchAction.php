<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Controller\Store;

class SwitchAction extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
     */
    protected $cookieMetadataFactory;

    /**
     * @var \Magento\Framework\Stdlib\CookieManagerInterface
     */
    protected $cookie;

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    /**
     * @var \Magento\Store\Api\StoreRepositoryInterface
     */
    protected $storeRepository;

    /**
     * @var \Magento\Store\Api\WebsiteRepositoryInterface
     */
    protected $websiteRepository;

    /**
     * @var \Magento\Store\Api\GroupRepositoryInterface
     */
    protected $groupRepository;

    /**
     * @param \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory
     * @param \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param \Magento\Store\Api\StoreRepositoryInterface $storeRepository
     * @param \Magento\Store\Api\WebsiteRepositoryInterface $websiteRepository
     * @param \Magento\Store\Api\GroupRepositoryInterface $groupRepository
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Store\Api\StoreRepositoryInterface $storeRepository,
        \Magento\Store\Api\WebsiteRepositoryInterface $websiteRepository,
        \Magento\Store\Api\GroupRepositoryInterface $groupRepository
    ) {
        parent::__construct($context);
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->cookie = $cookieManager;
        $this->httpContext = $httpContext;
        $this->storeRepository = $storeRepository;
        $this->websiteRepository = $websiteRepository;
        $this->groupRepository = $groupRepository;
    }

    /**
     * @return void
     */
    public function execute()
    {
        $storageStore = $storage->getStore();
        if ($storageStore->getCode() == $storeCode) {
            $store = $storage->getStore($storeCode);
            if ($store->getWebsite()->getDefaultStore()->getId() == $store->getId()) {
                $store->deleteCookie();
            } else {
                $storageStore->setCookie();
                $this->_httpContext->setValue(
                    Store::ENTITY,
                    $storageStore->getCode(),
                    \Magento\Store\Model\Store::DEFAULT_CODE
                );
            }
        }





        // $this->_cookieManager->getCookie(self::COOKIE_NAME);

        $store = $this->storeRepository->get($this->_request->getParam(\Magento\Store\Model\StoreResolver::PARAM_NAME));
        $website = $this->websiteRepository->get($store->getWebsiteId());
        $group = $this->groupRepository->get($website->getDefaultGroupId());

        if ((int)$group->getDefaultStoreId() === (int)$store->getId()) {
            $cookieMetadata = $this->cookieMetadataFactory->createPublicCookieMetadata()
                ->setPath($store->getStorePath());
            $this->cookie->deleteCookie(\Magento\Store\Model\StoreResolver::COOKIE_NAME, $cookieMetadata);
        } else {
            $cookieMetadata = $this->cookieMetadataFactory->createPublicCookieMetadata()
                ->setHttpOnly(true)
                ->setDurationOneYear()
                ->setPath($store->getStorePath());
            $this->cookie->setPublicCookie(
                \Magento\Store\Model\StoreResolver::COOKIE_NAME,
                $store->getCode(),
                $cookieMetadata
            );

            $this->httpContext->setValue(
                \Magento\Store\Model\Store::ENTITY,
                $store->getCode(),
                \Magento\Store\Model\Store::DEFAULT_CODE
            );
        }
//        $this->getResponse()->setRedirect($this->_redirect->getRedirectUrl());
    }
}
