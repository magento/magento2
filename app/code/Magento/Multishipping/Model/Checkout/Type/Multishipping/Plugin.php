<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Multishipping\Model\Checkout\Type\Multishipping;

use Magento\Checkout\Model\Cart as CustomerCart;
use Magento\Checkout\Model\Cart\RequestQuantityProcessor;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Filter\LocalizedToNormalized;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Quote\Api\CartRepositoryInterface;

/**
 * Class for afterSave and beforeSave plugin for quote
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class Plugin
{
    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var ResolverInterface
     */
    private $locale;

    /**
     * @var RequestQuantityProcessor
     */
    private $quantityProcessor;

    /**
     * Initialize constructor
     *
     * @param Session $checkoutSession
     * @param CartRepositoryInterface $cartRepository
     * @param RequestInterface $request
     * @param ResolverInterface $locale
     * @param RequestQuantityProcessor $quantityProcessor
     */
    public function __construct(
        Session $checkoutSession,
        CartRepositoryInterface $cartRepository,
        RequestInterface $request,
        ResolverInterface $locale,
        RequestQuantityProcessor $quantityProcessor
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->cartRepository = $cartRepository;
        $this->request = $request;
        $this->locale = $locale;
        $this->quantityProcessor = $quantityProcessor;
    }

    /**
     * Map STEP_SELECT_ADDRESSES to Cart::CHECKOUT_STATE_BEGIN
     *
     * @param CustomerCart $subject
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeSave(CustomerCart $subject)
    {
        if ($this->checkoutSession->getCheckoutState() === State::STEP_SELECT_ADDRESSES) {
            $this->checkoutSession->setCheckoutState(Session::CHECKOUT_STATE_BEGIN);
        }
    }

    /**
     * Disable MultiShipping mode before saving Quote.
     *
     * @param CustomerCart $customerCart
     * @param CustomerCart $resultCart
     * @return CustomerCart $resultCart
     */
    public function afterSave(CustomerCart $customerCart, CustomerCart $resultCart): CustomerCart
    {
        $isMultipleShippingAddressesPresent = $customerCart->getCheckoutSession()->getMultiShippingAddressesFlag();
        if ($isMultipleShippingAddressesPresent) {
            $customerCart->getCheckoutSession()->setMultiShippingAddressesFlag(false);
            $params = $this->request->getParams();
            if (isset($params['qty'])) {
                $this->recollectCartSummary($params['qty'], $resultCart);
            }
        }
        return $resultCart;
    }

    /**
     * Recollect and recalculate cart summary data
     *
     * @param int|float|string|array $quantity
     * @param CustomerCart $cart
     */
    private function recollectCartSummary($quantity, CustomerCart $cart): void
    {
        $quote = $cart->getQuote();

        $filter = new LocalizedToNormalized(
            ['locale' => $this->locale->getLocale()]
        );
        $newQty = $this->quantityProcessor->prepareQuantity($quantity);
        $newQty = $filter->filter($newQty);

        $quote->setItemsQty($quote->getItemsQty() + $newQty);
        $quote->setTotalsCollectedFlag(false);
        $quote->collectTotals();
        $this->cartRepository->save($quote);
    }
}
