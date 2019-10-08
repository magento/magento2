<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model;

use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Customer\Api\RedirectCookieManagerInterface;

/**
 * Customer redirect cookie manager
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 *
 */
class RedirectCookieManager implements RedirectCookieManagerInterface
{
    /**
     * Cookie name
     */
    const COOKIE_NAME = 'login_redirect';

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
    public function getRedirectCookie()
    {
        return $this->cookieManager->getCookie(self::COOKIE_NAME, null);
    }

    /**
     * {@inheritdoc}
     */
    public function setRedirectCookie($route, StoreInterface $store)
    {
        $cookieMetadata = $this->cookieMetadataFactory->createPublicCookieMetadata()
            ->setHttpOnly(true)
            ->setDuration(3600)
            ->setPath($store->getStorePath());
        $this->cookieManager->setPublicCookie(self::COOKIE_NAME, $route, $cookieMetadata);
    }

    /**
     * {@inheritdoc}
     */
    public function clearRedirectCookie(StoreInterface $store)
    {
        $cookieMetadata = $this->cookieMetadataFactory->createPublicCookieMetadata()
            ->setPath($store->getStorePath());
        $this->cookieManager->deleteCookie(self::COOKIE_NAME, $cookieMetadata);
    }
}
