<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Plugin\Model;

use Magento\Checkout\Api\PaymentInformationManagementInterface;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Quote\Model\Quote;

/**
 * Class PaymentInformationManagement
 */
class PaymentInformationManagementPlugin
{
    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var AddressRepositoryInterface
     */
    private $addressRepository;

    /**
     * PaymentInformationManagement constructor
     *
     * @param CartRepositoryInterface $quoteRepository
     * @param AddressRepositoryInterface $addressRepository
     */
    public function __construct(
        CartRepositoryInterface $quoteRepository,
        AddressRepositoryInterface $addressRepository
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->addressRepository = $addressRepository;
    }

    /**
     * Disable order submitting for preview
     *
     * @param PaymentInformationManagementInterface $subject
     * @param int $cartId
     * @param PaymentInterface $paymentMethod
     * @param AddressInterface|null $billingAddress
     * @return void
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function beforeSavePaymentInformationAndPlaceOrder(
        PaymentInformationManagementInterface $subject,
        int $cartId,
        PaymentInterface $paymentMethod,
        AddressInterface $billingAddress = null
    ): void {
        /** @var Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);
        $shippingAddress = $quote->getShippingAddress();
        $quoteShippingAddressData = $shippingAddress->getData();
        $quoteSameAsBilling = (int) $shippingAddress->getSameAsBilling();
        $customer = $quote->getCustomer();
        $customerId = $customer->getId();
        $hasDefaultBilling = $customer->getDefaultBilling();
        $hasDefaultShipping = $customer->getDefaultShipping();

        if ($quoteSameAsBilling === 1) {
            $sameAsBillingFlag = 1;
        } elseif (!empty($quoteShippingAddressData) && !empty($billingAddress)) {
            $sameAsBillingFlag = $quote->getCustomerId() &&
                $this->checkIfShippingNullOrNotSameAsBillingAddress($shippingAddress, $billingAddress);
        } else {
            $sameAsBillingFlag = 0;
        }

        if ($sameAsBillingFlag) {
            $shippingAddress->setSameAsBilling(1);
            if ($customerId && !$hasDefaultBilling && !$hasDefaultShipping) {
                $this->processCustomerShippingAddress($quote);
            } elseif ($shippingAddress->getSaveInAddressBook() && $shippingAddress->getQuoteId()) {
                $shippingAddressData = $shippingAddress->exportCustomerAddress();
                $shippingAddressData->setCustomerId($quote->getCustomerId());
                $this->addressRepository->save($shippingAddressData);
                $quote->addCustomerAddress($shippingAddressData);
                $shippingAddress->setCustomerAddressData($shippingAddressData);
                $shippingAddress->setCustomerAddressId($shippingAddressData->getId());
            }
        }
    }

    /**
     * Returns true if shipping address is same as billing or it is undefined
     *
     * @param AddressInterface $shippingAddress
     * @param AddressInterface $billingAddress
     * @return bool
     */
    private function checkIfShippingNullOrNotSameAsBillingAddress(
        AddressInterface $shippingAddress,
        AddressInterface $billingAddress
    ): bool {
        if ($shippingAddress->getCustomerAddressId() !== null &&
            $billingAddress->getCustomerAddressId() !== null
        ) {
            $sameAsBillingFlag = ((int)$shippingAddress->getCustomerAddressId() ===
                (int)$billingAddress->getCustomerAddressId());
        } else {
            $quoteShippingAddressData = $shippingAddress->getData();
            $billingAddressData = $billingAddress->getData();
            if (!empty($quoteShippingAddressData) && !empty($billingAddressData)) {
                $billingData = $this->convertAddressValueToFlatArray($billingAddressData);
                $billingKeys = array_flip(array_keys($billingData));
                $shippingData = array_intersect_key($quoteShippingAddressData, $billingKeys);
                $removeKeys = ['region_code', 'save_in_address_book'];
                $billingData = array_diff_key($billingData, array_flip($removeKeys));
                $difference = array_diff($billingData, $shippingData);
                $sameAsBillingFlag = empty($difference);
            } else {
                $sameAsBillingFlag = false;
            }
        }

        return $sameAsBillingFlag;
    }

    /**
     * Convert $address value to flat array
     *
     * @param array $address
     * @return array
     */
    private function convertAddressValueToFlatArray(array $address): array
    {
        array_walk(
            $address,
            function (&$value) {
                if (is_array($value) && isset($value['value'])) {
                    if (!is_array($value['value'])) {
                        $value = (string)$value['value'];
                    } elseif (isset($value['value'][0]['file'])) {
                        $value = $value['value'][0]['file'];
                    }
                }
            }
        );
        return $address;
    }

    /**
     * Process customer shipping address
     *
     * @param Quote $quote
     * @return void
     * @throws LocalizedException
     */
    private function processCustomerShippingAddress(Quote $quote): void
    {
        $shippingAddress = $quote->getShippingAddress();
        $billingAddress = $quote->getBillingAddress();

        $customer = $quote->getCustomer();
        $hasDefaultBilling = $customer->getDefaultBilling();
        $hasDefaultShipping = $customer->getDefaultShipping();

        if ($shippingAddress->getQuoteId()) {
            $shippingAddressData = $shippingAddress->exportCustomerAddress();
        }
        if (isset($shippingAddressData)) {
            if (!$hasDefaultShipping) {
                //Make provided address as default shipping address
                $shippingAddressData->setIsDefaultShipping(true);
                if (!$hasDefaultBilling && !$billingAddress->getSaveInAddressBook()) {
                    $shippingAddressData->setIsDefaultBilling(true);
                }
            }
            //save here new customer address
            $shippingAddressData->setCustomerId($quote->getCustomerId());
            $this->addressRepository->save($shippingAddressData);
            $quote->addCustomerAddress($shippingAddressData);
            $shippingAddress->setCustomerAddressData($shippingAddressData);
            $shippingAddress->setCustomerAddressId($shippingAddressData->getId());
        }
    }
}
