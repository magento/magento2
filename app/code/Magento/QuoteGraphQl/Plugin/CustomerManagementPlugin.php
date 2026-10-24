<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Plugin;

use Exception;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\CustomerManagement as Subject;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Quote\Model\Quote;

class CustomerManagementPlugin
{
    /**
     * CustomerManagementPlugin Constructor
     *
     * @param AddressRepositoryInterface $addressRepository
     */
    public function __construct(
        private readonly AddressRepositoryInterface $addressRepository,
    ) {
    }

    /**
     * Save shipping address in address book if same as billing
     *
     * @param Subject $subject
     * @param Quote $quote
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     * @throws LocalizedException
     */
    public function beforePopulateCustomerInfo(
        Subject $subject,
        Quote $quote
    ): array {
        try {
            $shippingAddress = $quote->getShippingAddress();

            if ($shippingAddress->getSaveInAddressBook()
                && $shippingAddress->getQuoteId()
                && $shippingAddress->getSameAsBilling()
            ) {
                $shippingAddressData = $shippingAddress->exportCustomerAddress();
                $shippingAddressData->setCustomerId($quote->getCustomerId());
                $this->addressRepository->save($shippingAddressData);
                $quote->addCustomerAddress($shippingAddressData);
                $shippingAddress->setCustomerAddressData($shippingAddressData);
                $shippingAddress->setCustomerAddressId($shippingAddressData->getId());
            }
        } catch (Exception $e) {
            throw new LocalizedException(__($e->getMessage()));
        }

        return [$quote];
    }
}
