<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Security\Model;

use Magento\Backend\Helper\Data as BackendHelper;
use Magento\Framework\Stdlib\Cookie\CookieReaderInterface;
use Magento\Framework\Stdlib\Cookie\PhpCookieManager;
use Magento\Framework\Stdlib\Cookie\PublicCookieMetadata;
use Magento\Framework\Stdlib\Cookie\PublicCookieMetadataFactory;

/**
 * Manager for a cookie with logout reason
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 * @api
 * @since 100.1.0
 */
class SecurityCookie
{
    /**
     * Cookie name
     */
    const LOGOUT_REASON_CODE_COOKIE_NAME = 'loggedOutReasonCode';

    /**
     * @param PhpCookieManager $phpCookieManager
     * @param PublicCookieMetadataFactory $cookieMetadataFactory
     * @param CookieReaderInterface $cookieReader
     * @param BackendHelper $backendData
     */
    public function __construct(
        private readonly PhpCookieManager $phpCookieManager,
        private readonly PublicCookieMetadataFactory $cookieMetadataFactory,
        private readonly CookieReaderInterface $cookieReader,
        private readonly BackendHelper $backendData
    ) {
    }

    /**
     * Get cookie with logout reason code
     *
     * @return string|int
     * @since 100.1.0
     */
    public function getLogoutReasonCookie()
    {
        return (int) $this->cookieReader->getCookie(self::LOGOUT_REASON_CODE_COOKIE_NAME, -1);
    }

    /**
     * Set logout reason cookie
     *
     * @param int $status
     * @return $this
     * @since 100.1.0
     */
    public function setLogoutReasonCookie($status)
    {
        $metaData = $this->createCookieMetaData();
        $metaData->setPath('/' . $this->backendData->getAreaFrontName());
        $metaData->setSameSite('Strict');

        $this->phpCookieManager->setPublicCookie(
            self::LOGOUT_REASON_CODE_COOKIE_NAME,
            (int) $status,
            $metaData
        );

        return $this;
    }

    /**
     * Delete cookie with reason of logout
     *
     * @return $this
     * @since 100.1.0
     */
    public function deleteLogoutReasonCookie()
    {
        $metaData = $this->createCookieMetaData();
        $metaData->setPath('/' . $this->backendData->getAreaFrontName())->setDuration(-1);

        $this->phpCookieManager->setPublicCookie(
            self::LOGOUT_REASON_CODE_COOKIE_NAME,
            '',
            $metaData
        );

        return $this;
    }

    /**
     * Create Cookie Metadata instance
     *
     * @return PublicCookieMetadata
     */
    private function createCookieMetaData()
    {
        return $this->cookieMetadataFactory->create();
    }
}
