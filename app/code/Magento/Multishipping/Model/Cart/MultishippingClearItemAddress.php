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
<<<<<<< HEAD
use Magento\Checkout\Model\Cart as CartModel;
=======
>>>>>>> 50b054ffc2e (Resolved conflicts)
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Multishipping\Model\DisableMultishipping;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;

/**
 * Cleans shipping addresses and item assignments after MultiShipping flow
<<<<<<< HEAD
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
=======
>>>>>>> 50b054ffc2e (Resolved conflicts)
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
<<<<<<< HEAD
     * @var CartModel
     */
    private $cartmodel;

    /**
=======
>>>>>>> 50b054ffc2e (Resolved conflicts)
     * @param CartRepositoryInterface $cartRepository
     * @param Session $checkoutSession
     * @param AddressRepositoryInterface $addressRepository
     * @param DisableMultishipping $disableMultishipping
<<<<<<< HEAD
     * @param CartModel $cartmodel
=======
>>>>>>> 50b054ffc2e (Resolved conflicts)
     */
    public function __construct(
        CartRepositoryInterface $cartRepository,
        Session $checkoutSession,
        AddressRepositoryInterface $addressRepository,
<<<<<<< HEAD
        DisableMultishipping $disableMultishipping,
        CartModel $cartmodel
=======
        DisableMultishipping $disableMultishipping
>>>>>>> 50b054ffc2e (Resolved conflicts)
    ) {
        $this->cartRepository = $cartRepository;
        $this->checkoutSession = $checkoutSession;
        $this->addressRepository = $addressRepository;
        $this->disableMultishipping = $disableMultishipping;
<<<<<<< HEAD
        $this->cartmodel = $cartmodel;
=======
>>>>>>> 50b054ffc2e (Resolved conflicts)
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
<<<<<<< HEAD
                $quote = $this->cartRepository->get($quote->getId());
                $this->cartmodel->setQuote($quote);
=======
                $quote = $this->checkoutSession->getQuote();
                $quote->setTotalsCollectedFlag(false);
                $this->cartRepository->save($quote);
>>>>>>> 50b054ffc2e (Resolved conflicts)
            }
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
