<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Security\Model;

use Magento\Framework\Stdlib\Cookie\CookieReaderInterface;

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
     * @var \Magento\Framework\Stdlib\Cookie\PhpCookieManager
     */
    private $phpCookieManager;

    /**
     * @var \Magento\Backend\Helper\Data
     */
    private $backendData;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\PublicCookieMetadataFactory
     */
    private $cookieMetadataFactory;

    /**
     * @var CookieReaderInterface
     */
    private $cookieReader;

    /**
     * @param \Magento\Framework\Stdlib\Cookie\PhpCookieManager $phpCookieManager
     * @param \Magento\Framework\Stdlib\Cookie\PublicCookieMetadataFactory $cookieMetadataFactory
     * @param CookieReaderInterface $cookieReader
     * @param \Magento\Backend\Helper\Data $backendData
     */
    public function __construct(
        \Magento\Framework\Stdlib\Cookie\PhpCookieManager $phpCookieManager,
        \Magento\Framework\Stdlib\Cookie\PublicCookieMetadataFactory $cookieMetadataFactory,
        CookieReaderInterface $cookieReader,
        \Magento\Backend\Helper\Data $backendData
    ) {
        $this->phpCookieManager = $phpCookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->cookieReader = $cookieReader;
        $this->backendData = $backendData;
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
     * @return \Magento\Framework\Stdlib\Cookie\PublicCookieMetadata
     */
    private function createCookieMetaData()
    {
        return $this->cookieMetadataFactory->create();
    }
}
