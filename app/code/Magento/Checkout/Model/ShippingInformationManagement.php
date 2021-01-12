<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Model;

use Magento\Checkout\Api\Data\PaymentDetailsInterface;
use Magento\Checkout\Api\Data\ShippingInformationInterface;
use Magento\Checkout\Api\ShippingInformationManagementInterface;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\CartTotalRepositoryInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\CartExtensionFactory;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\PaymentMethodManagementInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\TotalsCollector;
use Magento\Quote\Model\QuoteAddressValidator;
use Magento\Quote\Model\ShippingAssignmentFactory;
use Magento\Quote\Model\ShippingFactory;
use Psr\Log\LoggerInterface as Logger;

/**
 * Class checkout shipping information management
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ShippingInformationManagement implements ShippingInformationManagementInterface
{
    /**
     * @var PaymentMethodManagementInterface
     */
    protected $paymentMethodManagement;

    /**
     * @var PaymentDetailsFactory
     */
    protected $paymentDetailsFactory;

    /**
     * @var CartTotalRepositoryInterface
     */
    protected $cartTotalsRepository;

    /**
     * @var CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var QuoteAddressValidator
     */
    protected $addressValidator;

    /**
     * @var AddressRepositoryInterface
     * @deprecated 100.2.0
     */
    protected $addressRepository;

    /**
     * @var ScopeConfigInterface
     * @deprecated 100.2.0
     */
    protected $scopeConfig;

    /**
     * @var TotalsCollector
     * @deprecated 100.2.0
     */
    protected $totalsCollector;

    /**
     * @var CartExtensionFactory
     */
    private $cartExtensionFactory;

    /**
     * @var ShippingAssignmentFactory
     */
    protected $shippingAssignmentFactory;

    /**
     * @var ShippingFactory
     */
    private $shippingFactory;

    /**
     * @param PaymentMethodManagementInterface $paymentMethodManagement
     * @param PaymentDetailsFactory $paymentDetailsFactory
     * @param CartTotalRepositoryInterface $cartTotalsRepository
     * @param CartRepositoryInterface $quoteRepository
     * @param QuoteAddressValidator $addressValidator
     * @param Logger $logger
     * @param AddressRepositoryInterface $addressRepository
     * @param ScopeConfigInterface $scopeConfig
     * @param TotalsCollector $totalsCollector
     * @param CartExtensionFactory|null $cartExtensionFactory
     * @param ShippingAssignmentFactory|null $shippingAssignmentFactory
     * @param ShippingFactory|null $shippingFactory
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        PaymentMethodManagementInterface $paymentMethodManagement,
        PaymentDetailsFactory $paymentDetailsFactory,
        CartTotalRepositoryInterface $cartTotalsRepository,
        CartRepositoryInterface $quoteRepository,
        QuoteAddressValidator $addressValidator,
        Logger $logger,
        AddressRepositoryInterface $addressRepository,
        ScopeConfigInterface $scopeConfig,
        TotalsCollector $totalsCollector,
        CartExtensionFactory $cartExtensionFactory = null,
        ShippingAssignmentFactory $shippingAssignmentFactory = null,
        ShippingFactory $shippingFactory = null
    ) {
        $this->paymentMethodManagement = $paymentMethodManagement;
        $this->paymentDetailsFactory = $paymentDetailsFactory;
        $this->cartTotalsRepository = $cartTotalsRepository;
        $this->quoteRepository = $quoteRepository;
        $this->addressValidator = $addressValidator;
        $this->logger = $logger;
        $this->addressRepository = $addressRepository;
        $this->scopeConfig = $scopeConfig;
        $this->totalsCollector = $totalsCollector;
        $this->cartExtensionFactory = $cartExtensionFactory ?: ObjectManager::getInstance()
            ->get(CartExtensionFactory::class);
        $this->shippingAssignmentFactory = $shippingAssignmentFactory ?: ObjectManager::getInstance()
            ->get(ShippingAssignmentFactory::class);
        $this->shippingFactory = $shippingFactory ?: ObjectManager::getInstance()
            ->get(ShippingFactory::class);
    }

    /**
     * Save address information.
     *
     * @param int $cartId
     * @param ShippingInformationInterface $addressInformation
     * @return PaymentDetailsInterface
     * @throws InputException
     * @throws NoSuchEntityException
     * @throws StateException
     */
    public function saveAddressInformation(
        $cartId,
        ShippingInformationInterface $addressInformation
    ): PaymentDetailsInterface {
        /** @var Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);
        $this->validateQuote($quote);

        $address = $addressInformation->getShippingAddress();
        $this->validateAddress($address);

        if (!$address->getCustomerAddressId()) {
            $address->setCustomerAddressId(null);
        }

        try {
            $billingAddress = $addressInformation->getBillingAddress();
            if ($billingAddress) {
                if (!$billingAddress->getCustomerAddressId()) {
                    $billingAddress->setCustomerAddressId(null);
                }
                $this->addressValidator->validateForCart($quote, $billingAddress);
                $quote->setBillingAddress($billingAddress);
            }

            $this->addressValidator->validateForCart($quote, $address);
            $carrierCode = $addressInformation->getShippingCarrierCode();
            $address->setLimitCarrier($carrierCode);
            $methodCode = $addressInformation->getShippingMethodCode();
            $quote = $this->prepareShippingAssignment($quote, $address, $carrierCode . '_' . $methodCode);

            $quote->setIsMultiShipping(false);

            $this->quoteRepository->save($quote);
        } catch (LocalizedException $e) {
            $this->logger->critical($e);
            throw new InputException(
                __(
                    'The shipping information was unable to be saved. Error: "%message"',
                    ['message' => $e->getMessage()]
                )
            );
        } catch (\Exception $e) {
            $this->logger->critical($e);
            throw new InputException(
                __('The shipping information was unable to be saved. Verify the input data and try again.')
            );
        }

        $shippingAddress = $quote->getShippingAddress();

        if (!$quote->getIsVirtual()
            && !$shippingAddress->getShippingRateByCode($shippingAddress->getShippingMethod())
        ) {
            $errorMessage = $methodCode ?
                __('Carrier with such method not found: %1, %2', $carrierCode, $methodCode)
                : __('The shipping method is missing. Select the shipping method and try again.');
            throw new NoSuchEntityException(
                $errorMessage
            );
        }

        /** @var PaymentDetailsInterface $paymentDetails */
        $paymentDetails = $this->paymentDetailsFactory->create();
        $paymentDetails->setPaymentMethods($this->paymentMethodManagement->getList($cartId));
        $paymentDetails->setTotals($this->cartTotalsRepository->get($cartId));
        return $paymentDetails;
    }

    /**
     * Validate shipping address
     *
     * @param AddressInterface|null $address
     * @return void
     * @throws StateException
     */
    private function validateAddress(?AddressInterface $address): void
    {
        if (!$address || !$address->getCountryId()) {
            throw new StateException(__('The shipping address is missing. Set the address and try again.'));
        }
    }

    /**
     * Validate quote
     *
     * @param Quote $quote
     * @throws InputException
     * @return void
     */
    protected function validateQuote(Quote $quote): void
    {
        if (!$quote->getItemsCount()) {
            throw new InputException(
                __('The shipping method can\'t be set for an empty cart. Add an item to cart and try again.')
            );
        }
    }

    /**
     * Prepare shipping assignment.
     *
     * @param CartInterface $quote
     * @param AddressInterface $address
     * @param string $method
     * @return CartInterface
     */
    private function prepareShippingAssignment(CartInterface $quote, AddressInterface $address, $method): CartInterface
    {
        $cartExtension = $quote->getExtensionAttributes();
        if ($cartExtension === null) {
            $cartExtension = $this->cartExtensionFactory->create();
        }

        $shippingAssignments = $cartExtension->getShippingAssignments();
        if (empty($shippingAssignments)) {
            $shippingAssignment = $this->shippingAssignmentFactory->create();
        } else {
            $shippingAssignment = $shippingAssignments[0];
        }

        $shipping = $shippingAssignment->getShipping();
        if ($shipping === null) {
            $shipping = $this->shippingFactory->create();
        }

        $shipping->setAddress($address);
        $shipping->setMethod($method);
        $shippingAssignment->setShipping($shipping);
        $cartExtension->setShippingAssignments([$shippingAssignment]);
        return $quote->setExtensionAttributes($cartExtension);
    }
}
