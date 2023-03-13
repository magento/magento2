<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Store\Model\StoreSwitcher;

use Exception;
use Magento\Framework\App\PageCache\Version as PageCacheVersion;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Store\Model\StoreSwitcherInterface;
use Magento\Store\Api\Data\StoreInterface;

/**
 * Set private content cookie to have actual local storage data on target store after store switching.
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class ManagePrivateContent implements StoreSwitcherInterface
{
    /**
     * @param CookieMetadataFactory $cookieMetadataFactory
     * @param CookieManagerInterface $cookieManager
     */
    public function __construct(
        private readonly CookieMetadataFactory $cookieMetadataFactory,
        private readonly CookieManagerInterface $cookieManager
    ) {
    }

    /**
     * Update version of private content on each store switch.
     *
     * @param StoreInterface $fromStore store where we came from
     * @param StoreInterface $targetStore store where to go to
     * @param string $redirectUrl original url requested for redirect after switching
     *
     * @return string redirect url
     * @throws CannotSwitchStoreException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function switch(StoreInterface $fromStore, StoreInterface $targetStore, string $redirectUrl): string
    {
        try {
            $publicCookieMetadata = $this->cookieMetadataFactory->createPublicCookieMetadata()
                ->setDurationOneYear()
                ->setPath('/')
                ->setSecure(false)
                ->setHttpOnly(false)
                ->setSameSite('Lax');
            $this->cookieManager->setPublicCookie(
                PageCacheVersion::COOKIE_NAME,
                \uniqid('updated-', true),
                $publicCookieMetadata
            );
        } catch (Exception $e) {
            throw new CannotSwitchStoreException($e);
        }

        return $redirectUrl;
    }
}
