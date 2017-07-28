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

/**
 * Class \Magento\Persistent\Model\Checkout\ConfigProviderPlugin
 *
 * @since 2.0.0
 */
class ConfigProviderPlugin
{
    /**
     * @var PersistentSession
     * @since 2.0.0
     */
    private $persistentSession;

    /**
     * @var PersistentHelper
     * @since 2.0.0
     */
    private $persistentHelper;

    /**
     * @var CheckoutSession
     * @since 2.0.0
     */
    private $checkoutSession;

    /**
     * @var QuoteIdMaskFactory
     * @since 2.0.0
     */
    private $quoteIdMaskFactory;

    /**
     * @var CustomerSession
     * @since 2.0.0
     */
    private $customerSession;

    /**
     * @param PersistentHelper $persistentHelper
     * @param PersistentSession $persistentSession
     * @param CheckoutSession $checkoutSession
     * @param QuoteIdMaskFactory $quoteIdMaskFactory
     * @param CustomerSession $customerSession
     * @since 2.0.0
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
     * @since 2.0.0
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
