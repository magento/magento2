<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Model\Paypal\Helper;

use Magento\Quote\Model\Quote;
use Magento\Checkout\Helper\Data;
use Magento\Customer\Model\Group;
use Magento\Customer\Model\Session;
use Magento\Checkout\Model\Type\Onepage;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Checkout\Api\AgreementsValidatorInterface;

/**
 * Class OrderPlace
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 2.1.0
 */
class OrderPlace extends AbstractHelper
{
    /**
     * @var CartManagementInterface
     * @since 2.1.0
     */
    private $cartManagement;

    /**
     * @var AgreementsValidatorInterface
     * @since 2.1.0
     */
    private $agreementsValidator;

    /**
     * @var Session
     * @since 2.1.0
     */
    private $customerSession;

    /**
     * @var Data
     * @since 2.1.0
     */
    private $checkoutHelper;

    /**
     * Constructor
     *
     * @param CartManagementInterface $cartManagement
     * @param AgreementsValidatorInterface $agreementsValidator
     * @param Session $customerSession
     * @param Data $checkoutHelper
     * @since 2.1.0
     */
    public function __construct(
        CartManagementInterface $cartManagement,
        AgreementsValidatorInterface $agreementsValidator,
        Session $customerSession,
        Data $checkoutHelper
    ) {
        $this->cartManagement = $cartManagement;
        $this->agreementsValidator = $agreementsValidator;
        $this->customerSession = $customerSession;
        $this->checkoutHelper = $checkoutHelper;
    }

    /**
     * Execute operation
     *
     * @param Quote $quote
     * @param array $agreement
     * @return void
     * @throws LocalizedException
     * @since 2.1.0
     */
    public function execute(Quote $quote, array $agreement)
    {
        if (!$this->agreementsValidator->isValid($agreement)) {
            throw new LocalizedException(__('Please agree to all the terms and conditions before placing the order.'));
        }

        if ($this->getCheckoutMethod($quote) === Onepage::METHOD_GUEST) {
            $this->prepareGuestQuote($quote);
        }

        $this->disabledQuoteAddressValidation($quote);

        $quote->collectTotals();
        $this->cartManagement->placeOrder($quote->getId());
    }

    /**
     * Get checkout method
     *
     * @param Quote $quote
     * @return string
     * @since 2.1.0
     */
    private function getCheckoutMethod(Quote $quote)
    {
        if ($this->customerSession->isLoggedIn()) {
            return Onepage::METHOD_CUSTOMER;
        }
        if (!$quote->getCheckoutMethod()) {
            if ($this->checkoutHelper->isAllowedGuestCheckout($quote)) {
                $quote->setCheckoutMethod(Onepage::METHOD_GUEST);
            } else {
                $quote->setCheckoutMethod(Onepage::METHOD_REGISTER);
            }
        }

        return $quote->getCheckoutMethod();
    }

    /**
     * Prepare quote for guest checkout order submit
     *
     * @param Quote $quote
     * @return void
     * @since 2.1.0
     */
    private function prepareGuestQuote(Quote $quote)
    {
        $quote->setCustomerId(null)
            ->setCustomerEmail($quote->getBillingAddress()->getEmail())
            ->setCustomerIsGuest(true)
            ->setCustomerGroupId(Group::NOT_LOGGED_IN_ID);
    }
}
