<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Quote\Model\GuestCart;

use Magento\Quote\Api\GuestBillingAddressManagementInterface;
use Magento\Quote\Model\BillingAddressManagement;
use Magento\Quote\Model\QuoteAddressValidator;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Magento\Quote\Model\QuoteRepository;
use Psr\Log\LoggerInterface as Logger;

/** Quote billing address write service object. */
class GuestBillingAddressManagement extends BillingAddressManagement implements GuestBillingAddressManagementInterface
{
    /**
     * @var QuoteIdMaskFactory
     */
    private $quoteIdMaskFactory;

    /**
     * Constructs a quote billing address service object.
     *
     * @param QuoteRepository $quoteRepository Quote repository.
     * @param QuoteAddressValidator $addressValidator Address validator.
     * @param Logger $logger Logger.
     * @param QuoteIdMaskFactory $quoteIdMaskFactory
     */
    public function __construct(
        QuoteRepository $quoteRepository,
        QuoteAddressValidator $addressValidator,
        Logger $logger,
        QuoteIdMaskFactory $quoteIdMaskFactory
    ) {
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        parent::__construct(
            $quoteRepository,
            $addressValidator,
            $logger
        );
    }

    /**
     * {@inheritDoc}
     */
    public function assign($cartId, \Magento\Quote\Api\Data\AddressInterface $address)
    {
        /** @var $quoteIdMask QuoteIdMask */
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');

        return parent::assign($quoteIdMask->getId(), $address);
    }

    /**
     * {@inheritDoc}
     */
    public function get($cartId)
    {
        /** @var $quoteIdMask QuoteIdMask */
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');

        return parent::get($quoteIdMask->getId());
    }
}
