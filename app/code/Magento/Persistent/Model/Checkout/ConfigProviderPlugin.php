<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Persistent\Model\Checkout;

use Magento\Persistent\Helper\Session as PersistentSession;
use Magento\Persistent\Helper\Data as PersistentHelper;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Magento\Customer\Model\Session as CustomerSession;

class ConfigProviderPlugin
{
    /**
     * @var PersistentSession
     */
    private $persistentSession;

    /**
     * @var PersistentHelper
     */
    private $persistentHelper;

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var QuoteIdMaskFactory
     */
    private $quoteIdMaskFactory;

    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * @param PersistentHelper $persistentHelper
     * @param PersistentSession $persistentSession
     * @param CheckoutSession $checkoutSession
     * @param QuoteIdMaskFactory $quoteIdMaskFactory
     * @param CustomerSession $customerSession
     */
    public function __construct(
        PersistentHelper $persistentHelper,
        PersistentSession $persistentSession,
        CheckoutSession $checkoutSession,
        QuoteIdMaskFactory $quoteIdMaskFactory,
        CustomerSession $customerSession
    ) {
        $this->persistentHelper = $persistentHelper;
        $this->persistentSession = $persistentSession;
        $this->checkoutSession = $checkoutSession;
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->customerSession = $customerSession;
    }

    /**
     * @param \Magento\Checkout\Model\DefaultConfigProvider $subject
     * @param array $result
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetConfig(\Magento\Checkout\Model\DefaultConfigProvider $subject, array $result)
    {
        if ($this->persistentHelper->isEnabled()
                && $this->persistentSession->isPersistent()
                && !$this->customerSession->isLoggedIn()
        ) {
            /** @var $quoteIdMask \Magento\Quote\Model\QuoteIdMask */
            $quoteIdMask = $this->quoteIdMaskFactory->create();
            $result['quoteData']['entity_id'] = $quoteIdMask->load(
                $this->checkoutSession->getQuote()->getId(),
                'quote_id'
            )->getMaskedId();
        }
        return $result;
    }
}
