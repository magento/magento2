<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model;

/** Quote shipping/billing address validator service. */
class QuoteAddressValidator
{
    /**
     * Address factory.
     *
     * @var \Magento\Quote\Model\Quote\AddressFactory
     */
    protected $quoteAddressFactory;

    /**
     * Customer factory.
     *
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;

    /**
     * Constructs a quote shipping address validator service object.
     *
     * @param \Magento\Quote\Model\Quote\AddressFactory $quoteAddressFactory Address factory.
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory Customer factory.
     */
    public function __construct(
        \Magento\Quote\Model\Quote\AddressFactory $quoteAddressFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory
    ) {
        $this->quoteAddressFactory = $quoteAddressFactory;
        $this->customerFactory = $customerFactory;
    }

    /**
     * Validates the fields in a specified address data object.
     *
     * @param \Magento\Quote\Api\Data\AddressInterface $addressData The address data object.
     * @return bool
     * @throws \Magento\Framework\Exception\InputException The specified address belongs to another customer.
     * @throws \Magento\Framework\Exception\NoSuchEntityException The specified customer ID or address ID is not valid.
     */
    public function validate(\Magento\Quote\Api\Data\AddressInterface $addressData)
    {
        //validate customer id
        if ($addressData->getCustomerId()) {
            $customer = $this->customerFactory->create();
            $customer->load($addressData->getCustomerId());
            if (!$customer->getId()) {
                throw new \Magento\Framework\Exception\NoSuchEntityException(
                    __('Invalid customer id %1', $addressData->getCustomerId())
                );
            }
        }

        // validate address id
        if ($addressData->getId()) {
            $address = $this->quoteAddressFactory->create();
            $address->load($addressData->getId());
            if (!$address->getId()) {
                throw new \Magento\Framework\Exception\NoSuchEntityException(
                    __('Invalid address id %1', $addressData->getId())
                );
            }

            // check correspondence between customer id and address id
            if ($addressData->getCustomerId()) {
                if ($address->getCustomerId() != $addressData->getCustomerId()) {
                    throw new \Magento\Framework\Exception\InputException(
                        __('Address with id %1 belongs to another customer', $addressData->getId())
                    );
                }
            }
        }
        return true;
    }
}
