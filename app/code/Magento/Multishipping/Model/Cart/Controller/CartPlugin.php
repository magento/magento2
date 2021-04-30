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
use Magento\Multishipping\Model\DisableMultishipping;
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
     * @var DisableMultishipping
     */
    private $disableMultishipping;

    /**
     * @param CartRepositoryInterface $cartRepository
     * @param Session $checkoutSession
     * @param AddressRepositoryInterface $addressRepository
     * @param DisableMultishipping $disableMultishipping
     */
    public function __construct(
        CartRepositoryInterface $cartRepository,
        Session $checkoutSession,
        AddressRepositoryInterface $addressRepository,
        DisableMultishipping $disableMultishipping
    ) {
        $this->cartRepository = $cartRepository;
        $this->checkoutSession = $checkoutSession;
        $this->addressRepository = $addressRepository;
        $this->disableMultishipping = $disableMultishipping;
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
        if ($quote->isMultipleShippingAddresses() || $this->isDisableMultishippingRequired($request, $quote)) {
            $this->disableMultishipping->execute($quote);
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
        } elseif ($this->disableMultishipping->execute($quote) && $this->isVirtualItemInQuote($quote)) {
            $quote->setTotalsCollectedFlag(false);
            $this->cartRepository->save($quote);
        }
    }

    /**
     * Checks whether quote has virtual items
     *
     * @param Quote $quote
     * @return bool
     */
    private function isVirtualItemInQuote(Quote $quote): bool
    {
        $items = $quote->getItems();
        if (!empty($items)) {
            foreach ($items as $item) {
                if ($item->getIsVirtual()) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Check if we have to disable multishipping mode depends on the request action name
     *
     * We should not disable multishipping mode if we are adding a new product item to the existing quote
     *
     * @param RequestInterface $request
     * @param Quote $quote
     * @return bool
     */
    private function isDisableMultishippingRequired(RequestInterface $request, Quote $quote): bool
    {
        return $request->getActionName() !== "add" && $quote->getIsMultiShipping();
    }
}
