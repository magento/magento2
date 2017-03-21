<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Model;

use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Api\StoreCookieManagerInterface;

class StoreCookieManager implements StoreCookieManagerInterface
{
    /**
     * Cookie name
     */
    const COOKIE_NAME = 'store';

    /**
     * @var CookieMetadataFactory
     */
    protected $cookieMetadataFactory;

    /**
     * @var CookieManagerInterface
     */
    protected $cookieManager;

    /**
     * @param CookieMetadataFactory $cookieMetadataFactory
     * @param CookieManagerInterface $cookieManager
     */
    public function __construct(
        CookieMetadataFactory $cookieMetadataFactory,
        CookieManagerInterface $cookieManager
    ) {
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->cookieManager = $cookieManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getStoreCodeFromCookie()
    {
        return $this->cookieManager->getCookie(self::COOKIE_NAME);
    }

    /**
     * {@inheritdoc}
     */
    public function setStoreCookie(StoreInterface $store)
    {
        $cookieMetadata = $this->cookieMetadataFactory->createPublicCookieMetadata()
            ->setHttpOnly(true)
            ->setDurationOneYear()
            ->setPath($store->getStorePath());

        $this->cookieManager->setPublicCookie(self::COOKIE_NAME, $store->getCode(), $cookieMetadata);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteStoreCookie(StoreInterface $store)
    {
        $cookieMetadata = $this->cookieMetadataFactory->createPublicCookieMetadata()
            ->setPath($store->getStorePath());

        $this->cookieManager->deleteCookie(self::COOKIE_NAME, $cookieMetadata);
    }
}
