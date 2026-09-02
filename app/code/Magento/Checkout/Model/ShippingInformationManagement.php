<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Checkout\Model;

use Magento\Checkout\Api\Data\PaymentDetailsInterface;
use Magento\Checkout\Api\Data\ShippingInformationInterface;
use Magento\Checkout\Api\ShippingInformationManagementInterface;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterface as CustomerAddressInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
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
use Magento\Quote\Model\QuoteAddressValidationService;
use Magento\Quote\Model\ShippingAssignmentFactory;
use Magento\Quote\Model\ShippingFactory;
use Psr\Log\LoggerInterface as Logger;

/**
 * Class checkout shipping information management
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class ShippingInformationManagement implements ShippingInformationManagementInterface
{
    /**
     * @var PaymentMethodManagementInterface
     */
    protected PaymentMethodManagementInterface $paymentMethodManagement;

    /**
     * @var PaymentDetailsFactory
     */
    protected PaymentDetailsFactory $paymentDetailsFactory;

    /**
     * @var CartTotalRepositoryInterface
     */
    protected CartTotalRepositoryInterface $cartTotalsRepository;

    /**
     * @var CartRepositoryInterface
     */
    protected CartRepositoryInterface $quoteRepository;
    /**
     * @var Logger
     */
    protected Logger $logger;

    /**
     * @var QuoteAddressValidator
     */
    protected QuoteAddressValidator $addressValidator;

    /**
     * @var AddressRepositoryInterface
     * @deprecated 100.2.0
     * @see AddressRepositoryInterface
     */
    protected AddressRepositoryInterface $addressRepository;

    /**
     * @var ScopeConfigInterface
     * @deprecated 100.2.0
     * @see ScopeConfigInterface
     */
    protected ScopeConfigInterface $scopeConfig;

    /**
     * @var TotalsCollector
     * @deprecated 100.2.0
     * @see TotalsCollector
     */
    protected TotalsCollector $totalsCollector;

    /**
     * @var CartExtensionFactory
     */
    private CartExtensionFactory $cartExtensionFactory;

    /**
     * @var ShippingAssignmentFactory
     */
    protected ShippingAssignmentFactory $shippingAssignmentFactory;

    /**
     * @var ShippingFactory
     */
    private $shippingFactory;

    /**
     * @var AddressComparatorInterface
     */
    private $addressComparator;

    /**
     * @var QuoteAddressValidationService
     */
    private $quoteAddressValidationService;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var AddressRepositoryInterface
     */
    private $customerAddressRepository;

    /**
     * Address book of the customer the cart belongs to, loaded once per saveAddressInformation() call.
     *
     * @var CustomerAddressInterface[]|null
     */
    private $customerAddressBook;

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
     * @param AddressComparatorInterface|null $addressComparator
     * @param QuoteAddressValidationService|null $quoteAddressValidationService
     * @param SearchCriteriaBuilder|null $searchCriteriaBuilder
     * @param AddressRepositoryInterface|null $customerAddressRepository
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
        ?CartExtensionFactory $cartExtensionFactory = null,
        ?ShippingAssignmentFactory $shippingAssignmentFactory = null,
        ?ShippingFactory $shippingFactory = null,
        ?AddressComparatorInterface $addressComparator = null,
        ?QuoteAddressValidationService $quoteAddressValidationService = null,
        ?SearchCriteriaBuilder $searchCriteriaBuilder = null,
        ?AddressRepositoryInterface $customerAddressRepository = null
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
        $this->addressComparator = $addressComparator
            ?? ObjectManager::getInstance()->get(AddressComparatorInterface::class);
        $this->quoteAddressValidationService = $quoteAddressValidationService ?: ObjectManager::getInstance()
            ->get(QuoteAddressValidationService::class);
        $this->searchCriteriaBuilder = $searchCriteriaBuilder ?: ObjectManager::getInstance()
            ->get(SearchCriteriaBuilder::class);
        $this->customerAddressRepository = $customerAddressRepository ?: ObjectManager::getInstance()
            ->get(AddressRepositoryInterface::class);
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
        $this->customerAddressBook = null;

        /** @var Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);
        $this->validateQuote($quote);

        $address = $addressInformation->getShippingAddress();
        $this->validateAddress($address);

        $this->updateCustomerShippingAddressId($quote, $address);
        if (!$address->getCustomerAddressId()) {
            $address->setCustomerAddressId(null);
        }

        try {
            $billingAddress = $addressInformation->getBillingAddress();
            $this->quoteAddressValidationService->validateAddressesWithRules(
                $quote,
                $address,
                $billingAddress
            );
            if ($billingAddress) {
                $this->updateCustomerBillingAddressId($quote, $billingAddress);
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
    private function prepareShippingAssignment(
        CartInterface $quote,
        AddressInterface $address,
        string $method
    ): CartInterface {
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

    /**
     * Update customer shipping address ID
     *
     * Reuses the ID of the quote shipping address, or of an entry that is already saved in the
     * customer's address book, so that a duplicate address is not created.
     *
     * @param Quote $quote
     * @param AddressInterface $address
     * @return void
     */
    private function updateCustomerShippingAddressId(Quote $quote, AddressInterface $address): void
    {
        if ($address->getCustomerAddressId()) {
            return;
        }

        $quoteShippingAddress = $quote->getShippingAddress();
        if ($quoteShippingAddress->getCustomerAddressId() &&
            $this->addressComparator->isEqual($address, $quoteShippingAddress)
        ) {
            $address->setCustomerAddressId($quoteShippingAddress->getCustomerAddressId());
            return;
        }

        $this->linkToExistingCustomerAddress($quote, $address);
    }

    /**
     * Update customer billing address ID
     *
     * Reuses the ID of the quote billing address, or of an entry that is already saved in the
     * customer's address book, so that a duplicate address is not created.
     *
     * @param Quote $quote
     * @param AddressInterface $billingAddress
     * @return void
     */
    private function updateCustomerBillingAddressId(Quote $quote, AddressInterface $billingAddress): void
    {
        $quoteBillingAddress = $quote->getBillingAddress();
        if ($quoteBillingAddress->getCustomerAddressId() &&
            $this->addressComparator->isEqual($billingAddress, $quoteBillingAddress)
        ) {
            $billingAddress->setCustomerAddressId($quoteBillingAddress->getCustomerAddressId());
            return;
        }

        $this->linkToExistingCustomerAddress($quote, $billingAddress);
    }

    /**
     * Point the address at a matching address book entry instead of letting a duplicate be created.
     *
     * Addresses the case where a customer uses "Add new address" and manually re-enters the details
     * of an address that is already stored in their address book.
     *
     * @param Quote $quote
     * @param AddressInterface $address
     * @return void
     */
    private function linkToExistingCustomerAddress(Quote $quote, AddressInterface $address): void
    {
        // Only an address that is about to be written to the address book can produce a duplicate.
        if (!$address->getSaveInAddressBook()) {
            return;
        }

        $matchingAddressId = $this->findMatchingCustomerAddressId($quote, $address);
        if (!$matchingAddressId) {
            return;
        }

        $address->setCustomerAddressId($matchingAddressId);
        // The entry already exists, so there is nothing left to write. Skipping the write also keeps
        // the data that is not part of the comparison - custom attributes, default billing/shipping
        // flags - on the stored address untouched.
        $address->setSaveInAddressBook(0);
        // QuoteManagement::_prepareCustomerQuote() re-saves the address as a new one whenever
        // getCustomerId() is empty, regardless of the save-in-address-book flag or the address ID
        // set above - exportCustomerAddress() does not carry customer_address_id over as the id of
        // the exported object. Setting the owner here closes that gate and keeps the order pointed
        // at the existing address book entry instead of a freshly duplicated one.
        $address->setCustomerId((int)$quote->getCustomerId());
    }

    /**
     * Search the customer's address book for an address equal to the given quote address.
     *
     * @param Quote $quote
     * @param AddressInterface $address
     * @return int|null
     */
    private function findMatchingCustomerAddressId(Quote $quote, AddressInterface $address): ?int
    {
        $customerId = (int)$quote->getCustomerId();
        if (!$customerId) {
            return null;
        }

        $addressData = $this->extractQuoteAddressData($address);
        foreach ($this->getCustomerAddressBook($customerId) as $customerAddress) {
            if ($this->isSameAddress($addressData, $this->extractCustomerAddressData($customerAddress))) {
                return (int)$customerAddress->getId();
            }
        }

        return null;
    }

    /**
     * Load the address book of the given customer, once per saveAddressInformation() call.
     *
     * @param int $customerId
     * @return CustomerAddressInterface[]
     */
    private function getCustomerAddressBook(int $customerId): array
    {
        if ($this->customerAddressBook !== null) {
            return $this->customerAddressBook;
        }

        // Addresses of the customer_address EAV entity reference their owner through parent_id;
        // customer_id is not an attribute of that collection and is rejected by the collection processor.
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('parent_id', $customerId)
            ->create();

        try {
            $addresses = $this->customerAddressRepository->getList($searchCriteria)->getItems();
        } catch (\Exception $e) {
            // A failed lookup only means the duplicate cannot be detected, so checkout continues.
            $this->logger->error($e);
            $addresses = [];
        }

        // An address book that already contains duplicates must always resolve to the same entry.
        usort(
            $addresses,
            static fn (CustomerAddressInterface $left, CustomerAddressInterface $right)
                => (int)$left->getId() <=> (int)$right->getId()
        );
        $this->customerAddressBook = $addresses;

        return $this->customerAddressBook;
    }

    /**
     * Bring a quote address to the form used for comparison.
     *
     * @param AddressInterface $address
     * @return array
     */
    private function extractQuoteAddressData(AddressInterface $address): array
    {
        return [
            'prefix' => $this->normalize($address->getPrefix()),
            'firstname' => $this->normalize($address->getFirstname()),
            'middlename' => $this->normalize($address->getMiddlename()),
            'lastname' => $this->normalize($address->getLastname()),
            'suffix' => $this->normalize($address->getSuffix()),
            'company' => $this->normalize($address->getCompany()),
            'street' => $this->normalizeStreet($address->getStreet()),
            'city' => $this->normalize($address->getCity()),
            'region_id' => $this->normalize($address->getRegionId()),
            'region' => $this->normalize($address->getRegion()),
            'postcode' => $this->normalize($address->getPostcode()),
            'country_id' => $this->normalize($address->getCountryId()),
            'telephone' => $this->normalize($address->getTelephone()),
            'fax' => $this->normalize($address->getFax()),
            'vat_id' => $this->normalize($address->getVatId()),
        ];
    }

    /**
     * Bring an address book entry to the form used for comparison.
     *
     * @param CustomerAddressInterface $address
     * @return array
     */
    private function extractCustomerAddressData(CustomerAddressInterface $address): array
    {
        $region = $address->getRegion();

        return [
            'prefix' => $this->normalize($address->getPrefix()),
            'firstname' => $this->normalize($address->getFirstname()),
            'middlename' => $this->normalize($address->getMiddlename()),
            'lastname' => $this->normalize($address->getLastname()),
            'suffix' => $this->normalize($address->getSuffix()),
            'company' => $this->normalize($address->getCompany()),
            'street' => $this->normalizeStreet($address->getStreet()),
            'city' => $this->normalize($address->getCity()),
            'region_id' => $this->normalize($address->getRegionId()),
            'region' => $this->normalize($region === null ? null : $region->getRegion()),
            'postcode' => $this->normalize($address->getPostcode()),
            'country_id' => $this->normalize($address->getCountryId()),
            'telephone' => $this->normalize($address->getTelephone()),
            'fax' => $this->normalize($address->getFax()),
            'vat_id' => $this->normalize($address->getVatId()),
        ];
    }

    /**
     * Compare two addresses that were brought to their comparable form.
     *
     * @param array $quoteAddressData
     * @param array $customerAddressData
     * @return bool
     */
    private function isSameAddress(array $quoteAddressData, array $customerAddressData): bool
    {
        // A region is stored as an id for countries with a predefined region list and as free text
        // for the remaining ones. Where both sides carry an id it decides, and the text - which the
        // checkout does not always fill in - is left out of the comparison.
        if ($quoteAddressData['region_id'] !== '' && $customerAddressData['region_id'] !== '') {
            if ($quoteAddressData['region_id'] !== $customerAddressData['region_id']) {
                return false;
            }
            unset($quoteAddressData['region'], $customerAddressData['region']);
        }
        unset($quoteAddressData['region_id'], $customerAddressData['region_id']);

        return $quoteAddressData == $customerAddressData;
    }

    /**
     * Bring a single address field to a comparable form.
     *
     * The value is cast rather than type hinted because REST and GraphQL payloads deliver numeric
     * postcodes and phone numbers as integers, which would fail the strict types of this file.
     *
     * @param mixed $value
     * @return string
     */
    private function normalize($value): string
    {
        return mb_strtolower(trim((string)$value));
    }

    /**
     * Bring the street of an address to a comparable form.
     *
     * The number of stored lines differs between an address entered in checkout and one loaded from
     * the address book, so empty lines are dropped and the remaining ones are re-indexed.
     *
     * @param string[]|string|null $street
     * @return string[]
     */
    private function normalizeStreet($street): array
    {
        $lines = [];
        foreach ((array)$street as $line) {
            $line = $this->normalize($line);
            if ($line !== '') {
                $lines[] = $line;
            }
        }

        return $lines;
    }
}
