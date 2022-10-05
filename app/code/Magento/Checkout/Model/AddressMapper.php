<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Checkout\Model;

use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteIdMaskFactory;

class AddressMapper implements AddressMapperInterface
{
    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var AddressRepositoryInterface
     */
    private $addressRepository;

    /**
     * @var QuoteIdMaskFactory
     */
    private $quoteIdMaskFactory;

    /**
     * AddressMapper constructor
     *
     * @param CartRepositoryInterface $cartRepository
     * @param AddressRepositoryInterface $addressRepository
     * @param QuoteIdMaskFactory $quoteIdMaskFactory
     */
    public function __construct(
        CartRepositoryInterface    $cartRepository,
        AddressRepositoryInterface $addressRepository,
        QuoteIdMaskFactory $quoteIdMaskFactory
    ) {
        $this->cartRepository = $cartRepository;
        $this->addressRepository = $addressRepository;
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
    }

    /**
     * @inheritDoc
     *
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function customerCheckoutAddressMapper(
        int $cartId,
        PaymentInterface $paymentMethod,
        AddressInterface $billingAddress = null
    ): void {
        /** @var Quote $quote */
        $quote = $this->cartRepository->getActive($cartId);
        $shippingAddress = $quote->getShippingAddress();
        $quoteShippingAddressData = $shippingAddress->getData();
        $quoteSameAsBilling = (int)$shippingAddress->getSameAsBilling();
        $customer = $quote->getCustomer();
        $customerId = $customer->getId();
        $hasDefaultBilling = $customer->getDefaultBilling();
        $hasDefaultShipping = $customer->getDefaultShipping();

        if ($quoteSameAsBilling === 1) {
            $sameAsBillingFlag = 1;
        } elseif (!empty($quoteShippingAddressData) && !empty($billingAddress)) {
            $sameAsBillingFlag = $quote->getCustomerId() &&
                    $this->checkIfShippingAddressMatchesWithBillingAddress($shippingAddress, $billingAddress);
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
     * @inheritDoc
     */
    public function guestCheckoutAddressMapper(
        int|string       $cartId,
        string           $email,
        PaymentInterface $paymentMethod,
        AddressInterface $billingAddress = null
    ): void {
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
        /** @var Quote $quote */
        $quote = $this->cartRepository->getActive($quoteIdMask->getQuoteId());
        $shippingAddress = $quote->getShippingAddress();

        if (!empty($billingAddress)) {
            $sameAsBillingFlag = $this->checkIfShippingAddressMatchesWithBillingAddress(
                $shippingAddress,
                $billingAddress
            );
        } else {
            $sameAsBillingFlag = 0;
        }

        if ($sameAsBillingFlag) {
            $shippingAddress->setSameAsBilling(1);
        }
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

    /**
     * Returns true if shipping address is same as billing, or it is undefined
     *
     * @param AddressInterface $shippingAddress
     * @param AddressInterface $billingAddress
     * @return bool
     */
    private function checkIfShippingAddressMatchesWithBillingAddress(
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
            static function (&$value) {
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
}
