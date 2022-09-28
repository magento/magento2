<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Plugin\Model;

use Magento\Checkout\Api\GuestPaymentInformationManagementInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteIdMaskFactory;

/**
 * Class GuestPaymentInformationManagement
 */
class GuestPaymentInformationManagementPlugin
{
    /**
     * @var QuoteIdMaskFactory
     */
    private $quoteIdMaskFactory;

    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * GuestPaymentInformationManagement constructor
     *
     * @param QuoteIdMaskFactory $quoteIdMaskFactory
     * @param CartRepositoryInterface $cartRepository
     */
    public function __construct(
        QuoteIdMaskFactory $quoteIdMaskFactory,
        CartRepositoryInterface $cartRepository
    ) {
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->cartRepository = $cartRepository;
    }

    /**
     * Disable order submitting for preview
     *
     * @param GuestPaymentInformationManagementInterface $subject
     * @param string $cartId
     * @param string $email
     * @param PaymentInterface $paymentMethod
     * @param AddressInterface|null $billingAddress
     * @return void
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeSavePaymentInformationAndPlaceOrder(
        GuestPaymentInformationManagementInterface $subject,
        string $cartId,
        string $email,
        PaymentInterface $paymentMethod,
        AddressInterface $billingAddress = null
    ): void {
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
        /** @var Quote $quote */
        $quote = $this->cartRepository->getActive($quoteIdMask->getQuoteId());
        $shippingAddress = $quote->getShippingAddress();

        if (!empty($billingAddress)) {
            $sameAsBillingFlag = $this->checkIfShippingAddressMatchesWithBillingAddress($quote, $billingAddress);
        } else {
            $sameAsBillingFlag = 0;
        }

        if ($sameAsBillingFlag) {
            $shippingAddress->setSameAsBilling(1);
        }
    }

    /**
     * Returns true if shipping address is same as billing address
     *
     * @param Quote $quote
     * @param AddressInterface $billingAddress
     * @return bool
     */
    private function checkIfShippingAddressMatchesWithBillingAddress(Quote $quote, AddressInterface $billingAddress): bool
    {
        $quoteShippingAddressData = $quote->getShippingAddress()->getData();
        $billingData = $this->convertAddressValueToFlatArray($billingAddress->getData());
        $billingKeys = array_flip(array_keys($billingData));
        $shippingData = array_intersect_key($quoteShippingAddressData, $billingKeys);
        $removeKeys = ['region_code', 'save_in_address_book'];
        $billingData = array_diff_key($billingData, array_flip($removeKeys));
        $difference = array_diff($billingData, $shippingData);
        return empty($difference);
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
