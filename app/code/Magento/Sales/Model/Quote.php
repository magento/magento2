<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\Data\GroupInterface;
use Magento\Sales\Model\Quote\Address;

/**
 * Quote model
 *
 * Supported events:
 *  sales_quote_load_after
 *  sales_quote_save_before
 *  sales_quote_save_after
 *  sales_quote_delete_before
 *  sales_quote_delete_after
 *
 * @method Quote setStoreId(int $value)
 * @method string getCreatedAt()
 * @method Quote setCreatedAt(string $value)
 * @method string getUpdatedAt()
 * @method Quote setUpdatedAt(string $value)
 * @method string getConvertedAt()
 * @method Quote setConvertedAt(string $value)
 * @method int getIsActive()
 * @method Quote setIsActive(int $value)
 * @method Quote setIsVirtual(int $value)
 * @method int getIsMultiShipping()
 * @method Quote setIsMultiShipping(int $value)
 * @method int getItemsCount()
 * @method Quote setItemsCount(int $value)
 * @method float getItemsQty()
 * @method Quote setItemsQty(float $value)
 * @method int getOrigOrderId()
 * @method Quote setOrigOrderId(int $value)
 * @method float getStoreToBaseRate()
 * @method Quote setStoreToBaseRate(float $value)
 * @method float getStoreToQuoteRate()
 * @method Quote setStoreToQuoteRate(float $value)
 * @method string getBaseCurrencyCode()
 * @method Quote setBaseCurrencyCode(string $value)
 * @method string getStoreCurrencyCode()
 * @method Quote setStoreCurrencyCode(string $value)
 * @method string getQuoteCurrencyCode()
 * @method Quote setQuoteCurrencyCode(string $value)
 * @method float getGrandTotal()
 * @method Quote setGrandTotal(float $value)
 * @method float getBaseGrandTotal()
 * @method Quote setBaseGrandTotal(float $value)
 * @method Quote setCheckoutMethod(string $value)
 * @method int getCustomerId()
 * @method Quote setCustomerId(int $value)
 * @method Quote setCustomerTaxClassId(int $value)
 * @method Quote setCustomerGroupId(int $value)
 * @method string getCustomerEmail()
 * @method Quote setCustomerEmail(string $value)
 * @method string getCustomerPrefix()
 * @method Quote setCustomerPrefix(string $value)
 * @method string getCustomerFirstname()
 * @method Quote setCustomerFirstname(string $value)
 * @method string getCustomerMiddlename()
 * @method Quote setCustomerMiddlename(string $value)
 * @method string getCustomerLastname()
 * @method Quote setCustomerLastname(string $value)
 * @method string getCustomerSuffix()
 * @method Quote setCustomerSuffix(string $value)
 * @method string getCustomerDob()
 * @method Quote setCustomerDob(string $value)
 * @method string getCustomerNote()
 * @method Quote setCustomerNote(string $value)
 * @method int getCustomerNoteNotify()
 * @method Quote setCustomerNoteNotify(int $value)
 * @method int getCustomerIsGuest()
 * @method Quote setCustomerIsGuest(int $value)
 * @method string getRemoteIp()
 * @method Quote setRemoteIp(string $value)
 * @method string getAppliedRuleIds()
 * @method Quote setAppliedRuleIds(string $value)
 * @method string getReservedOrderId()
 * @method Quote setReservedOrderId(string $value)
 * @method string getPasswordHash()
 * @method Quote setPasswordHash(string $value)
 * @method string getCouponCode()
 * @method Quote setCouponCode(string $value)
 * @method string getGlobalCurrencyCode()
 * @method Quote setGlobalCurrencyCode(string $value)
 * @method float getBaseToGlobalRate()
 * @method Quote setBaseToGlobalRate(float $value)
 * @method float getBaseToQuoteRate()
 * @method Quote setBaseToQuoteRate(float $value)
 * @method string getCustomerTaxvat()
 * @method Quote setCustomerTaxvat(string $value)
 * @method string getCustomerGender()
 * @method Quote setCustomerGender(string $value)
 * @method float getSubtotal()
 * @method Quote setSubtotal(float $value)
 * @method float getBaseSubtotal()
 * @method Quote setBaseSubtotal(float $value)
 * @method float getSubtotalWithDiscount()
 * @method Quote setSubtotalWithDiscount(float $value)
 * @method float getBaseSubtotalWithDiscount()
 * @method Quote setBaseSubtotalWithDiscount(float $value)
 * @method int getIsChanged()
 * @method Quote setIsChanged(int $value)
 * @method int getTriggerRecollect()
 * @method Quote setTriggerRecollect(int $value)
 * @method string getExtShippingInfo()
 * @method Quote setExtShippingInfo(string $value)
 * @method int getGiftMessageId()
 * @method Quote setGiftMessageId(int $value)
 * @method bool|null getIsPersistent()
 * @method Quote setIsPersistent(bool $value)
 * @method Quote setSharedStoreIds(array $values)
 * @method Quote setWebsite($value)
 */
