<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Multishipping\Model\Cart\Controller;

use Magento\Checkout\Controller\Cart;
use Magento\Checkout\Model\Session;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Multishipping\Model\Checkout\Type\Multishipping\State;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;

/**
 * Cleans shipping addresses and item assignments after MultiShipping flow
 */
class CartPlugin
{
    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @var AddressRepositoryInterface
     */
    private $addressRepository;

    /**
     * @param CartRepositoryInterface $cartRepository
     * @param Session $checkoutSession
     * @param AddressRepositoryInterface $addressRepository
     */
    public function __construct(
        CartRepositoryInterface $cartRepository,
        Session $checkoutSession,
        AddressRepositoryInterface $addressRepository
    ) {
        $this->cartRepository = $cartRepository;
        $this->checkoutSession = $checkoutSession;
        $this->addressRepository = $addressRepository;
    }

    /**
     * Cleans shipping addresses and item assignments after MultiShipping flow
     *
     * @param Cart $subject
     * @param RequestInterface $request
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @throws LocalizedException
     */
    public function beforeDispatch(Cart $subject, RequestInterface $request)
    {
        /** @var Quote $quote */
        $quote = $this->checkoutSession->getQuote();
        if ($quote->isMultipleShippingAddresses() && $this->isCheckoutComplete()) {
            foreach ($quote->getAllShippingAddresses() as $address) {
                $quote->removeAddress($address->getId());
            }

            $shippingAddress = $quote->getShippingAddress();
            $defaultShipping = $quote->getCustomer()->getDefaultShipping();
            if ($defaultShipping) {
                $defaultCustomerAddress = $this->addressRepository->getById($defaultShipping);
                $shippingAddress->importCustomerAddressData($defaultCustomerAddress);
            }
            $this->cartRepository->save($quote);
        }
    }

    /**
     * Checks whether the checkout flow is complete
     *
     * @return bool
     */
    private function isCheckoutComplete() : bool
    {
        return (bool) ($this->checkoutSession->getStepData(State::STEP_SHIPPING)['is_complete'] ?? true);
    }
}
