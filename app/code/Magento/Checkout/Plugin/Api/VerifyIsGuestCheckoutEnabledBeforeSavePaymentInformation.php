<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Checkout\Plugin\Api;

use Magento\Checkout\Api\GuestPaymentInformationManagementInterface;
use Magento\Checkout\Helper\Data as CheckoutHelper;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Quote\Model\QuoteIdMask;
use Magento\Quote\Model\QuoteIdMaskFactory;

class VerifyIsGuestCheckoutEnabledBeforeSavePaymentInformation
{
    /**
     * @var CheckoutHelper
     */
    private CheckoutHelper $checkoutHelper;

    /**
     * @var QuoteIdMaskFactory
     */
    private QuoteIdMaskFactory $quoteIdMaskFactory;

    /**
     * @var CartRepositoryInterface
     */
    private CartRepositoryInterface $cartRepository;

    /**
     * @param CheckoutHelper $checkoutHelper
     * @param QuoteIdMaskFactory $quoteIdMaskFactory
     * @param CartRepositoryInterface $cartRepository
     */
    public function __construct(
        CheckoutHelper $checkoutHelper,
        QuoteIdMaskFactory $quoteIdMaskFactory,
        CartRepositoryInterface $cartRepository
    ) {
        $this->checkoutHelper = $checkoutHelper;
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->cartRepository = $cartRepository;
    }

    /**
     * Checks whether guest checkout is enabled before saving payment information
     *
     * @param GuestPaymentInformationManagementInterface $subject
     * @param string $cartId
     * @param string $email
     * @param PaymentInterface $paymentMethod
     * @param AddressInterface|null $billingAddress
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeSavePaymentInformation(
        GuestPaymentInformationManagementInterface $subject,
        $cartId,
        $email,
        PaymentInterface $paymentMethod,
        AddressInterface $billingAddress = null
    ): void {
        /** @var $quoteIdMask QuoteIdMask */
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
        $quote = $this->cartRepository->get($quoteIdMask->getQuoteId());
        if (!$this->checkoutHelper->isAllowedGuestCheckout($quote)) {
            throw new CouldNotSaveException(__('Sorry, guest checkout is not available.'));
        }
    }
}
