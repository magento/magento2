<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Security\Helper;

use Magento\Framework\Stdlib\Cookie\CookieReaderInterface;

/**
 * Security cookie helper
 */
class SecurityCookie extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Cookie name
     */
    const LOGOUT_REASON_CODE_COOKIE_NAME = 'loggedOutReasonCode';

    /**
     * @var \Magento\Framework\Stdlib\Cookie\PhpCookieManager
     */
    protected $phpCookieManager;

    /**
     * @var \Magento\Backend\Helper\Data
     */
    protected $backendData;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\PublicCookieMetadataFactory
     */
    protected $cookieMetadataFactory;

    /**
     * @var CookieReaderInterface
     */
    protected $cookieReader;

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
    protected function createCookieMetaData()
    {
        return $this->cookieMetadataFactory->create();
    }
}
