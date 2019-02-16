<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\CustomerData\Plugin;

use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\Cookie\PhpCookieManager;

/**
 * Class SessionChecker
 */
class SessionChecker
{
    /**
     * @var PhpCookieManager
     */
    private $cookieManager;

    /**
     * @var CookieMetadataFactory
     */
    private $cookieMetadataFactory;

    /**
     * @param PhpCookieManager $cookieManager
     * @param CookieMetadataFactory $cookieMetadataFactory
     */
    public function __construct(
        PhpCookieManager $cookieManager,
        CookieMetadataFactory $cookieMetadataFactory
    ) {
        $this->cookieManager = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
    }

    /**
     * Delete frontend session cookie if customer session is expired
     *
     * @param SessionManagerInterface $sessionManager
     * @return void
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Stdlib\Cookie\FailureToSendException
     */
    public function beforeStart(SessionManagerInterface $sessionManager)
    {
        if (!$this->cookieManager->getCookie($sessionManager->getName())
            && $this->cookieManager->getCookie('mage-cache-sessid')
        ) {
            $metadata = $this->cookieMetadataFactory->createCookieMetadata();
            $metadata->setPath('/');
            $this->cookieManager->deleteCookie('mage-cache-sessid', $metadata);
        }
    }
}
