<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Observer\Frontend\Quote;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\Cookie\PhpCookieManager;
use Magento\Checkout\Model\Session;

/**
 * Class responsible for refreshing customer data when trigger recollect flag was set to the quote
 * by some actions in admin.
 */
class RefreshCustomerDataObserver implements ObserverInterface
{
    /**
     * Checkout session
     *
     * @var \Magento\Checkout\Model\Session
     */
    private $checkoutSession;

    /**
     * @var PhpCookieManager
     */
    private $cookieManager;

    /**
     * @var CookieMetadataFactory
     */
    private $cookieMetadataFactory;

    /**
     * RefreshCustomerDataObserver constructor.
     * @param Session $checkoutSession
     * @param PhpCookieManager $cookieManager
     * @param CookieMetadataFactory $cookieMetadataFactory
     */
    public function __construct(
        Session $checkoutSession,
        PhpCookieManager $cookieManager,
        CookieMetadataFactory $cookieMetadataFactory
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->cookieManager = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
    }

    /**
     * Check and clear session data if persistent session expired
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $quote = $this->checkoutSession->getQuote();
        /* Check if original quote object loaded from the database had trigger_recollect flag */
        if ($quote->getOrigData('trigger_recollect')) {
            $metadata = $this->cookieMetadataFactory->createCookieMetadata();
            $metadata->setPath('/');
            $this->cookieManager->deleteCookie('mage-cache-sessid', $metadata);
        }
    }
}
