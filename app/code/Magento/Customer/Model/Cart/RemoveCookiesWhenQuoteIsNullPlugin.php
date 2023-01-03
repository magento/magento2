<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model\Cart;

use Magento\Checkout\Block\Cart\Sidebar;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Stdlib\Cookie\FailureToSendException;
use Magento\Framework\Stdlib\Cookie\PhpCookieManager;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Session\SessionManagerInterface;

class RemoveCookiesWhenQuoteIsNullPlugin
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
     * @var SessionManagerInterface
     */
    private $sessionManager;

    /**
     * @param PhpCookieManager $cookieManager
     * @param CookieMetadataFactory $cookieMetadataFactory
     * @param SessionManagerInterface $sessionManager
     */
    public function __construct(
        PhpCookieManager $cookieManager,
        CookieMetadataFactory $cookieMetadataFactory,
        SessionManagerInterface $sessionManager
    ) {
        $this->cookieManager = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->sessionManager = $sessionManager;
    }

    /**
     * @param Sidebar $subject
     * @return void
     * @throws InputException
     * @throws FailureToSendException
     */
    public function beforeGetConfig(Sidebar $subject)
    {
        if ($subject->getQuote()->getItems() === null) {
            $metadata = $this->cookieMetadataFactory->createCookieMetadata();
            $metadata->setPath($this->sessionManager->getCookiePath());
            $metadata->setDomain($this->sessionManager->getCookieDomain());
            $this->cookieManager->deleteCookie('mage-cache-sessid', $metadata);
        }
    }
}