class Quote extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Checkout login method key
     */
    const CHECKOUT_METHOD_LOGIN_IN = 'login_in';

    /**
     * @var string
     */
    protected $_eventPrefix = 'sales_quote';

    /**
     * @var string
     */
    protected $_eventObject = 'quote';

    /**
     * Quote customer model object
     *
     * @var \Magento\Customer\Model\Customer
     */
    protected $_customer;

    /**
     * Quote addresses collection
     *
     * @var \Magento\Eav\Model\Entity\Collection\AbstractCollection
     */
    protected $_addresses;

    /**
     * Quote items collection
     *
     * @var \Magento\Eav\Model\Entity\Collection\AbstractCollection
     */
    protected $_items;

    /**
     * Quote payments
     *
     * @var \Magento\Eav\Model\Entity\Collection\AbstractCollection
     */
    protected $_payments;

    /**
     * @var \Magento\Sales\Model\Quote\Payment
     */
    protected $_currentPayment;

    /**
     * Different groups of error infos
     *
     * @var array
     */
    protected $_errorInfoGroups = [];

    /**
     * Whether quote should not be saved
     *
     * @var bool
     */
    protected $_preventSaving = false;

    /**
     * Catalog product
     *
     * @var \Magento\Catalog\Helper\Product
     */
    protected $_catalogProduct;

    /**
     * Sales data
     *
     * @var \Magento\Sales\Helper\Data
     */
    protected $_salesData;

    /**
     * Core store config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_config;

    /**
     * @var \Magento\Sales\Model\Quote\AddressFactory
     */
    protected $_quoteAddressFactory;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $_customerFactory;

    /**
     * Group repository
     *
     * @var \Magento\Customer\Api\GroupRepositoryInterface
     */
    protected $groupRepository;

    /**
     * @var \Magento\Sales\Model\Resource\Quote\Item\CollectionFactory
     */
    protected $_quoteItemCollectionFactory;

    /**
     * @var \Magento\Sales\Model\Quote\ItemFactory
     */
    protected $_quoteItemFactory;

    /**
     * @var \Magento\Framework\Message\Factory
     */
    protected $messageFactory;

    /**
     * @var \Magento\Sales\Model\Status\ListFactory
     */
    protected $_statusListFactory;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \Magento\Sales\Model\Quote\PaymentFactory
     */
    protected $_quotePaymentFactory;

    /**
     * @var \Magento\Sales\Model\Resource\Quote\Payment\CollectionFactory
     */
    protected $_quotePaymentCollectionFactory;

    /**
     * @var \Magento\Framework\Object\Copy
     */
    protected $_objectCopyService;

    /**
     * Address repository
     *
     * @var \Magento\Customer\Api\AddressRepositoryInterface
     */
    protected $addressRepository;

    /**
     * Search criteria builder
     *
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $criteriaBuilder;

    /**
     * Filter builder
     *
     * @var \Magento\Framework\Api\FilterBuilder
     */
    protected $filterBuilder;

    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface
     */
    protected $stockRegistry;

    /**
     * @var \Magento\Sales\Model\Quote\Item\Processor
     */
    protected $itemProcessor;

    /**
     * @var \Magento\Framework\Object\Factory
     */
    protected $objectFactory;

    /**
     * @var \Magento\Framework\Api\ExtensibleDataObjectConverter
     */
    protected $extensibleDataObjectConverter;

    /**
     * @var \Magento\Customer\Api\Data\AddressDataBuilder
     */
    protected $addressBuilder;

    /**
     * @var \Magento\Customer\Api\Data\CustomerDataBuilder
     */
    protected $customerBuilder;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Sales\Helper\Data $salesData
     * @param \Magento\Catalog\Helper\Product $catalogProduct
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param Quote\AddressFactory $quoteAddressFactory
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Customer\Api\GroupRepositoryInterface $groupRepository
     * @param Resource\Quote\Item\CollectionFactory $quoteItemCollectionFactory
     * @param Quote\ItemFactory $quoteItemFactory
     * @param \Magento\Framework\Message\Factory $messageFactory
     * @param Status\ListFactory $statusListFactory
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param Quote\PaymentFactory $quotePaymentFactory
     * @param Resource\Quote\Payment\CollectionFactory $quotePaymentCollectionFactory
     * @param \Magento\Framework\Object\Copy $objectCopyService
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     * @param Quote\Item\Processor $itemProcessor
     * @param \Magento\Framework\Object\Factory $objectFactory
     * @param \Magento\Customer\Api\AddressRepositoryInterface $addressRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $criteriaBuilder
     * @param \Magento\Framework\Api\FilterBuilder $filterBuilder
     * @param \Magento\Customer\Api\Data\AddressDataBuilder $addressBuilder
     * @param \Magento\Customer\Api\Data\CustomerDataBuilder $customerBuilder
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Framework\Api\ExtensibleDataObjectConverter $extensibleDataObjectConverter
     * @param \Magento\Framework\Model\Resource\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Sales\Helper\Data $salesData,
        \Magento\Catalog\Helper\Product $catalogProduct,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Sales\Model\Quote\AddressFactory $quoteAddressFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Customer\Api\GroupRepositoryInterface $groupRepository,
        \Magento\Sales\Model\Resource\Quote\Item\CollectionFactory $quoteItemCollectionFactory,
        \Magento\Sales\Model\Quote\ItemFactory $quoteItemFactory,
        \Magento\Framework\Message\Factory $messageFactory,
        \Magento\Sales\Model\Status\ListFactory $statusListFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Sales\Model\Quote\PaymentFactory $quotePaymentFactory,
        \Magento\Sales\Model\Resource\Quote\Payment\CollectionFactory $quotePaymentCollectionFactory,
        \Magento\Framework\Object\Copy $objectCopyService,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\Sales\Model\Quote\Item\Processor $itemProcessor,
        \Magento\Framework\Object\Factory $objectFactory,
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $criteriaBuilder,
        \Magento\Framework\Api\FilterBuilder $filterBuilder,
        \Magento\Customer\Api\Data\AddressDataBuilder $addressBuilder,
        \Magento\Customer\Api\Data\CustomerDataBuilder $customerBuilder,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Framework\Api\ExtensibleDataObjectConverter $extensibleDataObjectConverter,
        \Magento\Framework\Model\Resource\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\Db $resourceCollection = null,
        array $data = []
    ) {
        $this->_salesData = $salesData;
        $this->_catalogProduct = $catalogProduct;
        $this->_scopeConfig = $scopeConfig;
        $this->_storeManager = $storeManager;
        $this->_config = $config;
        $this->_quoteAddressFactory = $quoteAddressFactory;
        $this->_customerFactory = $customerFactory;
        $this->groupRepository = $groupRepository;
        $this->_quoteItemCollectionFactory = $quoteItemCollectionFactory;
        $this->_quoteItemFactory = $quoteItemFactory;
        $this->messageFactory = $messageFactory;
        $this->_statusListFactory = $statusListFactory;
        $this->productRepository = $productRepository;
        $this->_quotePaymentFactory = $quotePaymentFactory;
        $this->_quotePaymentCollectionFactory = $quotePaymentCollectionFactory;
        $this->_objectCopyService = $objectCopyService;
        $this->addressRepository = $addressRepository;
        $this->criteriaBuilder = $criteriaBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->stockRegistry = $stockRegistry;
        $this->itemProcessor = $itemProcessor;
        $this->objectFactory = $objectFactory;
        $this->addressBuilder = $addressBuilder;
        $this->customerBuilder = $customerBuilder;
        $this->customerRepository = $customerRepository;
        $this->extensibleDataObjectConverter = $extensibleDataObjectConverter;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Init resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\Sales\Model\Resource\Quote');
    }

    /**
     * Get quote store identifier
     *
     * @return int
     */
    public function getStoreId()
    {
        if (!$this->hasStoreId()) {
            return $this->_storeManager->getStore()->getId();
        }
        return $this->_getData('store_id');
    }

    /**
     * Get quote store model object
     *
     * @return  \Magento\Store\Model\Store
     */
    public function getStore()
    {
        return $this->_storeManager->getStore($this->getStoreId());
    }

    /**
     * Declare quote store model
     *
     * @param \Magento\Store\Model\Store $store
     * @return $this
     */
    public function setStore(\Magento\Store\Model\Store $store)
    {
        $this->setStoreId($store->getId());
        return $this;
    }

    /**
     * Get all available store ids for quote
     *
     * @return array
     */
    public function getSharedStoreIds()
    {
        $ids = $this->_getData('shared_store_ids');
        if (is_null($ids) || !is_array($ids)) {
            $website = $this->getWebsite();
            if ($website) {
                return $website->getStoreIds();
            }
            return $this->getStore()->getWebsite()->getStoreIds();
        }
        return $ids;
    }

    /**
     * Prepare data before save
     *
     * @return $this
     */
    public function beforeSave()
    {
        /**
         * Currency logic
         *
         * global - currency which is set for default in backend
         * base - currency which is set for current website. all attributes that
         *      have 'base_' prefix saved in this currency
         * quote/order - currency which was selected by customer or configured by
         *      admin for current store. currency in which customer sees
         *      price thought all checkout.
         *
         * Rates:
         *      base_to_global & base_to_quote/base_to_order
         */
        $globalCurrencyCode = $this->_config->getValue(
            \Magento\Directory\Model\Currency::XML_PATH_CURRENCY_BASE,
            'default'
        );
        $baseCurrency = $this->getStore()->getBaseCurrency();

        if ($this->hasForcedCurrency()) {
            $quoteCurrency = $this->getForcedCurrency();
        } else {
            $quoteCurrency = $this->getStore()->getCurrentCurrency();
        }

        $this->setGlobalCurrencyCode($globalCurrencyCode);
        $this->setBaseCurrencyCode($baseCurrency->getCode());
        $this->setStoreCurrencyCode($baseCurrency->getCode());
        $this->setQuoteCurrencyCode($quoteCurrency->getCode());

        $this->setBaseToGlobalRate($baseCurrency->getRate($globalCurrencyCode));
        $this->setBaseToQuoteRate($baseCurrency->getRate($quoteCurrency));

        if (!$this->hasChangedFlag() || $this->getChangedFlag() == true) {
            $this->setIsChanged(1);
        } else {
            $this->setIsChanged(0);
        }

        if ($this->_customer) {
            $this->setCustomerId($this->_customer->getId());
        }

        parent::beforeSave();
    }

    /**
     * Save related items
     *
     * @return $this
     */
    public function afterSave()
    {
        parent::afterSave();

        if (null !== $this->_addresses) {
            $this->getAddressesCollection()->save();
        }

        if (null !== $this->_items) {
            $this->getItemsCollection()->save();
        }

        if (null !== $this->_payments) {
            $this->getPaymentsCollection()->save();
        }

        if (null !== $this->_currentPayment) {
            $this->getPayment()->save();
        }
        return $this;
    }

    /**
     * Loading quote data by customer
     *
     * @param \Magento\Customer\Model\Customer|int $customer
     * @return $this
     */
    public function loadByCustomer($customer)
    {
        /* @TODO: remove this if after external usages of loadByCustomerId are refactored in MAGETWO-19935 */
        if ($customer instanceof \Magento\Customer\Model\Customer || $customer instanceof CustomerInterface) {
            $customerId = $customer->getId();
        } else {
            $customerId = (int)$customer;
        }
        $this->_getResource()->loadByCustomerId($this, $customerId);
        $this->_afterLoad();
        return $this;
    }

    /**
     * Loading only active quote
     *
     * @param int $quoteId
     * @return $this
     */
    public function loadActive($quoteId)
    {
        $this->_getResource()->loadActive($this, $quoteId);
        $this->_afterLoad();
        return $this;
    }

    /**
     * Loading quote by identifier
     *
     * @param int $quoteId
     * @return $this
     */
    public function loadByIdWithoutStore($quoteId)
    {
        $this->_getResource()->loadByIdWithoutStore($this, $quoteId);
        $this->_afterLoad();
        return $this;
    }

    /**
     * Assign customer model object data to quote
     *
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     * @return $this
     */
    public function assignCustomer(\Magento\Customer\Api\Data\CustomerInterface $customer)
    {
        return $this->assignCustomerWithAddressChange($customer);
    }

    /**
     * Assign customer model to quote with billing and shipping address change
     *
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     * @param Address $billingAddress Quote billing address
     * @param Address $shippingAddress Quote shipping address
     * @return $this
     */
    public function assignCustomerWithAddressChange(
        \Magento\Customer\Api\Data\CustomerInterface $customer,
        Address $billingAddress = null,
        Address $shippingAddress = null
    ) {
        if ($customer->getId()) {
            $this->setCustomer($customer);

            if (null !== $billingAddress) {
                $this->setBillingAddress($billingAddress);
            } else {
                try {
                    $defaultBillingAddress = $this->addressRepository->getById($customer->getDefaultBilling());
                } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                    //
                }
                if (isset($defaultBillingAddress)) {
                    /** @var \Magento\Sales\Model\Quote\Address $billingAddress */
                    $billingAddress = $this->_quoteAddressFactory->create();
                    $billingAddress->importCustomerAddressData($defaultBillingAddress);
                    $this->setBillingAddress($billingAddress);
                }
            }

            if (null === $shippingAddress) {
                try {
                    $defaultShippingAddress = $this->addressRepository->getById($customer->getDefaultShipping());
                } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                    //
                }
                if (isset($defaultShippingAddress)) {
                    /** @var \Magento\Sales\Model\Quote\Address $shippingAddress */
                    $shippingAddress = $this->_quoteAddressFactory->create();
                    $shippingAddress->importCustomerAddressData($defaultShippingAddress);
                } else {
                    $shippingAddress = $this->_quoteAddressFactory->create();
                }
            }
            $this->setShippingAddress($shippingAddress);
        }

        return $this;
    }

    /**
     * Define customer object
     *
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     * @return $this
     */
    public function setCustomer(\Magento\Customer\Api\Data\CustomerInterface $customer)
    {
        /* @TODO: Remove the method after all external usages are refactored in MAGETWO-19930 */
        $this->_customer = $customer;
        $this->setCustomerId($customer->getId());
        $customerData = $this->objectFactory->create(
            $this->extensibleDataObjectConverter->toFlatArray(
                $this->customerBuilder->populate($customer)->setAddresses([])->create(),
                [],
                '\Magento\Customer\Api\Data\CustomerInterface'
            )
        );
        $this->_objectCopyService->copyFieldsetToTarget('customer_account', 'to_quote', $customerData, $this);

        return $this;
    }

    /**
     * Retrieve customer model object
     *
     * @return \Magento\Customer\Api\Data\CustomerInterface|\Magento\Framework\Api\ExtensibleDataInterface
     */
    public function getCustomer()
    {
        /**
         * @TODO: Remove the method after all external usages are refactored in MAGETWO-19930
         * _customer and _customerFactory variables should be eliminated as well
         */
        if (null === $this->_customer) {
            try {
                $this->_customer = $this->customerRepository->getById($this->getCustomerId());
            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                $this->_customer = $this->customerBuilder->setId(null)->create();
            }
        }

        return $this->_customer;
    }

    /**
     * Substitute customer addresses
     *
     * @param \Magento\Customer\Api\Data\AddressInterface[] $addresses
     * @return $this
     */
    public function setCustomerAddressData(array $addresses)
    {
        foreach ($addresses as $address) {
            if (!$address->getId()) {
                $this->addCustomerAddress($address);
            }
        }

        return $this;
    }

    /**
     * Add address to the customer, created out of a Data Object
     *
     * TODO refactor in scope of MAGETWO-19930
     *
     * @param \Magento\Customer\Api\Data\AddressInterface $address
     * @return $this
     */
    public function addCustomerAddress(\Magento\Customer\Api\Data\AddressInterface $address)
    {
        $addresses = (array)$this->getCustomer()->getAddresses();
        $addresses[] = $address;
        $customer = $this->customerBuilder->populate($this->getCustomer())
            ->setAddresses($addresses)
            ->create();
        $this->setCustomer($customer);
        return $this;
    }

    /**
     * Update customer data object
     *
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     * @return $this
     */
    public function updateCustomerData(\Magento\Customer\Api\Data\CustomerInterface $customer)
    {
        $customer = $this->customerBuilder->mergeDataObjects($this->getCustomer(), $customer)
            ->create();
        $this->setCustomer($customer);
        return $this;
    }

    /**
     * Retrieve customer group id
     *
     * @return int
     */
    public function getCustomerGroupId()
    {
        if ($this->hasData('customer_group_id')) {
            return $this->getData('customer_group_id');
        } elseif ($this->getCustomerId()) {
            return $this->getCustomer()->getGroupId();
        } else {
            return GroupInterface::NOT_LOGGED_IN_ID;
        }
    }

    /**
     * Get customer tax class ID.
     *
     * @return string
     */
    public function getCustomerTaxClassId()
    {
        /**
         * tax class can vary at any time. so instead of using the value from session,
         * we need to retrieve from db every time to get the correct tax class
         */
        //if (!$this->getData('customer_group_id') && !$this->getData('customer_tax_class_id')) {
        $groupId = $this->getCustomerGroupId();
        if (!is_null($groupId)) {
            $taxClassId = $this->groupRepository->getById($this->getCustomerGroupId())->getTaxClassId();
            $this->setCustomerTaxClassId($taxClassId);
        }

        return $this->getData('customer_tax_class_id');
    }

    /**
     * Retrieve quote address collection
     *
     * @return \Magento\Eav\Model\Entity\Collection\AbstractCollection
     */
    public function getAddressesCollection()
    {
        if (null === $this->_addresses) {
            $this->_addresses = $this->_quoteAddressFactory->create()->getCollection()->setQuoteFilter($this->getId());

            if ($this->getId()) {
                foreach ($this->_addresses as $address) {
                    $address->setQuote($this);
                }
            }
        }
        return $this->_addresses;
    }

    /**
     * Retrieve quote address by type
     *
     * @param   string $type
     * @return  Address
     */
    protected function _getAddressByType($type)
    {
        foreach ($this->getAddressesCollection() as $address) {
            if ($address->getAddressType() == $type && !$address->isDeleted()) {
                return $address;
            }
        }

        $address = $this->_quoteAddressFactory->create()->setAddressType($type);
        $this->addAddress($address);
        return $address;
    }

    /**
     * Retrieve quote billing address
     *
     * @return Address
     */
    public function getBillingAddress()
    {
        return $this->_getAddressByType(Address::TYPE_BILLING);
    }

    /**
     * Retrieve quote shipping address
     *
     * @return Address
     */
    public function getShippingAddress()
    {
        return $this->_getAddressByType(Address::TYPE_SHIPPING);
    }

    /**
     * @return array
     */
    public function getAllShippingAddresses()
    {
        $addresses = [];
        foreach ($this->getAddressesCollection() as $address) {
            if ($address->getAddressType() == Address::TYPE_SHIPPING && !$address->isDeleted()) {
                $addresses[] = $address;
            }
        }
        return $addresses;
    }

    /**
     * Get all quote addresses
     *
     * @return \Magento\Sales\Model\Quote\Address[]
     */
    public function getAllAddresses()
    {
        $addresses = [];
        foreach ($this->getAddressesCollection() as $address) {
            if (!$address->isDeleted()) {
                $addresses[] = $address;
            }
        }
        return $addresses;
    }

    /**
     *
     * @param int $addressId
     * @return Address|false
     */
    public function getAddressById($addressId)
    {
        foreach ($this->getAddressesCollection() as $address) {
            if ($address->getId() == $addressId) {
                return $address;
            }
        }
        return false;
    }

    /**
     * @param int|string $addressId
     * @return Address|false
     */
    public function getAddressByCustomerAddressId($addressId)
    {
        foreach ($this->getAddressesCollection() as $address) {
            if (!$address->isDeleted() && $address->getCustomerAddressId() == $addressId) {
                return $address;
            }
        }
        return false;
    }

    /**
     * Get quote address by customer address ID.
     *
     * @param int|string $addressId
     * @return Address|false
     */
    public function getShippingAddressByCustomerAddressId($addressId)
    {
        /** @var \Magento\Sales\Model\Quote\Address $address */
        foreach ($this->getAddressesCollection() as $address) {
            if (!$address->isDeleted() &&
                $address->getAddressType() == Address::TYPE_SHIPPING &&
                $address->getCustomerAddressId() == $addressId
            ) {
                return $address;
            }
        }
        return false;
    }

    /**
     * @param int|string $addressId
     * @return $this
     */
    public function removeAddress($addressId)
    {
        foreach ($this->getAddressesCollection() as $address) {
            if ($address->getId() == $addressId) {
                $address->isDeleted(true);
                break;
            }
        }
        return $this;
    }

    /**
     * Leave no more than one billing and one shipping address, fill them with default data
     *
     * @return $this
     */
    public function removeAllAddresses()
    {
        $addressByType = [];
        $addressesCollection = $this->getAddressesCollection();

        // mark all addresses as deleted
        foreach ($addressesCollection as $address) {
            $type = $address->getAddressType();
            if (!isset($addressByType[$type]) || $addressByType[$type]->getId() > $address->getId()) {
                $addressByType[$type] = $address;
            }
            $address->isDeleted(true);
        }

        // create new billing and shipping addresses filled with default values, set this data to existing records
        foreach ($addressByType as $type => $address) {
            $id = $address->getId();
            $emptyAddress = $this->_getAddressByType($type);
            $address->setData($emptyAddress->getData())->setId($id)->isDeleted(false);
            $emptyAddress->setDeleteImmediately(true);
        }

        // remove newly created billing and shipping addresses from collection to avoid senseless delete queries
        foreach ($addressesCollection as $key => $item) {
            if ($item->getDeleteImmediately()) {
                $addressesCollection->removeItemByKey($key);
            }
        }

        return $this;
    }

    /**
     * @param Address $address
     * @return $this
     */
    public function addAddress(Address $address)
    {
        $address->setQuote($this);
        if (!$address->getId()) {
            $this->getAddressesCollection()->addItem($address);
        }
        return $this;
    }

    /**
     * @param Address $address
     * @return $this
     */
    public function setBillingAddress(Address $address)
    {
        $old = $this->getBillingAddress();

        if (!empty($old)) {
            $old->addData($address->getData());
        } else {
            $this->addAddress($address->setAddressType(Address::TYPE_BILLING));
        }
        return $this;
    }

    /**
     * Set shipping address
     *
     * @param Address $address
     * @return $this
     */
    public function setShippingAddress(Address $address)
    {
        if ($this->getIsMultiShipping()) {
            $this->addAddress($address->setAddressType(Address::TYPE_SHIPPING));
        } else {
            $old = $this->getShippingAddress();
            if (!empty($old)) {
                $old->addData($address->getData());
            } else {
                $this->addAddress($address->setAddressType(Address::TYPE_SHIPPING));
            }
        }
        return $this;
    }

    /**
     * @param Address $address
     * @return $this
     */
    public function addShippingAddress(Address $address)
    {
        $this->setShippingAddress($address);
        return $this;
    }

    /**
     * Retrieve quote items collection
     *
     * @param bool $useCache
     * @return  \Magento\Eav\Model\Entity\Collection\AbstractCollection
     */
    public function getItemsCollection($useCache = true)
    {
        if ($this->hasItemsCollection()) {
            return $this->getData('items_collection');
        }
        if (null === $this->_items) {
            $this->_items = $this->_quoteItemCollectionFactory->create();
            $this->_items->setQuote($this);
        }
        return $this->_items;
    }

    /**
     * Retrieve quote items array
     *
     * @return array
     */
    public function getAllItems()
    {
        $items = [];
        foreach ($this->getItemsCollection() as $item) {
            if (!$item->isDeleted()) {
                $items[] = $item;
            }
        }
        return $items;
    }

    /**
     * Get array of all items what can be display directly
     *
     * @return \Magento\Sales\Model\Quote\Item[]
     */
    public function getAllVisibleItems()
    {
        $items = [];
        foreach ($this->getItemsCollection() as $item) {
            if (!$item->isDeleted() && !$item->getParentItemId()) {
                $items[] = $item;
            }
        }
        return $items;
    }

    /**
     * Checking items availability
     *
     * @return bool
     */
    public function hasItems()
    {
        return sizeof($this->getAllItems()) > 0;
    }

    /**
     * Checking availability of items with decimal qty
     *
     * @return bool
     */
    public function hasItemsWithDecimalQty()
    {
        foreach ($this->getAllItems() as $item) {
            $stockItemDo = $this->stockRegistry->getStockItem($item->getProduct()->getId(), $item->getStore()->getWebsiteId());
            if ($stockItemDo->getItemId() && $stockItemDo->getIsQtyDecimal()) {
                return true;
            }
        }
        return false;
    }

    /**
     * Checking product exist in Quote
     *
     * @param int $productId
     * @return bool
     */
    public function hasProductId($productId)
    {
        foreach ($this->getAllItems() as $item) {
            if ($item->getProductId() == $productId) {
                return true;
            }
        }

        return false;
    }

    /**
     * Retrieve item model object by item identifier
     *
     * @param   int $itemId
     * @return  \Magento\Sales\Model\Quote\Item
     */
    public function getItemById($itemId)
    {
        return $this->getItemsCollection()->getItemById($itemId);
    }

    /**
     * Delete quote item. If it does not have identifier then it will be only removed from collection
     *
     * @param   \Magento\Sales\Model\Quote\Item $item
     * @return $this
     */
    public function deleteItem(\Magento\Sales\Model\Quote\Item $item)
    {
        if ($item->getId()) {
            $this->removeItem($item->getId());
        } else {
            $quoteItems = $this->getItemsCollection();
            $items = [$item];
            if ($item->getHasChildren()) {
                foreach ($item->getChildren() as $child) {
                    $items[] = $child;
                }
            }
            foreach ($quoteItems as $key => $quoteItem) {
                foreach ($items as $item) {
                    if ($quoteItem->compare($item)) {
                        $quoteItems->removeItemByKey($key);
                    }
                }
            }
        }

        return $this;
    }

    /**
     * Remove quote item by item identifier
     *
     * @param   int $itemId
     * @return $this
     */
    public function removeItem($itemId)
    {
        $item = $this->getItemById($itemId);

        if ($item) {
            $item->setQuote($this);
            /**
             * If we remove item from quote - we can't use multishipping mode
             */
            $this->setIsMultiShipping(false);
            $item->isDeleted(true);
            if ($item->getHasChildren()) {
                foreach ($item->getChildren() as $child) {
                    $child->isDeleted(true);
                }
            }

            $parent = $item->getParentItem();
            if ($parent) {
                $parent->isDeleted(true);
            }

            $this->_eventManager->dispatch('sales_quote_remove_item', ['quote_item' => $item]);
        }

        return $this;
    }

    /**
     * Mark all quote items as deleted (empty quote)
     *
     * @return $this
     */
    public function removeAllItems()
    {
        foreach ($this->getItemsCollection() as $itemId => $item) {
            if (is_null($item->getId())) {
                $this->getItemsCollection()->removeItemByKey($itemId);
            } else {
                $item->isDeleted(true);
            }
        }
        return $this;
    }

    /**
     * Adding new item to quote
     *
     * @param   \Magento\Sales\Model\Quote\Item $item
     * @return $this
     * @throws \Magento\Framework\Model\Exception
     */
    public function addItem(\Magento\Sales\Model\Quote\Item $item)
    {
        $item->setQuote($this);
        if (!$item->getId()) {
            $this->getItemsCollection()->addItem($item);
            $this->_eventManager->dispatch('sales_quote_add_item', ['quote_item' => $item]);
        }
        return $this;
    }

    /**
     * Advanced func to add product to quote - processing mode can be specified there.
     * Returns error message if product type instance can't prepare product.
     *
     * @param mixed $product
     * @param null|float|\Magento\Framework\Object $request
     * @param null|string $processMode
     * @return \Magento\Sales\Model\Quote\Item|string
     * @throws \Magento\Framework\Model\Exception
     */
    public function addProduct(
        \Magento\Catalog\Model\Product $product,
        $request = null,
        $processMode = \Magento\Catalog\Model\Product\Type\AbstractType::PROCESS_MODE_FULL
    ) {
        if ($request === null) {
            $request = 1;
        }
        if (is_numeric($request)) {
            $request = $this->objectFactory->create(['qty' => $request]);
        }
        if (!$request instanceof \Magento\Framework\Object) {
            throw new \Magento\Framework\Model\Exception(
                __('We found an invalid request for adding product to quote.')
            );
        }

        $cartCandidates = $product->getTypeInstance()->prepareForCartAdvanced($request, $product, $processMode);

        /**
         * Error message
         */
        if (is_string($cartCandidates)) {
            return $cartCandidates;
        }

        /**
         * If prepare process return one object
         */
        if (!is_array($cartCandidates)) {
            $cartCandidates = [$cartCandidates];
        }

        $parentItem = null;
        $errors = [];
        $item = null;
        $items = [];
        foreach ($cartCandidates as $candidate) {
            // Child items can be sticked together only within their parent
            $stickWithinParent = $candidate->getParentProductId() ? $parentItem : null;
            $candidate->setStickWithinParent($stickWithinParent);

            $item = $this->getItemByProduct($candidate);
            if (!$item) {
                $item = $this->itemProcessor->init($candidate, $request);
                // Add only item that is not in quote already
                $this->addItem($item);
            }
            $items[] = $item;

            /**
             * As parent item we should always use the item of first added product
             */
            if (!$parentItem) {
                $parentItem = $item;
            }
            if ($parentItem && $candidate->getParentProductId() && !$item->getParentItem()) {
                $item->setParentItem($parentItem);
            }

            $this->itemProcessor->prepare($item, $request, $candidate);

            // collect errors instead of throwing first one
            if ($item->getHasError()) {
                $message = $item->getMessage();
                if (!in_array($message, $errors)) {
                    // filter duplicate messages
                    $errors[] = $message;
                }
            }
        }
        if (!empty($errors)) {
            throw new \Magento\Framework\Model\Exception(implode("\n", $errors));
        }

        $this->_eventManager->dispatch('sales_quote_product_add_after', ['items' => $items]);

        return $item;
    }

    /**
     * Adding catalog product object data to quote
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param int $qty
     * @return \Magento\Sales\Model\Quote\Item
     */
    protected function _addCatalogProduct(\Magento\Catalog\Model\Product $product, $qty = 1)
    {
        $newItem = false;
        $item = $this->getItemByProduct($product);
        if (!$item) {
            $item = $this->_quoteItemFactory->create();
            $item->setQuote($this);
            if ($this->_appState->getAreaCode() === \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE) {
                $item->setStoreId($this->getStore()->getId());
            } else {
                $item->setStoreId($this->_storeManager->getStore()->getId());
            }
            $newItem = true;
        }

        /**
         * We can't modify existing child items
         */
        if ($item->getId() && $product->getParentProductId()) {
            return $item;
        }

        $item->setOptions($product->getCustomOptions())->setProduct($product);

        // Add only item that is not in quote already (there can be other new or already saved item
        if ($newItem) {
            $this->addItem($item);
        }

        return $item;
    }

    /**
     * Updates quote item with new configuration
     *
     * $params sets how current item configuration must be taken into account and additional options.
     * It's passed to \Magento\Catalog\Helper\Product->addParamsToBuyRequest() to compose resulting buyRequest.
     *
     * Basically it can hold
     * - 'current_config', \Magento\Framework\Object or array - current buyRequest that configures product in this item,
     *   used to restore currently attached files
     * - 'files_prefix': string[a-z0-9_] - prefix that was added at frontend to names of file options (file inputs),
     *   so they won't intersect with other submitted options
     *
     * For more options see \Magento\Catalog\Helper\Product->addParamsToBuyRequest()
     *
     * @param int $itemId
     * @param \Magento\Framework\Object $buyRequest
     * @param null|array|\Magento\Framework\Object $params
     * @return \Magento\Sales\Model\Quote\Item
     * @throws \Magento\Framework\Model\Exception
     *
     * @see \Magento\Catalog\Helper\Product::addParamsToBuyRequest()
     */
    public function updateItem($itemId, $buyRequest, $params = null)
    {
        $item = $this->getItemById($itemId);
        if (!$item) {
            throw new \Magento\Framework\Model\Exception(
                __('This is the wrong quote item id to update configuration.')
            );
        }
        $productId = $item->getProduct()->getId();

        //We need to create new clear product instance with same $productId
        //to set new option values from $buyRequest
        $product = clone $this->productRepository->getById($productId, false, $this->getStore()->getId());

        if (!$params) {
            $params = new \Magento\Framework\Object();
        } elseif (is_array($params)) {
            $params = new \Magento\Framework\Object($params);
        }
        $params->setCurrentConfig($item->getBuyRequest());
        $buyRequest = $this->_catalogProduct->addParamsToBuyRequest($buyRequest, $params);

        $buyRequest->setResetCount(true);
        $resultItem = $this->addProduct($product, $buyRequest);

        if (is_string($resultItem)) {
            throw new \Magento\Framework\Model\Exception($resultItem);
        }

        if ($resultItem->getParentItem()) {
            $resultItem = $resultItem->getParentItem();
        }

        if ($resultItem->getId() != $itemId) {
            /**
             * Product configuration didn't stick to original quote item
             * It either has same configuration as some other quote item's product or completely new configuration
             */
            $this->removeItem($itemId);
            $items = $this->getAllItems();
            foreach ($items as $item) {
                if ($item->getProductId() == $productId && $item->getId() != $resultItem->getId()) {
                    if ($resultItem->compare($item)) {
                        // Product configuration is same as in other quote item
                        $resultItem->setQty($resultItem->getQty() + $item->getQty());
                        $this->removeItem($item->getId());
                        break;
                    }
                }
            }
        } else {
            $resultItem->setQty($buyRequest->getQty());
        }

        return $resultItem;
    }

    /**
     * Retrieve quote item by product id
     *
     * @param   \Magento\Catalog\Model\Product $product
     * @return  \Magento\Sales\Model\Quote\Item|bool
     */
    public function getItemByProduct($product)
    {
        foreach ($this->getAllItems() as $item) {
            if ($item->representProduct($product)) {
                return $item;
            }
        }
        return false;
    }

    /**
     * @return int
     */
    public function getItemsSummaryQty()
    {
        $qty = $this->getData('all_items_qty');
        if (null === $qty) {
            $qty = 0;
            foreach ($this->getAllItems() as $item) {
                if ($item->getParentItem()) {
                    continue;
                }

                $children = $item->getChildren();
                if ($children && $item->isShipSeparately()) {
                    foreach ($children as $child) {
                        $qty += $child->getQty() * $item->getQty();
                    }
                } else {
                    $qty += $item->getQty();
                }
            }
            $this->setData('all_items_qty', $qty);
        }
        return $qty;
    }

    /**
     * @return int
     */
    public function getItemVirtualQty()
    {
        $qty = $this->getData('virtual_items_qty');
        if (null === $qty) {
            $qty = 0;
            foreach ($this->getAllItems() as $item) {
                if ($item->getParentItem()) {
                    continue;
                }

                $children = $item->getChildren();
                if ($children && $item->isShipSeparately()) {
                    foreach ($children as $child) {
                        if ($child->getProduct()->getIsVirtual()) {
                            $qty += $child->getQty();
                        }
                    }
                } else {
                    if ($item->getProduct()->getIsVirtual()) {
                        $qty += $item->getQty();
                    }
                }
            }
            $this->setData('virtual_items_qty', $qty);
        }
        return $qty;
    }

    /*********************** PAYMENTS ***************************/
    /**
     * @return \Magento\Eav\Model\Entity\Collection\AbstractCollection
     */
    public function getPaymentsCollection()
    {
        if (null === $this->_payments) {
            $this->_payments = $this->_quotePaymentCollectionFactory->create()->setQuoteFilter($this->getId());

            if ($this->getId()) {
                foreach ($this->_payments as $payment) {
                    $payment->setQuote($this);
                }
            }
        }
        return $this->_payments;
    }

    /**
     * @return \Magento\Sales\Model\Quote\Payment
     */
    public function getPayment()
    {
        if (null === $this->_currentPayment || !$this->_currentPayment) {
            $this->_currentPayment = $this->_quotePaymentCollectionFactory->create()
                ->setQuoteFilter($this->getId())
                ->getFirstItem();
        }
        if ($payment = $this->_currentPayment) {
            if ($this->getId()) {
                $payment->setQuote($this);
            }
            if (!$payment->isDeleted()) {
                return $payment;
            }
        }
        $payment = $this->_quotePaymentFactory->create();
        $this->addPayment($payment);
        return $payment;
    }

    /**
     * @param string $paymentId
     * @return bool
     */
    public function getPaymentById($paymentId)
    {
        foreach ($this->getPaymentsCollection() as $payment) {
            if ($payment->getId() == $paymentId) {
                return $payment;
            }
        }
        return false;
    }

    /**
     * @param \Magento\Sales\Model\Quote\Payment $payment
     * @return $this
     */
    public function addPayment(\Magento\Sales\Model\Quote\Payment $payment)
    {
        $payment->setQuote($this);
        if (!$payment->getId()) {
            $this->getPaymentsCollection()->addItem($payment);
        }
        return $this;
    }

    /**
     * @param \Magento\Sales\Model\Quote\Payment $payment
     * @return \Magento\Sales\Model\Quote\Payment
     */
    public function setPayment(\Magento\Sales\Model\Quote\Payment $payment)
    {
        if (!$this->getIsMultiPayment() && ($old = $this->getPayment())) {
            $payment->setId($old->getId());
        }
        $this->addPayment($payment);

        return $payment;
    }

    /**
     * @return $this
     */
    public function removePayment()
    {
        $this->getPayment()->isDeleted(true);
        return $this;
    }

    /**
     * Collect totals
     *
     * @return $this
     */
    public function collectTotals()
    {
        /**
         * Protect double totals collection
         */
        if ($this->getTotalsCollectedFlag()) {
            return $this;
        }
        $this->_eventManager->dispatch(
            $this->_eventPrefix . '_collect_totals_before',
            [$this->_eventObject => $this]
        );

        $this->_collectItemsQtys();

        $this->setSubtotal(0);
        $this->setBaseSubtotal(0);

        $this->setSubtotalWithDiscount(0);
        $this->setBaseSubtotalWithDiscount(0);

        $this->setGrandTotal(0);
        $this->setBaseGrandTotal(0);

        foreach ($this->getAllAddresses() as $address) {
            $address->setSubtotal(0);
            $address->setBaseSubtotal(0);

            $address->setGrandTotal(0);
            $address->setBaseGrandTotal(0);

            $address->collectTotals();

            $this->setSubtotal((float)$this->getSubtotal() + $address->getSubtotal());
            $this->setBaseSubtotal((float)$this->getBaseSubtotal() + $address->getBaseSubtotal());

            $this->setSubtotalWithDiscount(
                (float)$this->getSubtotalWithDiscount() + $address->getSubtotalWithDiscount()
            );
            $this->setBaseSubtotalWithDiscount(
                (float)$this->getBaseSubtotalWithDiscount() + $address->getBaseSubtotalWithDiscount()
            );

            $this->setGrandTotal((float)$this->getGrandTotal() + $address->getGrandTotal());
            $this->setBaseGrandTotal((float)$this->getBaseGrandTotal() + $address->getBaseGrandTotal());
        }

        $this->_salesData->checkQuoteAmount($this, $this->getGrandTotal());
        $this->_salesData->checkQuoteAmount($this, $this->getBaseGrandTotal());

        $this->setData('trigger_recollect', 0);
        $this->_validateCouponCode();

        $this->_eventManager->dispatch(
            $this->_eventPrefix . '_collect_totals_after',
            [$this->_eventObject => $this]
        );

        $this->setTotalsCollectedFlag(true);
        return $this;
    }

    /**
     * Collect items qty
     *
     * @return $this
     */
    protected function _collectItemsQtys()
    {
        $this->setItemsCount(0);
        $this->setItemsQty(0);
        $this->setVirtualItemsQty(0);

        foreach ($this->getAllVisibleItems() as $item) {
            if ($item->getParentItem()) {
                continue;
            }

            $children = $item->getChildren();
            if ($children && $item->isShipSeparately()) {
                foreach ($children as $child) {
                    if ($child->getProduct()->getIsVirtual()) {
                        $this->setVirtualItemsQty($this->getVirtualItemsQty() + $child->getQty() * $item->getQty());
                    }
                }
            }

            if ($item->getProduct()->getIsVirtual()) {
                $this->setVirtualItemsQty($this->getVirtualItemsQty() + $item->getQty());
            }
            $this->setItemsCount($this->getItemsCount() + 1);
            $this->setItemsQty((float)$this->getItemsQty() + $item->getQty());
        }

        return $this;
    }

    /**
     * Get all quote totals (sorted by priority)
     * Method process quote states isVirtual and isMultiShipping
     *
     * @return array
     */
    public function getTotals()
    {
        /**
         * If quote is virtual we are using totals of billing address because
         * all items assigned to it
         */
        if ($this->isVirtual()) {
            return $this->getBillingAddress()->getTotals();
        }

        $shippingAddress = $this->getShippingAddress();
        $totals = $shippingAddress->getTotals();
        // Going through all quote addresses and merge their totals
        foreach ($this->getAddressesCollection() as $address) {
            if ($address->isDeleted() || $address === $shippingAddress) {
                continue;
            }
            foreach ($address->getTotals() as $code => $total) {
                if (isset($totals[$code])) {
                    $totals[$code]->merge($total);
                } else {
                    $totals[$code] = $total;
                }
            }
        }

        $sortedTotals = [];
        foreach ($this->getBillingAddress()->getTotalCollector()->getRetrievers() as $total) {
            /* @var $total Address\Total\AbstractTotal */
            if (isset($totals[$total->getCode()])) {
                $sortedTotals[$total->getCode()] = $totals[$total->getCode()];
            }
        }
        return $sortedTotals;
    }

    /**
     * @param string $message
     * @param string $index
     * @return $this
     */
    public function addMessage($message, $index = 'error')
    {
        $messages = $this->getData('messages');
        if (null === $messages) {
            $messages = [];
        }

        if (isset($messages[$index])) {
            return $this;
        }

        if (is_string($message)) {
            $message = $this->messageFactory->create(\Magento\Framework\Message\MessageInterface::TYPE_ERROR, $message);
        }

        $messages[$index] = $message;
        $this->setData('messages', $messages);
        return $this;
    }

    /**
     * Retrieve current quote messages
     *
     * @return array
     */
    public function getMessages()
    {
        $messages = $this->getData('messages');
        if (null === $messages) {
            $messages = [];
            $this->setData('messages', $messages);
        }
        return $messages;
    }

    /**
     * Retrieve current quote errors
     *
     * @return array
     */
    public function getErrors()
    {
        $errors = [];
        foreach ($this->getMessages() as $message) {
            /* @var $error \Magento\Framework\Message\AbstractMessage */
            if ($message->getType() == \Magento\Framework\Message\MessageInterface::TYPE_ERROR) {
                array_push($errors, $message);
            }
        }
        return $errors;
    }

    /**
     * Sets flag, whether this quote has some error associated with it.
     *
     * @param bool $flag
     * @return $this
     */
    protected function _setHasError($flag)
    {
        return $this->setData('has_error', $flag);
    }

    /**
     * Sets flag, whether this quote has some error associated with it.
     * When TRUE - also adds 'unknown' error information to list of quote errors.
     * When FALSE - clears whole list of quote errors.
     * It's recommended to use addErrorInfo() instead - to be able to remove error statuses later.
     *
     * @param bool $flag
     * @return $this
     * @see addErrorInfo()
     */
    public function setHasError($flag)
    {
        if ($flag) {
            $this->addErrorInfo();
        } else {
            $this->_clearErrorInfo();
        }
        return $this;
    }

    /**
     * Clears list of errors, associated with this quote.
     * Also automatically removes error-flag from oneself.
     *
     * @return $this
     */
    protected function _clearErrorInfo()
    {
        $this->_errorInfoGroups = [];
        $this->_setHasError(false);
        return $this;
    }

    /**
     * Adds error information to the quote.
     * Automatically sets error flag.
     *
     * @param string $type An internal error type ('error', 'qty', etc.), passed then to adding messages routine
     * @param string|null $origin Usually a name of module, that embeds error
     * @param int|null $code Error code, unique for origin, that sets it
     * @param string|null $message Error message
     * @param \Magento\Framework\Object|null $additionalData Any additional data, that caller would like to store
     * @return $this
     */
    public function addErrorInfo(
        $type = 'error',
        $origin = null,
        $code = null,
        $message = null,
        $additionalData = null
    ) {
        if (!isset($this->_errorInfoGroups[$type])) {
            $this->_errorInfoGroups[$type] = $this->_statusListFactory->create();
        }

        $this->_errorInfoGroups[$type]->addItem($origin, $code, $message, $additionalData);

        if ($message !== null) {
            $this->addMessage($message, $type);
        }
        $this->_setHasError(true);

        return $this;
    }

    /**
     * Removes error infos, that have parameters equal to passed in $params.
     * $params can have following keys (if not set - then any item is good for this key):
     *   'origin', 'code', 'message'
     *
     * @param string $type An internal error type ('error', 'qty', etc.), passed then to adding messages routine
     * @param array $params
     * @return $this
     */
    public function removeErrorInfosByParams($type, $params)
    {
        if ($type && !isset($this->_errorInfoGroups[$type])) {
            return $this;
        }

        $errorLists = [];
        if ($type) {
            $errorLists[] = $this->_errorInfoGroups[$type];
        } else {
            $errorLists = $this->_errorInfoGroups;
        }

        foreach ($errorLists as $type => $errorList) {
            $removedItems = $errorList->removeItemsByParams($params);
            foreach ($removedItems as $item) {
                if ($item['message'] !== null) {
                    $this->removeMessageByText($type, $item['message']);
                }
            }
        }

        $errorsExist = false;
        foreach ($this->_errorInfoGroups as $errorListCheck) {
            if ($errorListCheck->getItems()) {
                $errorsExist = true;
                break;
            }
        }
        if (!$errorsExist) {
            $this->_setHasError(false);
        }

        return $this;
    }

    /**
     * Removes message by text
     *
     * @param string $type
     * @param string $text
     * @return $this
     */
    public function removeMessageByText($type, $text)
    {
        $messages = $this->getData('messages');
        if (null === $messages) {
            $messages = [];
        }

        if (!isset($messages[$type])) {
            return $this;
        }

        $message = $messages[$type];
        if ($message instanceof \Magento\Framework\Message\AbstractMessage) {
            $message = $message->getText();
        } elseif (!is_string($message)) {
            return $this;
        }
        if ($message == $text) {
            unset($messages[$type]);
            $this->setData('messages', $messages);
        }
        return $this;
    }

    /**
     * Generate new increment order id and associate it with current quote
     *
     * @return $this
     */
    public function reserveOrderId()
    {
        if (!$this->getReservedOrderId()) {
            $this->setReservedOrderId($this->_getResource()->getReservedOrderId($this));
        } else {
            //checking if reserved order id was already used for some order
            //if yes reserving new one if not using old one
            if ($this->_getResource()->isOrderIncrementIdUsed($this->getReservedOrderId())) {
                $this->setReservedOrderId($this->_getResource()->getReservedOrderId($this));
            }
        }
        return $this;
    }

    /**
     * @param bool $multishipping
     * @return bool
     */
    public function validateMinimumAmount($multishipping = false)
    {
        $storeId = $this->getStoreId();
        $minOrderActive = $this->_scopeConfig->isSetFlag(
            'sales/minimum_order/active',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
        if (!$minOrderActive) {
            return true;
        }
        $minOrderMulti = $this->_scopeConfig->isSetFlag(
            'sales/minimum_order/multi_address',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
        $minAmount = $this->_scopeConfig->getValue(
            'sales/minimum_order/amount',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
        $taxInclude = $this->_scopeConfig->getValue(
            'sales/minimum_order/tax_including',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );

        $addresses = $this->getAllAddresses();

        if (!$multishipping) {
            foreach ($addresses as $address) {
                /* @var $address Address */
                if (!$address->validateMinimumAmount()) {
                    return false;
                }
            }
            return true;
        }

        if (!$minOrderMulti) {
            foreach ($addresses as $address) {
                $taxes = ($taxInclude) ? $address->getBaseTaxAmount() : 0;
                foreach ($address->getQuote()->getItemsCollection() as $item) {
                    /** @var \Magento\Sales\Model\Quote\Item $item */
                    $amount = $item->getBaseRowTotal() - $item->getBaseDiscountAmount() + $taxes;
                    if ($amount < $minAmount) {
                        return false;
                    }
                }
            }
        } else {
            $baseTotal = 0;
            foreach ($addresses as $address) {
                $taxes = ($taxInclude) ? $address->getBaseTaxAmount() : 0;
                $baseTotal += $address->getBaseSubtotalWithDiscount() + $taxes;
            }
            if ($baseTotal < $minAmount) {
                return false;
            }
        }
        return true;
    }

    /**
     * Check quote for virtual product only
     *
     * @return bool
     */
    public function isVirtual()
    {
        $isVirtual = true;
        $countItems = 0;
        foreach ($this->getItemsCollection() as $_item) {
            /* @var $_item \Magento\Sales\Model\Quote\Item */
            if ($_item->isDeleted() || $_item->getParentItemId()) {
                continue;
            }
            $countItems++;
            if (!$_item->getProduct()->getIsVirtual()) {
                $isVirtual = false;
                break;
            }
        }
        return $countItems == 0 ? false : $isVirtual;
    }

    /**
     * Check quote for virtual product only
     *
     * @return bool
     */
    public function getIsVirtual()
    {
        return intval($this->isVirtual());
    }

    /**
     * Has a virtual products on quote
     *
     * @return bool
     */
    public function hasVirtualItems()
    {
        $hasVirtual = false;
        foreach ($this->getItemsCollection() as $item) {
            if ($item->getParentItemId()) {
                continue;
            }
            if ($item->getProduct()->isVirtual()) {
                $hasVirtual = true;
            }
        }
        return $hasVirtual;
    }

    /**
     * Merge quotes
     *
     * @param   Quote $quote
     * @return $this
     */
    public function merge(Quote $quote)
    {
        $this->_eventManager->dispatch(
            $this->_eventPrefix . '_merge_before',
            [$this->_eventObject => $this, 'source' => $quote]
        );

        foreach ($quote->getAllVisibleItems() as $item) {
            $found = false;
            foreach ($this->getAllItems() as $quoteItem) {
                if ($quoteItem->compare($item)) {
                    $quoteItem->setQty($quoteItem->getQty() + $item->getQty());
                    $found = true;
                    break;
                }
            }

            if (!$found) {
                $newItem = clone $item;
                $this->addItem($newItem);
                if ($item->getHasChildren()) {
                    foreach ($item->getChildren() as $child) {
                        $newChild = clone $child;
                        $newChild->setParentItem($newItem);
                        $this->addItem($newChild);
                    }
                }
            }
        }

        /**
         * Init shipping and billing address if quote is new
         */
        if (!$this->getId()) {
            $this->getShippingAddress();
            $this->getBillingAddress();
        }

        if ($quote->getCouponCode()) {
            $this->setCouponCode($quote->getCouponCode());
        }

        $this->_eventManager->dispatch(
            $this->_eventPrefix . '_merge_after',
            [$this->_eventObject => $this, 'source' => $quote]
        );

        return $this;
    }

    /**
     * @return $this
     */
    protected function _validateCouponCode()
    {
        $code = $this->_getData('coupon_code');
        if (strlen($code)) {
            $addressHasCoupon = false;
            $addresses = $this->getAllAddresses();
            if (count($addresses) > 0) {
                foreach ($addresses as $address) {
                    if ($address->hasCouponCode()) {
                        $addressHasCoupon = true;
                    }
                }
                if (!$addressHasCoupon) {
                    $this->setCouponCode('');
                }
            }
        }
        return $this;
    }

    /**
     * Trigger collect totals after loading, if required
     *
     * @return $this
     */
    protected function _afterLoad()
    {
        // collect totals and save me, if required
        if (1 == $this->getData('trigger_recollect')) {
            $this->collectTotals()->save();
        }
        return parent::_afterLoad();
    }

    /**
     * Return checkout method code
     *
     * @param boolean $originalMethod if true return defined method from beginning
     * @return string
     */
    public function getCheckoutMethod($originalMethod = false)
    {
        if ($this->getCustomerId() && !$originalMethod) {
            return self::CHECKOUT_METHOD_LOGIN_IN;
        }
        return $this->_getData('checkout_method');
    }

    /**
     * Prevent quote from saving
     *
     * @return $this
     */
    public function preventSaving()
    {
        $this->_preventSaving = true;
        return $this;
    }

    /**
     * Check if model can be saved
     *
     * @return bool
     */
    public function isPreventSaving()
    {
        return $this->_preventSaving;
    }

    /**
     * Check if there are more than one shipping address
     *
     * @return bool
     */
    public function isMultipleShippingAddresses()
    {
        return \count($this->getAllShippingAddresses()) > 1;
    }
}
