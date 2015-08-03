<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Model\Express\Checkout;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterfaceFactory;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Framework\Object\Copy as CopyObject;

/**
 * Class Quote
 */
class Quote
{
    /**
     * @var AddressInterfaceFactory
     */
    protected $addressFactory;

    /**
     * @var CustomerInterfaceFactory
     */
    protected $customerFactory;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var CopyObject
     */
    protected $copyObject;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @param AddressInterfaceFactory $addressFactory
     * @param CustomerInterfaceFactory $customerFactory
     * @param CustomerRepositoryInterface $customerRepository
     * @param CopyObject $copyObject
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     */
    public function __construct(
        AddressInterfaceFactory $addressFactory,
        CustomerInterfaceFactory $customerFactory,
        CustomerRepositoryInterface $customerRepository,
        CopyObject $copyObject,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
    ) {
        $this->addressFactory = $addressFactory;
        $this->customerFactory = $customerFactory;
        $this->customerRepository = $customerRepository;
        $this->copyObject = $copyObject;
        $this->dataObjectHelper = $dataObjectHelper;
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @return \Magento\Quote\Model\Quote
     */
    public function prepareQuoteForNewCustomer(\Magento\Quote\Model\Quote $quote)
    {
        $billing = $quote->getBillingAddress();
        $shipping = $quote->isVirtual() ? null : $quote->getShippingAddress();
        $billing->setDefaultBilling(true);
        if ($shipping && !$shipping->getSameAsBilling()) {
            $shipping->setDefaultShipping(true);
            $address = $shipping->exportCustomerAddress();
            $shipping->setCustomerAddressData($address);
        } elseif ($shipping) {
            $billing->setDefaultShipping(true);
        }
        $address = $shipping->exportCustomerAddress();
        $billing->setCustomerAddressData($address);
        foreach (['customer_dob', 'customer_taxvat', 'customer_gender'] as $attribute) {
            if ($quote->getData($attribute) && !$billing->getData($attribute)) {
                $billing->setData($attribute, $quote->getData($attribute));
            }
        }
        $customer = $this->customerFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $customer,
            $this->copyObject->getDataFromFieldset(
                'checkout_onepage_billing',
                'to_customer',
                $billing
            ),
            '\Magento\Customer\Api\Data\CustomerInterface'
        );
        $customer->setEmail($quote->getCustomerEmail());
        $customer->setPrefix($quote->getCustomerPrefix());
        $customer->setFirstname($quote->getCustomerFirstname());
        $customer->setMiddlename($quote->getCustomerMiddlename());
        $customer->setLastname($quote->getCustomerLastname());
        $customer->setSuffix($quote->getCustomerSuffix());
        $quote->setCustomer($customer);
        $quote->addCustomerAddress($billing->exportCustomerAddress());
        if ($shipping->hasCustomerAddress()) {
            $quote->addCustomerAddress($shipping->exportCustomerAddress());
        }
        return $quote;
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @param int|null $customerId
     * @return \Magento\Quote\Model\Quote
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function prepareRegisteredCustomerQuote(\Magento\Quote\Model\Quote $quote, $customerId)
    {
        $billing = $quote->getBillingAddress();
        $shipping = $quote->isVirtual() ? null : $quote->getShippingAddress();
        $customer = $this->customerRepository->getById($customerId);

        $isBillingAddressDefaultBilling = !$customer->getDefaultBilling();
        $isBillingAddressDefaultShipping = false;

        $isShippingAddressDefaultShipping = false;

        if ($shipping && !$customer->getDefaultShipping()) {
            $isShippingAddressDefaultShipping = true;
        } elseif (!$customer->getDefaultShipping()) {
            $isBillingAddressDefaultShipping = true;
        }

        if ($shipping && $shipping->getTelephone() && !$shipping->getSameAsBilling()
            && (!$shipping->getCustomerId() || $shipping->getSaveInAddressBook() || !$customer->getDefaultShipping())
        ) {
            $address = $shipping->exportCustomerAddress();
            $address->setIsDefaultShipping($isShippingAddressDefaultShipping);
            $quote->addCustomerAddress($address);
        }

        if ($billing && $billing->getTelephone()
            && (!$customer->getDefaultBilling() || $billing->getSaveInAddressBook())
        ) {
            $address = $billing->exportCustomerAddress();
            $address->setIsDefaultBilling($isBillingAddressDefaultBilling);
            $address->setIsDefaultShipping($isBillingAddressDefaultShipping);
            $quote->addCustomerAddress($address);
        }

        return $quote;
    }
}
