<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Model;

use Magento\Checkout\Api\Exception\PaymentProcessingRateLimitExceededException;
use Magento\Checkout\Api\PaymentProcessingRateLimiterInterface;
use Magento\Checkout\Api\PaymentSavingRateLimiterInterface;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\AddressInterface as QuoteAddressInterface;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Psr\Log\LoggerInterface;

/**
 * Payment information management service.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PaymentInformationManagement implements \Magento\Checkout\Api\PaymentInformationManagementInterface
{
    /**
     * @var \Magento\Quote\Api\BillingAddressManagementInterface
     * @deprecated 100.1.0 This call was substituted to eliminate extra quote::save call
     * @see not in use anymore
     */
    protected $billingAddressManagement;

    /**
     * @var \Magento\Quote\Api\PaymentMethodManagementInterface
     */
    protected $paymentMethodManagement;

    /**
     * @var \Magento\Quote\Api\CartManagementInterface
     */
    protected $cartManagement;

    /**
     * @var PaymentDetailsFactory
     */
    protected $paymentDetailsFactory;

    /**
     * @var \Magento\Quote\Api\CartTotalRepositoryInterface
     */
    protected $cartTotalsRepository;

    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var PaymentProcessingRateLimiterInterface
     */
    private $paymentRateLimiter;

    /**
     * @var PaymentSavingRateLimiterInterface
     */
    private $saveRateLimiter;

    /**
     * @var bool
     */
    private $saveRateLimiterDisabled = false;

    /**
     * @var AddressRepositoryInterface
     */
    private $addressRepository;

    /**
     * @var AddressComparatorInterface
     */
    private $addressComparator;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param \Magento\Quote\Api\BillingAddressManagementInterface $billingAddressManagement
     * @param \Magento\Quote\Api\PaymentMethodManagementInterface $paymentMethodManagement
     * @param \Magento\Quote\Api\CartManagementInterface $cartManagement
     * @param PaymentDetailsFactory $paymentDetailsFactory
     * @param \Magento\Quote\Api\CartTotalRepositoryInterface $cartTotalsRepository
     * @param PaymentProcessingRateLimiterInterface|null $paymentRateLimiter
     * @param PaymentSavingRateLimiterInterface|null $saveRateLimiter
     * @param CartRepositoryInterface|null $cartRepository
     * @param AddressRepositoryInterface|null $addressRepository
     * @param AddressComparatorInterface|null $addressComparator
     * @param LoggerInterface|null $logger
     * @codeCoverageIgnore
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Quote\Api\BillingAddressManagementInterface $billingAddressManagement,
        \Magento\Quote\Api\PaymentMethodManagementInterface $paymentMethodManagement,
        \Magento\Quote\Api\CartManagementInterface $cartManagement,
        \Magento\Checkout\Model\PaymentDetailsFactory $paymentDetailsFactory,
        \Magento\Quote\Api\CartTotalRepositoryInterface $cartTotalsRepository,
        ?PaymentProcessingRateLimiterInterface $paymentRateLimiter = null,
        ?PaymentSavingRateLimiterInterface $saveRateLimiter = null,
        ?CartRepositoryInterface $cartRepository = null,
        ?AddressRepositoryInterface $addressRepository = null,
        ?AddressComparatorInterface $addressComparator = null,
        ?LoggerInterface $logger = null
    ) {
        $this->billingAddressManagement = $billingAddressManagement;
        $this->paymentMethodManagement = $paymentMethodManagement;
        $this->cartManagement = $cartManagement;
        $this->paymentDetailsFactory = $paymentDetailsFactory;
        $this->cartTotalsRepository = $cartTotalsRepository;
        $this->paymentRateLimiter = $paymentRateLimiter
            ?? ObjectManager::getInstance()->get(PaymentProcessingRateLimiterInterface::class);
        $this->saveRateLimiter = $saveRateLimiter
            ?? ObjectManager::getInstance()->get(PaymentSavingRateLimiterInterface::class);
        $this->cartRepository = $cartRepository
            ?? ObjectManager::getInstance()->get(CartRepositoryInterface::class);
        $this->addressRepository = $addressRepository
            ?? ObjectManager::getInstance()->get(AddressRepositoryInterface::class);
        $this->addressComparator = $addressComparator
            ?? ObjectManager::getInstance()->get(AddressComparatorInterface::class);
        $this->logger = $logger ?? ObjectManager::getInstance()->get(LoggerInterface::class);
    }

    /**
     * @inheritdoc
     */
    public function savePaymentInformationAndPlaceOrder(
        $cartId,
        PaymentInterface $paymentMethod,
        ?QuoteAddressInterface $billingAddress = null
    ) {
        $this->paymentRateLimiter->limit();
        try {
            //Have to do this hack because of plugins for savePaymentInformation()
            $this->saveRateLimiterDisabled = true;
            $this->savePaymentInformation($cartId, $paymentMethod, $billingAddress);
        } finally {
            $this->saveRateLimiterDisabled = false;
        }
        try {
            $orderId = $this->cartManagement->placeOrder($cartId);
        } catch (LocalizedException $e) {
            $this->logger->critical(
                'Placing an Order failed (reason: '.  $e->getMessage() .')',
                [
                    'quote_id' => $cartId,
                    'exception' => (string)$e,
                    'is_guest_checkout' => false
                ]
            );
            throw new CouldNotSaveException(
                __($e->getMessage()),
                $e
            );
        } catch (\Exception $e) {
            $this->logger->critical($e);
            throw new CouldNotSaveException(
                __('A server error stopped your order from being placed. Please try to place your order again.'),
                $e
            );
        }
        return $orderId;
    }

    /**
     * @inheritdoc
     *
     * @throws LocalizedException
     */
    public function savePaymentInformation(
        $cartId,
        PaymentInterface $paymentMethod,
        ?QuoteAddressInterface $billingAddress = null
    ) {
        if (!$this->saveRateLimiterDisabled) {
            try {
                $this->saveRateLimiter->limit();
            } catch (PaymentProcessingRateLimitExceededException $ex) {
                //Limit reached
                return false;
            }
        }

        if ($billingAddress) {
            /** @var \Magento\Quote\Model\Quote $quote */
            $quote = $this->cartRepository->getActive($cartId);
            $customerId = $quote->getBillingAddress()
                ->getCustomerId();
            if (!$billingAddress->getCustomerId() && $customerId) {
                //It's necessary to verify the price rules with the customer data
                $billingAddress->setCustomerId($customerId);
            }
            $this->updateCustomerBillingAddressId($quote, $billingAddress);
            $quote->removeAddress($quote->getBillingAddress()->getId());
            $quote->setBillingAddress($billingAddress);
            $quote->setDataChanges(true);
            if ($quote->getShippingAddress()) {
                $this->processShippingAddress($quote);
            }
        }
        $this->paymentMethodManagement->set($cartId, $paymentMethod);
        return true;
    }

    /**
     * @inheritdoc
     */
    public function getPaymentInformation($cartId)
    {
        /** @var \Magento\Checkout\Api\Data\PaymentDetailsInterface $paymentDetails */
        $paymentDetails = $this->paymentDetailsFactory->create();
        $paymentDetails->setPaymentMethods($this->paymentMethodManagement->getList($cartId));
        $paymentDetails->setTotals($this->cartTotalsRepository->get($cartId));
        return $paymentDetails;
    }

    /**
     * Processes shipping address.
     *
     * @param Quote $quote
     * @return void
     * @throws LocalizedException
     */
    private function processShippingAddress(Quote $quote): void
    {
        $shippingAddress = $quote->getShippingAddress();
        $billingAddress = $quote->getBillingAddress();
        if ($shippingAddress->getShippingMethod()) {
            $shippingRate = $shippingAddress->getShippingRateByCode($shippingAddress->getShippingMethod());
            if ($shippingRate) {
                $shippingAddress->setLimitCarrier($shippingRate->getCarrier());
            }
        }
        if ($this->addressComparator->isEqual($shippingAddress, $billingAddress)) {
            $shippingAddress->setSameAsBilling(1);
        }
        // Save new address in the customer address book and set it id for billing and shipping quote addresses.
        if ($shippingAddress->getSameAsBilling() &&
            $shippingAddress->getSaveInAddressBook() &&
            !$shippingAddress->getCustomerAddressId()
        ) {
            $shippingAddressData = $shippingAddress->exportCustomerAddress();
            $this->saveAddressesAsDefault($quote, $shippingAddressData, $billingAddress);
            $shippingAddressData->setCustomerId($quote->getCustomerId());
            $this->addressRepository->save($shippingAddressData);
            $quote->addCustomerAddress($shippingAddressData);
            $shippingAddress->setCustomerAddressData($shippingAddressData);
            $shippingAddress->setCustomerAddressId($shippingAddressData->getId());
            $billingAddress->setCustomerAddressId($shippingAddressData->getId());
        }
    }

    /**
     * Update customer billing address ID if the address is the same as the quote billing address.
     *
     * @param Quote $quote
     * @param QuoteAddressInterface $billingAddress
     * @return void
     */
    private function updateCustomerBillingAddressId(Quote $quote, QuoteAddressInterface $billingAddress): void
    {
        $quoteBillingAddress = $quote->getBillingAddress();
        if (!$billingAddress->getCustomerAddressId() &&
            $quoteBillingAddress->getCustomerAddressId() &&
            $this->addressComparator->isEqual($billingAddress, $quoteBillingAddress)
        ) {
            $billingAddress->setCustomerAddressId($quoteBillingAddress->getCustomerAddressId());
        }
    }

    /**
     * Save addresses as default shipping/ billing if they are not set yet.
     *
     * @param Quote $quote
     * @param AddressInterface $shippingAddressData
     * @param Address $billingAddress
     * @return void
     */
    private function saveAddressesAsDefault(
        Quote $quote,
        AddressInterface $shippingAddressData,
        Address $billingAddress
    ): void {
        $customer = $quote->getCustomer();
        $hasDefaultBilling = (bool)$customer->getDefaultBilling();
        $hasDefaultShipping = (bool)$customer->getDefaultShipping();
        if (!$hasDefaultShipping) {
            //Make provided address as default shipping address
            $shippingAddressData->setIsDefaultShipping(true);
            if (!$hasDefaultBilling && !$billingAddress->getSaveInAddressBook()) {
                $shippingAddressData->setIsDefaultBilling(true);
            }
        }
    }
}
