<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Multishipping\Model\Cart;

use Magento\Checkout\Controller\Cart;
use Magento\Checkout\Controller\Sidebar\UpdateItemQty;
use Magento\Checkout\Model\Session;
use Magento\Checkout\Model\Cart as CartModel;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Multishipping\Model\DisableMultishipping;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;

/**
 * Cleans shipping addresses and item assignments after MultiShipping flow
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class MultishippingClearItemAddress
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
     * @var CartModel
     */
    private $cartmodel;

    /**
     * @param CartRepositoryInterface $cartRepository
     * @param Session $checkoutSession
     * @param AddressRepositoryInterface $addressRepository
     * @param DisableMultishipping $disableMultishipping
     * @param CartModel $cartmodel
     */
    public function __construct(
        CartRepositoryInterface $cartRepository,
        Session $checkoutSession,
        AddressRepositoryInterface $addressRepository,
        DisableMultishipping $disableMultishipping,
        CartModel $cartmodel
    ) {
        $this->cartRepository = $cartRepository;
        $this->checkoutSession = $checkoutSession;
        $this->addressRepository = $addressRepository;
        $this->disableMultishipping = $disableMultishipping;
        $this->cartmodel = $cartmodel;
    }

    /**
     * Cleans shipping addresses and item assignments after MultiShipping flow
     *
     * @param Cart|UpdateItemQty $subject
     * @param RequestInterface $request
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function clearAddressItem($subject, $request)
    {
        /** @var Quote $quote */
        $quote = $this->checkoutSession->getQuote();
        $isMultipleShippingAddressesPresent = $quote->isMultipleShippingAddresses();
        if ($isMultipleShippingAddressesPresent || $this->isDisableMultishippingRequired($request, $quote)) {
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
            if ($isMultipleShippingAddressesPresent) {
                $this->checkoutSession->setMultiShippingAddressesFlag(true);
            }
            $this->cartRepository->save($quote);
            if ($subject instanceof UpdateItemQty) {
                $quote = $this->cartRepository->get($quote->getId());
                $this->cartmodel->setQuote($quote);
            }
            $this->checkoutSession->clearQuote();
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
