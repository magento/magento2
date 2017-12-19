<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\PageCache\Model\Cookie\Prolongation;

use \Magento\Framework\Stdlib\Cookie\CookieMetadata;

/**
 * Frontend cookie prolongation model.
 */
class Frontend implements ProlongationInterface
{
    /**
     * @var \Magento\Framework\Stdlib\CookieManagerInterface
     */
    protected $_cookieManager;
    /**
     * @var \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
     */
    protected $_cookieMetadataFactory;
    /**
     * @var \Magento\Framework\Session\Config\ConfigInterface
     */
    protected $_sessionConfig;

    /**
     * Management constructor.
     *
     * @param \Magento\Framework\Stdlib\CookieManagerInterface       $cookieManager
     * @param \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory
     * @param \Magento\Framework\Session\Config\ConfigInterface      $sessionConfig
     */
    public function __construct(
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
        \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory,
        \Magento\Framework\Session\Config\ConfigInterface $sessionConfig
    ) {
        $this->_cookieManager = $cookieManager;
        $this->_cookieMetadataFactory = $cookieMetadataFactory;
        $this->_sessionConfig = $sessionConfig;
    }

    /**
     * Prolongs frontend cookie.
     *
     * @return void
     */
    public function execute()
    {
        if (!$this->_isCookieSet()) {
            return;
        }

        $this->_cookieManager->setSensitiveCookie(
            $this->_getCookieName(),
            $this->_getCookieValue(),
            $this->_getCookieMetadata()
        );
    }

    /**
     * Determines whether frontend cookie is set.
     *
     * @return bool
     */
    protected function _isCookieSet()
    {
        return $this->_getCookieValue() === null;
    }

    /**
     * Returns frontend cookie name.
     *
     * @return string
     */
    protected function _getCookieName()
    {
        return session_name();
    }

    /**
     * Returns frontend cookie value.
     *
     * @return string
     */
    protected function _getCookieValue()
    {
        return $this->_cookieManager->getCookie(
            $this->_getCookieName()
        );
    }

    /**
     * Returns cookie metadata.
     *
     * @return \Magento\Framework\Stdlib\Cookie\SensitiveCookieMetadata
     */
    protected function _getCookieMetadata()
    {
        return $this->_cookieMetadataFactory->createSensitiveCookieMetadata(
            [
                CookieMetadata::KEY_DOMAIN => $this->_sessionConfig->getCookieDomain(),
                CookieMetadata::KEY_PATH => $this->_sessionConfig->getCookiePath(),
                CookieMetadata::KEY_DURATION => $this->_sessionConfig->getCookieLifetime(),
            ]
        );
    }
}
