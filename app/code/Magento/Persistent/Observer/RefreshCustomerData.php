<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Persistent\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\Cookie\PhpCookieManager;

/**
 * Class \Magento\Persistent\Observer\RefreshCustomerData
 *
 * @since 2.2.0
 */
class RefreshCustomerData implements ObserverInterface
{
    /**
     * @var PhpCookieManager
     * @since 2.2.0
     */
    private $cookieManager;

    /**
     * @var CookieMetadataFactory
     * @since 2.2.0
     */
    private $cookieMetadataFactory;

    /**
     * RefreshCustomerData constructor.
     * @param PhpCookieManager $cookieManager
     * @param CookieMetadataFactory $cookieMetadataFactory
     * @since 2.2.0
     */
    public function __construct(
        PhpCookieManager $cookieManager,
        CookieMetadataFactory $cookieMetadataFactory
    ) {
        $this->cookieManager = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
    }

    /**
     * Check and clear session data if persistent session expired
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.2.0
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->cookieManager->getCookie('mage-cache-sessid')) {
            $metadata = $this->cookieMetadataFactory->createCookieMetadata();
            $metadata->setPath('/');
            $this->cookieManager->deleteCookie('mage-cache-sessid', $metadata);
        }
    }
}
