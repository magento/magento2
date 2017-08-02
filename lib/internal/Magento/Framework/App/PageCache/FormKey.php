<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\PageCache;

use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\Cookie\PublicCookieMetadata;
use Magento\Framework\Stdlib\CookieManagerInterface;

/**
 * Class Version
 *
 * @since 2.0.0
 */
class FormKey
{
    /**
     * Name of cookie that holds private content version
     */
    const COOKIE_NAME = 'form_key';

    /**
     * CookieManager
     *
     * @var CookieManagerInterface
     * @since 2.0.0
     */
    private $cookieManager;

    /**
     * @var CookieMetadataFactory
     * @since 2.0.0
     */
    private $cookieMetadataFactory;

    /**
     * @var SessionManagerInterface
     * @since 2.0.0
     */
    private $sessionManager;

    /**
     * @param CookieManagerInterface $cookieManager
     * @param CookieMetadataFactory $cookieMetadataFactory
     * @param SessionManagerInterface $sessionManager
     * @since 2.0.0
     */
    public function __construct(
        CookieManagerInterface $cookieManager,
        CookieMetadataFactory $cookieMetadataFactory,
        SessionManagerInterface $sessionManager
    ) {
        $this->cookieManager = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->sessionManager = $sessionManager;
    }

    /**
     * Get form key cookie
     *
     * @return string
     * @since 2.0.0
     */
    public function get()
    {
        return $this->cookieManager->getCookie(self::COOKIE_NAME);
    }

    /**
     * @param string $value
     * @param PublicCookieMetadata $metadata
     * @return void
     * @since 2.0.0
     */
    public function set($value, PublicCookieMetadata $metadata)
    {
        $this->cookieManager->setPublicCookie(
            self::COOKIE_NAME,
            $value,
            $metadata
        );
    }

    /**
     * @return void
     * @since 2.0.0
     */
    public function delete()
    {
        $this->cookieManager->deleteCookie(
            self::COOKIE_NAME,
            $this->cookieMetadataFactory
                ->createCookieMetadata()
                ->setPath($this->sessionManager->getCookiePath())
                ->setDomain($this->sessionManager->getCookieDomain())
        );
    }
}
