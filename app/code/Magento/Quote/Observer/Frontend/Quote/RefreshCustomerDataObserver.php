<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Observer\Frontend\Quote;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\Cookie\PhpCookieManager;
use Magento\Checkout\Model\Session;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\ResourceModel\Quote as QuoteResource;

/**
 * Class responsible for refreshing customer data when trigger recollect flag was set to the quote
 * by some actions in admin.
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
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
     * @var QuoteResource
     */
    private $quoteResource;

    /**
     * RefreshCustomerDataObserver constructor.
     * @param Session $checkoutSession
     * @param PhpCookieManager $cookieManager
     * @param CookieMetadataFactory $cookieMetadataFactory
     * @param QuoteResource $quoteResource
     */
    public function __construct(
        Session $checkoutSession,
        PhpCookieManager $cookieManager,
        CookieMetadataFactory $cookieMetadataFactory,
        QuoteResource $quoteResource
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->cookieManager = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->quoteResource = $quoteResource;
    }

    /**
     * Check and clear session data if persistent session expired
     *
     * @param Observer $observer
     * @return void
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(Observer $observer)
    {
        $quote = $this->checkoutSession->getQuote();
        /* Check if quote has trigger_private_content_update flag and flush customer private data if needed. */
        if ($quote->getData('trigger_private_content_update')) {
            $metadata = $this->cookieMetadataFactory->createCookieMetadata();
            $metadata->setPath('/');
            $this->cookieManager->deleteCookie('mage-cache-sessid', $metadata);
            $quote->setData('trigger_private_content_update', 0);
            $this->quoteResource->save($quote);
        }
    }
}
