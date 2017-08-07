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
 * @api
 * @since 2.1.0
 */
class SecurityCookie
{
    /**
     * Cookie name
     */
    const LOGOUT_REASON_CODE_COOKIE_NAME = 'loggedOutReasonCode';

    /**
     * @var \Magento\Framework\Stdlib\Cookie\PhpCookieManager
     * @since 2.1.0
     */
    private $phpCookieManager;

    /**
     * @var \Magento\Backend\Helper\Data
     * @since 2.1.0
     */
    private $backendData;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\PublicCookieMetadataFactory
     * @since 2.1.0
     */
    private $cookieMetadataFactory;

    /**
     * @var CookieReaderInterface
     * @since 2.1.0
     */
    private $cookieReader;

    /**
     * @param \Magento\Framework\Stdlib\Cookie\PhpCookieManager $phpCookieManager
     * @param \Magento\Framework\Stdlib\Cookie\PublicCookieMetadataFactory $cookieMetadataFactory
     * @param CookieReaderInterface $cookieReader
     * @param \Magento\Backend\Helper\Data $backendData
     * @since 2.1.0
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
     * @since 2.1.0
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
     * @since 2.1.0
     */
    public function setLogoutReasonCookie($status)
    {
        $metaData = $this->createCookieMetaData();
        $metaData->setPath('/' . $this->backendData->getAreaFrontName());

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
     * @since 2.1.0
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
     * @since 2.1.0
     */
    private function createCookieMetaData()
    {
        return $this->cookieMetadataFactory->create();
    }
}
