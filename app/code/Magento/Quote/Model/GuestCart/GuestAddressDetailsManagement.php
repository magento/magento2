<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model\GuestCart;

use Magento\Quote\Model\QuoteIdMaskFactory;

class GuestAddressDetailsManagement implements \Magento\Quote\Api\GuestAddressDetailsManagementInterface
{
    /**
     * @var \Magento\Quote\Api\AddressDetailsManagementInterface
     */
    protected $addressDetailsManagement;

    /**
     * @var QuoteIdMaskFactory
     */
    protected $quoteIdMaskFactory;

    /**
     * @param \Magento\Quote\Api\AddressDetailsManagementInterface $addressDetailsManagement
     * @param QuoteIdMaskFactory $quoteIdMaskFactory
     */
    public function __construct(
        \Magento\Quote\Api\AddressDetailsManagementInterface $addressDetailsManagement,
        QuoteIdMaskFactory $quoteIdMaskFactory
    ) {
        $this->addressDetailsManagement = $addressDetailsManagement;
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
    }

    /**
     * @{inheritdoc}
     */
    public function saveAddresses(
        $cartId,
        \Magento\Quote\Api\Data\AddressInterface $billingAddress,
        \Magento\Quote\Api\Data\AddressInterface $shippingAddress = null,
        \Magento\Quote\Api\Data\AddressAdditionalDataInterface $additionalData = null
    ) {
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
        return $this->addressDetailsManagement->saveAddresses(
            $quoteIdMask->getQuoteId(),
            $billingAddress,
            $shippingAddress,
            $additionalData
        );
    }
}
