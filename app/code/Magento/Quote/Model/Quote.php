<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\Data\GroupInterface;
use Magento\Directory\Model\AllowedCountries;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Model\AbstractExtensibleModel;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Address\Total as AddressTotal;
use Magento\Sales\Model\Status;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\ObjectManager;

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
 * @api
 * @method int getIsMultiShipping()
 * @method Quote setIsMultiShipping(int $value)
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
 * @method int getCustomerId()
 * @method Quote setCustomerId(int $value)
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
 * @method string getRemoteIp()
 * @method Quote setRemoteIp(string $value)
 * @method string getAppliedRuleIds()
 * @method Quote setAppliedRuleIds(string $value)
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
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 100.0.2
 */
class Quote extends AbstractExtensibleModel implements \Magento\Quote\Api\Data\CartInterface
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
     * @var \Magento\Quote\Model\Quote\Payment
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
     * Quote validator
     *
     * @var \Magento\Quote\Model\QuoteValidator
     */
    protected $quoteValidator;

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
     * @var \Magento\Quote\Model\Quote\AddressFactory
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
     * @var \Magento\Quote\Model\ResourceModel\Quote\Item\CollectionFactory
     */
    protected $_quoteItemCollectionFactory;

    /**
     * @var \Magento\Quote\Model\Quote\ItemFactory
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
     * @var \Magento\Quote\Model\Quote\PaymentFactory
     */
    protected $_quotePaymentFactory;

    /**
     * @var \Magento\Quote\Model\ResourceModel\Quote\Payment\CollectionFactory
     */
    protected $_quotePaymentCollectionFactory;

    /**
     * @var \Magento\Framework\DataObject\Copy
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
    protected $searchCriteriaBuilder;

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
     * @var \Magento\Quote\Model\Quote\Item\Processor
     */
    protected $itemProcessor;

    /**
     * @var \Magento\Framework\DataObject\Factory
     */
    protected $objectFactory;

    /**
     * @var \Magento\Framework\Api\ExtensibleDataObjectConverter
     */
    protected $extensibleDataObjectConverter;

    /**
     * @var \Magento\Customer\Api\Data\AddressInterfaceFactory
     */
    protected $addressDataFactory;

    /**
     * @var \Magento\Customer\Api\Data\CustomerInterfaceFactory
     */
    protected $customerDataFactory;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var Cart\CurrencyFactory
     */
    protected $currencyFactory;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var JoinProcessorInterface
     */
    protected $extensionAttributesJoinProcessor;

    /**
     * @var \Magento\Quote\Model\Quote\TotalsCollector
     */
    protected $totalsCollector;

    /**
     * @var \Magento\Quote\Model\Quote\TotalsReader
     */
    protected $totalsReader;

    /**
     * @var \Magento\Quote\Model\ShippingFactory
     */
    protected $shippingFactory;

    /**
     * @var \Magento\Quote\Model\ShippingAssignmentFactory
     */
    protected $shippingAssignmentFactory;

    /**
     * Quote shipping addresses items cache
     *
     * @var array
     */
    protected $shippingAddressesItems;

    /**
     * @var \Magento\Sales\Model\OrderIncrementIdChecker
     */
    private $orderIncrementIdChecker;

    /**
     * @var AllowedCountries
     */
    private $allowedCountriesReader;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param QuoteValidator $quoteValidator
     * @param \Magento\Catalog\Helper\Product $catalogProduct
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param Quote\AddressFactory $quoteAddressFactory
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Customer\Api\GroupRepositoryInterface $groupRepository
     * @param \Magento\Quote\Model\ResourceModel\Quote\Item\CollectionFactory $quoteItemCollectionFactory
     * @param Quote\ItemFactory $quoteItemFactory
     * @param \Magento\Framework\Message\Factory $messageFactory
     * @param Status\ListFactory $statusListFactory
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param Quote\PaymentFactory $quotePaymentFactory
     * @param \Magento\Quote\Model\ResourceModel\Quote\Payment\CollectionFactory $quotePaymentCollectionFactory
     * @param \Magento\Framework\DataObject\Copy $objectCopyService
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     * @param Quote\Item\Processor $itemProcessor
     * @param \Magento\Framework\DataObject\Factory $objectFactory
     * @param \Magento\Customer\Api\AddressRepositoryInterface $addressRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $criteriaBuilder
     * @param \Magento\Framework\Api\FilterBuilder $filterBuilder
     * @param \Magento\Customer\Api\Data\AddressInterfaceFactory $addressDataFactory
     * @param \Magento\Customer\Api\Data\CustomerInterfaceFactory $customerDataFactory
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     * @param \Magento\Framework\Api\ExtensibleDataObjectConverter $extensibleDataObjectConverter
     * @param Cart\CurrencyFactory $currencyFactory
     * @param JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param Quote\TotalsCollector $totalsCollector
     * @param Quote\TotalsReader $totalsReader
     * @param ShippingFactory $shippingFactory
     * @param ShippingAssignmentFactory $shippingAssignmentFactory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     * @param \Magento\Sales\Model\OrderIncrementIdChecker|null $orderIncrementIdChecker
     * @param AllowedCountries|null $allowedCountriesReader
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        \Magento\Quote\Model\QuoteValidator $quoteValidator,
        \Magento\Catalog\Helper\Product $catalogProduct,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Quote\Model\Quote\AddressFactory $quoteAddressFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Customer\Api\GroupRepositoryInterface $groupRepository,
        \Magento\Quote\Model\ResourceModel\Quote\Item\CollectionFactory $quoteItemCollectionFactory,
        \Magento\Quote\Model\Quote\ItemFactory $quoteItemFactory,
        \Magento\Framework\Message\Factory $messageFactory,
        \Magento\Sales\Model\Status\ListFactory $statusListFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Quote\Model\Quote\PaymentFactory $quotePaymentFactory,
        \Magento\Quote\Model\ResourceModel\Quote\Payment\CollectionFactory $quotePaymentCollectionFactory,
        \Magento\Framework\DataObject\Copy $objectCopyService,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\Quote\Model\Quote\Item\Processor $itemProcessor,
        \Magento\Framework\DataObject\Factory $objectFactory,
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $criteriaBuilder,
        \Magento\Framework\Api\FilterBuilder $filterBuilder,
        \Magento\Customer\Api\Data\AddressInterfaceFactory $addressDataFactory,
        \Magento\Customer\Api\Data\CustomerInterfaceFactory $customerDataFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        \Magento\Framework\Api\ExtensibleDataObjectConverter $extensibleDataObjectConverter,
        \Magento\Quote\Model\Cart\CurrencyFactory $currencyFactory,
        JoinProcessorInterface $extensionAttributesJoinProcessor,
        Quote\TotalsCollector $totalsCollector,
        Quote\TotalsReader $totalsReader,
        \Magento\Quote\Model\ShippingFactory $shippingFactory,
        \Magento\Quote\Model\ShippingAssignmentFactory $shippingAssignmentFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = [],
        \Magento\Sales\Model\OrderIncrementIdChecker $orderIncrementIdChecker = null,
        AllowedCountries $allowedCountriesReader = null
    ) {
        $this->quoteValidator = $quoteValidator;
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
        $this->searchCriteriaBuilder = $criteriaBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->stockRegistry = $stockRegistry;
        $this->itemProcessor = $itemProcessor;
        $this->objectFactory = $objectFactory;
        $this->addressDataFactory = $addressDataFactory;
        $this->customerDataFactory = $customerDataFactory;
        $this->customerRepository = $customerRepository;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->extensibleDataObjectConverter = $extensibleDataObjectConverter;
        $this->currencyFactory = $currencyFactory;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
        $this->totalsCollector = $totalsCollector;
        $this->totalsReader = $totalsReader;
        $this->shippingFactory = $shippingFactory;
        $this->shippingAssignmentFactory = $shippingAssignmentFactory;
        $this->orderIncrementIdChecker = $orderIncrementIdChecker ?: ObjectManager::getInstance()
            ->get(\Magento\Sales\Model\OrderIncrementIdChecker::class);
        $this->allowedCountriesReader = $allowedCountriesReader
            ?: ObjectManager::getInstance()->get(AllowedCountries::class);
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * Init resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Magento\Quote\Model\ResourceModel\Quote::class);
    }

    /**
     * Returns information about quote currency, such as code, exchange rate, and so on.
     *
     * @return \Magento\Quote\Api\Data\CurrencyInterface|null Quote currency information. Otherwise, null.
     * @codeCoverageIgnoreStart
     */
    public function getCurrency()
    {
        $currency = $this->getData(self::KEY_CURRENCY);
        if (!$currency) {
            $currency = $this->currencyFactory->create()
                ->setGlobalCurrencyCode($this->getGlobalCurrencyCode())
                ->setBaseCurrencyCode($this->getBaseCurrencyCode())
                ->setStoreCurrencyCode($this->getStoreCurrencyCode())
                ->setQuoteCurrencyCode($this->getQuoteCurrencyCode())
                ->setStoreToBaseRate($this->getStoreToBaseRate())
                ->setStoreToQuoteRate($this->getStoreToQuoteRate())
                ->setBaseToGlobalRate($this->getBaseToGlobalRate())
                ->setBaseToQuoteRate($this->getBaseToQuoteRate());
            $this->setData(self::KEY_CURRENCY, $currency);
        }
        return $currency;
    }

    /**
     * @inheritdoc
     */
    public function setCurrency(\Magento\Quote\Api\Data\CurrencyInterface $currency = null)
    {
        return $this->setData(self::KEY_CURRENCY, $currency);
    }

    /**
     * @inheritdoc
     */
    public function getItems()
    {
        return $this->_getData(self::KEY_ITEMS);
    }

    /**
     * @inheritdoc
     */
    public function setItems(array $items = null)
    {
        return $this->setData(self::KEY_ITEMS, $items);
    }

    /**
     * @inheritdoc
     */
    public function getCreatedAt()
    {
        return $this->_getData(self::KEY_CREATED_AT);
    }

    /**
     * @inheritdoc
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::KEY_CREATED_AT, $createdAt);
    }

    /**
     * @inheritdoc
     */
    public function getUpdatedAt()
    {
        return $this->_getData(self::KEY_UPDATED_AT);
    }

    /**
     * @inheritdoc
     */
    public function setUpdatedAt($updatedAt)
    {
        return $this->setData(self::KEY_UPDATED_AT, $updatedAt);
    }

    /**
     * @inheritdoc
     */
    public function getConvertedAt()
    {
        return $this->_getData(self::KEY_CONVERTED_AT);
    }

    /**
     * @inheritdoc
     */
    public function setConvertedAt($convertedAt)
    {
        return $this->setData(self::KEY_CONVERTED_AT, $convertedAt);
    }

    /**
     * @inheritdoc
     */
    public function getIsActive()
    {
        return $this->_getData(self::KEY_IS_ACTIVE);
    }

    /**
     * @inheritdoc
     */
    public function setIsActive($isActive)
    {
        return $this->setData(self::KEY_IS_ACTIVE, $isActive);
    }

    /**
     * @inheritdoc
     */
    public function setIsVirtual($isVirtual)
    {
        return $this->setData(self::KEY_IS_VIRTUAL, $isVirtual);
    }

    /**
     * @inheritdoc
     */
    public function getItemsCount()
    {
        return $this->_getData(self::KEY_ITEMS_COUNT);
    }

    /**
     * @inheritdoc
     */
    public function setItemsCount($itemsCount)
    {
        return $this->setData(self::KEY_ITEMS_COUNT, $itemsCount);
    }

    /**
     * @inheritdoc
     */
    public function getItemsQty()
    {
        return $this->_getData(self::KEY_ITEMS_QTY);
    }

    /**
     * @inheritdoc
     */
    public function setItemsQty($itemsQty)
    {
        return $this->setData(self::KEY_ITEMS_QTY, $itemsQty);
    }

    /**
     * @inheritdoc
     */
    public function getOrigOrderId()
    {
        return $this->_getData(self::KEY_ORIG_ORDER_ID);
    }

    /**
     * @inheritdoc
     */
    public function setOrigOrderId($origOrderId)
    {
        return $this->setData(self::KEY_ORIG_ORDER_ID, $origOrderId);
    }

    /**
     * @inheritdoc
     */
    public function getReservedOrderId()
    {
        return $this->_getData(self::KEY_RESERVED_ORDER_ID);
    }

    /**
     * @inheritdoc
     */
    public function setReservedOrderId($reservedOrderId)
    {
        return $this->setData(self::KEY_RESERVED_ORDER_ID, $reservedOrderId);
    }

    /**
     * @inheritdoc
     */
    public function getCustomerIsGuest()
    {
        return $this->_getData(self::KEY_CUSTOMER_IS_GUEST);
    }

    /**
     * @inheritdoc
     */
    public function setCustomerIsGuest($customerIsGuest)
    {
        return $this->setData(self::KEY_CUSTOMER_IS_GUEST, $customerIsGuest);
    }

    /**
     * @inheritdoc
     */
    public function getCustomerNote()
    {
        return $this->_getData(self::KEY_CUSTOMER_NOTE);
    }

    /**
     * @inheritdoc
     */
    public function setCustomerNote($customerNote)
    {
        return $this->setData(self::KEY_CUSTOMER_NOTE, $customerNote);
    }

    /**
     * @inheritdoc
     */
    public function getCustomerNoteNotify()
    {
        return $this->_getData(self::KEY_CUSTOMER_NOTE_NOTIFY);
    }

    /**
     * @inheritdoc
     */
    public function setCustomerNoteNotify($customerNoteNotify)
    {
        return $this->setData(self::KEY_CUSTOMER_NOTE_NOTIFY, $customerNoteNotify);
    }

    //@codeCoverageIgnoreEnd

    /**
     * @inheritdoc
     */
    public function getStoreId()
    {
        if (!$this->hasStoreId()) {
            return $this->_storeManager->getStore()->getId();
        }
        return (int)$this->_getData(self::KEY_STORE_ID);
    }

    /**
     * @inheritdoc
     */
    public function setStoreId($storeId)
    {
        $this->setData(self::KEY_STORE_ID, (int)$storeId);
        return $this;
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
        if ($ids === null || !is_array($ids)) {
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

        //mark quote if it has virtual products only
        $this->setIsVirtual($this->getIsVirtual());

        if ($this->hasDataChanges()) {
            $this->setUpdatedAt(null);
        }

        parent::beforeSave();
    }

    /**
     * Loading quote data by customer
     *
     * @param \Magento\Customer\Model\Customer|int $customer
     * @deprecated 101.0.0
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
                    /** @var \Magento\Quote\Model\Quote\Address $billingAddress */
                    $billingAddress = $this->_quoteAddressFactory->create();
                    $billingAddress->importCustomerAddressData($defaultBillingAddress);
                    $this->assignAddress($billingAddress);
                }
            }

            if (null === $shippingAddress) {
                try {
                    $defaultShippingAddress = $this->addressRepository->getById($customer->getDefaultShipping());
                } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                    //
                }
                if (isset($defaultShippingAddress)) {
                    /** @var \Magento\Quote\Model\Quote\Address $shippingAddress */
                    $shippingAddress = $this->_quoteAddressFactory->create();
                    $shippingAddress->importCustomerAddressData($defaultShippingAddress);
                } else {
                    $shippingAddress = $this->_quoteAddressFactory->create();
                }
            }

            $this->assignAddress($shippingAddress, false);
        }

        return $this;
    }

    /**
     * Define customer object
     *
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     * @return $this
     */
    public function setCustomer(\Magento\Customer\Api\Data\CustomerInterface $customer = null)
    {
        /* @TODO: Remove the method after all external usages are refactored in MAGETWO-19930 */
        $this->_customer = $customer;
        $this->setCustomerId($customer->getId());
        $origAddresses = $customer->getAddresses();
        $customer->setAddresses([]);
        $customerDataFlatArray = $this->objectFactory->create(
            $this->extensibleDataObjectConverter->toFlatArray(
                $customer,
                [],
                \Magento\Customer\Api\Data\CustomerInterface::class
            )
        );
        $customer->setAddresses($origAddresses);
        $this->_objectCopyService->copyFieldsetToTarget('customer_account', 'to_quote', $customerDataFlatArray, $this);

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
                $this->_customer = $this->customerDataFactory->create();
                $this->_customer->setId(null);
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
        $this->getCustomer()->setAddresses($addresses);
        $this->updateCustomerData($this->getCustomer());
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
        $quoteCustomer = $this->getCustomer();
        $this->dataObjectHelper->mergeDataObjects(CustomerInterface::class, $quoteCustomer, $customer);
        $this->setCustomer($quoteCustomer);
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
     * @inheritdoc
     */
    public function getCustomerTaxClassId()
    {
        /**
         * tax class can vary at any time. so instead of using the value from session,
         * we need to retrieve from db every time to get the correct tax class
         */
        //if (!$this->getData('customer_group_id') && !$this->getData('customer_tax_class_id')) {
        $groupId = $this->getCustomerGroupId();
        if ($groupId !== null) {
            $taxClassId = $this->groupRepository->getById($this->getCustomerGroupId())->getTaxClassId();
            $this->setCustomerTaxClassId($taxClassId);
        }

        return $this->getData(self::KEY_CUSTOMER_TAX_CLASS_ID);
    }

    /**
     * @inheritdoc
     */
    public function setCustomerTaxClassId($customerTaxClassId)
    {
        return $this->setData(self::KEY_CUSTOMER_TAX_CLASS_ID, $customerTaxClassId);
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
     * Get all shipping addresses.
     *
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
     * @return \Magento\Quote\Model\Quote\Address[]
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
     * Get address by id.
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
     * Get address by customer address id.
     *
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
        /** @var \Magento\Quote\Model\Quote\Address $address */
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
     * Remove address.
     *
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
     * Add address.
     *
     * @param \Magento\Quote\Api\Data\AddressInterface $address
     * @return $this
     */
    public function addAddress(\Magento\Quote\Api\Data\AddressInterface $address)
    {
        $address->setQuote($this);
        if (!$address->getId()) {
            $this->getAddressesCollection()->addItem($address);
        }
        return $this;
    }

    /**
     * Set billing address.
     *
     * @param \Magento\Quote\Api\Data\AddressInterface $address
     * @return $this
     */
    public function setBillingAddress(\Magento\Quote\Api\Data\AddressInterface $address = null)
    {
        $old = $this->getAddressesCollection()->getItemById($address->getId())
            ?? $this->getBillingAddress();
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
     * @param \Magento\Quote\Api\Data\AddressInterface $address
     * @return $this
     */
    public function setShippingAddress(\Magento\Quote\Api\Data\AddressInterface $address = null)
    {
        if ($this->getIsMultiShipping()) {
            $this->addAddress($address->setAddressType(Address::TYPE_SHIPPING));
        } else {
            $old = $this->getAddressesCollection()->getItemById($address->getId())
                ?? $this->getShippingAddress();
            if (!empty($old)) {
                $old->addData($address->getData());
            } else {
                $this->addAddress($address->setAddressType(Address::TYPE_SHIPPING));
            }
        }

        return $this;
    }

    /**
     * Add shipping address.
     *
     * @param \Magento\Quote\Api\Data\AddressInterface $address
     * @return $this
     */
    public function addShippingAddress(\Magento\Quote\Api\Data\AddressInterface $address)
    {
        $this->setShippingAddress($address);
        return $this;
    }

    /**
     * Retrieve quote items collection
     *
     * @param bool $useCache
     * @return \Magento\Eav\Model\Entity\Collection\AbstractCollection
     */
    public function getItemsCollection($useCache = true)
    {
        if ($this->hasItemsCollection() && $useCache) {
            return $this->getData('items_collection');
        }
        if (null === $this->_items || !$useCache) {
            $this->_items = $this->_quoteItemCollectionFactory->create();
            $this->extensionAttributesJoinProcessor->process($this->_items);
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
            /** @var \Magento\Quote\Model\Quote\Item $item */
            if (!$item->isDeleted()) {
                $items[] = $item;
            }
        }
        return $items;
    }

    /**
     * Get array of all items what can be display directly
     *
     * @return \Magento\Quote\Model\Quote\Item[]
     */
    public function getAllVisibleItems()
    {
        $items = [];
        foreach ($this->getItemsCollection() as $item) {
            if (!$item->isDeleted() && !$item->getParentItemId() && !$item->getParentItem()) {
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
        return count($this->getAllItems()) > 0;
    }

    /**
     * Checking availability of items with decimal qty
     *
     * @return bool
     */
    public function hasItemsWithDecimalQty()
    {
        foreach ($this->getAllItems() as $item) {
            $stockItemDo = $this->stockRegistry->getStockItem(
                $item->getProduct()->getId(),
                $item->getStore()->getWebsiteId()
            );
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
     * @return  \Magento\Quote\Model\Quote\Item|false
     */
    public function getItemById($itemId)
    {
        foreach ($this->getItemsCollection() as $item) {
            if ($item->getId() == $itemId) {
                return $item;
            }
        }

        return false;
    }

    /**
     * Delete quote item. If it does not have identifier then it will be only removed from collection
     *
     * @param \Magento\Quote\Model\Quote\Item $item
     * @return $this
     */
    public function deleteItem(\Magento\Quote\Model\Quote\Item $item)
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
     * @param int $itemId
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
            if ($item->getId() === null) {
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
     * @param \Magento\Quote\Model\Quote\Item $item
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function addItem(\Magento\Quote\Model\Quote\Item $item)
    {
        $item->setQuote($this);
        if (!$item->getId()) {
            $this->getItemsCollection()->addItem($item);
            $this->_eventManager->dispatch('sales_quote_add_item', ['quote_item' => $item]);
        }
        return $this;
    }

    /**
     * Add product. Returns error message if product type instance can't prepare product.
     *
     * @param mixed $product
     * @param null|float|\Magento\Framework\DataObject $request
     * @param null|string $processMode
     * @return \Magento\Quote\Model\Quote\Item|string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
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
        if (!$request instanceof \Magento\Framework\DataObject) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('We found an invalid request for adding product to quote.')
            );
        }

        if (!$product->isSalable()) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Product that you are trying to add is not available.')
            );
        }

        $cartCandidates = $product->getTypeInstance()->prepareForCartAdvanced($request, $product, $processMode);

        /**
         * Error message
         */
        if (is_string($cartCandidates) || $cartCandidates instanceof \Magento\Framework\Phrase) {
            return (string)$cartCandidates;
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
                $item->setQuote($this);
                $item->setOptions($candidate->getCustomOptions());
                $item->setProduct($candidate);
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
                foreach ($item->getMessage(false) as $message) {
                    if (!in_array($message, $errors)) {
                        // filter duplicate messages
                        $errors[] = $message;
                    }
                }
            }
        }
        if (!empty($errors)) {
            throw new \Magento\Framework\Exception\LocalizedException(__(implode("\n", $errors)));
        }

        $this->_eventManager->dispatch('sales_quote_product_add_after', ['items' => $items]);
        return $parentItem;
    }

    /**
     * Adding catalog product object data to quote
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param int $qty
     * @return \Magento\Quote\Model\Quote\Item
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
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
     * - 'current_config', \Magento\Framework\DataObject or array - current buyRequest that configures product in this
     * item, used to restore currently attached files
     * - 'files_prefix': string[a-z0-9_] - prefix that was added at frontend to names of file options (file inputs),
     *   so they won't intersect with other submitted options
     *
     * For more options see \Magento\Catalog\Helper\Product->addParamsToBuyRequest()
     *
     * @param int $itemId
     * @param \Magento\Framework\DataObject $buyRequest
     * @param null|array|\Magento\Framework\DataObject $params
     * @return \Magento\Quote\Model\Quote\Item
     * @throws \Magento\Framework\Exception\LocalizedException
     *
     * @see \Magento\Catalog\Helper\Product::addParamsToBuyRequest()
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function updateItem($itemId, $buyRequest, $params = null)
    {
        $item = $this->getItemById($itemId);
        if (!$item) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('This is the wrong quote item id to update configuration.')
            );
        }
        $productId = $item->getProduct()->getId();

        //We need to create new clear product instance with same $productId
        //to set new option values from $buyRequest
        $product = clone $this->productRepository->getById($productId, false, $this->getStore()->getId());

        if (!$params) {
            $params = new \Magento\Framework\DataObject();
        } elseif (is_array($params)) {
            $params = new \Magento\Framework\DataObject($params);
        }
        $params->setCurrentConfig($item->getBuyRequest());
        $buyRequest = $this->_catalogProduct->addParamsToBuyRequest($buyRequest, $params);

        $buyRequest->setResetCount(true);
        $resultItem = $this->addProduct($product, $buyRequest);

        if (is_string($resultItem)) {
            throw new \Magento\Framework\Exception\LocalizedException(__($resultItem));
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
     * @return  \Magento\Quote\Model\Quote\Item|bool
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
     * Get items summary qty.
     *
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
     * Get item virtual qty.
     *
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
     * Get payments collection.
     *
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
     * Get payment.
     *
     * @return \Magento\Quote\Model\Quote\Payment
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
     * Adds a payment to quote
     *
     * @param PaymentInterface $payment
     * @return $this
     */
    protected function addPayment(PaymentInterface $payment)
    {
        $payment->setQuote($this);
        if (!$payment->getId()) {
            $this->getPaymentsCollection()->addItem($payment);
        }
        return $this;
    }

    /**
     * Sets payment to current quote
     *
     * @param PaymentInterface $payment
     * @return PaymentInterface
     */
    public function setPayment(PaymentInterface $payment)
    {
        if (!$this->getIsMultiPayment() && ($old = $this->getPayment())) {
            $payment->setId($old->getId());
        }
        $this->addPayment($payment);

        return $payment;
    }

    /**
     * Remove payment.
     *
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
        if ($this->getTotalsCollectedFlag()) {
            return $this;
        }

        $total = $this->totalsCollector->collect($this);
        $this->addData($total->getData());

        $this->setTotalsCollectedFlag(true);
        return $this;
    }

    /**
     * Get all quote totals (sorted by priority)
     *
     * @return AddressTotal[]
     */
    public function getTotals()
    {
        return $this->totalsReader->fetch($this, $this->getData());
    }

    /**
     * Add message.
     *
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

        $message = $this->messageFactory->create(\Magento\Framework\Message\MessageInterface::TYPE_ERROR, $message);

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
                $errors[] = $message;
            }
        }
        return $errors;
    }

    /**
     * Sets flag, whether this quote has some error associated with it.
     *
     * @codeCoverageIgnore
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
     * Clears list of errors, associated with this quote. Also automatically removes error-flag from oneself.
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
     * Adds error information to the quote. Automatically sets error flag.
     *
     * @param string $type An internal error type ('error', 'qty', etc.), passed then to adding messages routine
     * @param string|null $origin Usually a name of module, that embeds error
     * @param int|null $code Error code, unique for origin, that sets it
     * @param string|null $message Error message
     * @param \Magento\Framework\DataObject|null $additionalData Any additional data, that caller would like to store
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
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
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
            if ($this->orderIncrementIdChecker->isIncrementIdUsed($this->getReservedOrderId())) {
                $this->setReservedOrderId($this->_getResource()->getReservedOrderId($this));
            }
        }
        return $this;
    }

    /**
     * Validate minimum amount.
     *
     * @param bool $multishipping
     * @return bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
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
        $includeDiscount = $this->_scopeConfig->getValue(
            'sales/minimum_order/include_discount_amount',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
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
                    /** @var \Magento\Quote\Model\Quote\Item $item */
                    $amount = $includeDiscount ?
                        $item->getBaseRowTotal() - $item->getBaseDiscountAmount() + $taxes :
                        $item->getBaseRowTotal() + $taxes;

                    if ($amount < $minAmount) {
                        return false;
                    }
                }
            }
        } else {
            $baseTotal = 0;
            foreach ($addresses as $address) {
                $taxes = ($taxInclude) ? $address->getBaseTaxAmount() : 0;
                $baseTotal += $includeDiscount ?
                    $address->getBaseSubtotalWithDiscount() + $taxes :
                    $address->getBaseSubtotal() + $taxes;
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
            /* @var $_item \Magento\Quote\Model\Quote\Item */
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
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getIsVirtual()
    {
        return (int)$this->isVirtual();
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
     * @param Quote $quote
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
                    $this->itemProcessor->merge($item, $quoteItem);
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
     * Trigger collect totals after loading, if required
     *
     * @return $this
     */
    protected function _afterLoad()
    {
        // collect totals and save me, if required
        if (1 == $this->getTriggerRecollect()) {
            $this->collectTotals()
                ->setTriggerRecollect(0)
                ->save();
        }
        return parent::_afterLoad();
    }

    /**
     * Checks if it was set
     *
     * @return bool
     */
    public function addressCollectionWasSet()
    {
        return null !== $this->_addresses;
    }

    /**
     * Checks if it was set
     *
     * @return bool
     */
    public function itemsCollectionWasSet()
    {
        return null !== $this->_items;
    }

    /**
     * Checks if it was set
     *
     * @return bool
     */
    public function paymentsCollectionWasSet()
    {
        return null !== $this->_payments;
    }

    /**
     * Checks if it was set
     *
     * @return bool
     */
    public function currentPaymentWasSet()
    {
        return null !== $this->_currentPayment;
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
        return $this->_getData(self::KEY_CHECKOUT_METHOD);
    }

    /**
     * Get quote items assigned to different quote addresses populated per item qty.
     *
     * @return array
     */
    public function getShippingAddressesItems()
    {
        if ($this->shippingAddressesItems !== null) {
            return $this->shippingAddressesItems;
        }
        $items = [];
        $addresses = $this->getAllAddresses();
        foreach ($addresses as $address) {
            foreach ($address->getAllItems() as $item) {
                if ($item->getParentItemId()) {
                    continue;
                }
                if ($item->getProduct()->getIsVirtual()) {
                    $items[] = $item;
                    continue;
                }
                if ($item->getQty() > 1) {
                    for ($itemIndex = 0, $itemQty = $item->getQty(); $itemIndex < $itemQty; $itemIndex++) {
                        if ($itemIndex == 0) {
                            $addressItem = $item;
                        } else {
                            $addressItem = clone $item;
                        }
                        $addressItem->setQty(1)->setCustomerAddressId($address->getCustomerAddressId())->save();
                        $items[] = $addressItem;
                    }
                } else {
                    $item->setCustomerAddressId($address->getCustomerAddressId());
                    $items[] = $item;
                }
            }
        }
        $this->shippingAddressesItems = $items;
        return $items;
    }

    /**
     * Sets the payment method that is used to process the cart.
     *
     * @codeCoverageIgnore
     *
     * @param string $checkoutMethod
     * @return $this
     */
    public function setCheckoutMethod($checkoutMethod)
    {
        return $this->setData(self::KEY_CHECKOUT_METHOD, $checkoutMethod);
    }

    /**
     * Prevent quote from saving
     *
     * @codeCoverageIgnore
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
     * @codeCoverageIgnore
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

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Quote\Api\Data\CartExtensionInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Quote\Api\Data\CartExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(\Magento\Quote\Api\Data\CartExtensionInterface $extensionAttributes)
    {
        return $this->_setExtensionAttributes($extensionAttributes);
    }

    /**
     * Check is address allowed for store
     *
     * @param Address $address
     * @param int|null $storeId
     * @return bool
     */
    private function isAddressAllowedForWebsite(Address $address, $storeId): bool
    {
        $allowedCountries = $this->allowedCountriesReader->getAllowedCountries(ScopeInterface::SCOPE_STORE, $storeId);

        return in_array($address->getCountryId(), $allowedCountries);
    }

    /**
     * Assign address to quote
     *
     * @param Address $address
     * @param bool $isBillingAddress
     * @return void
     */
    private function assignAddress(Address $address, bool $isBillingAddress = true): void
    {
        if ($this->isAddressAllowedForWebsite($address, $this->getStoreId())) {
            $isBillingAddress
                ? $this->setBillingAddress($address)
                : $this->setShippingAddress($address);
        }
    }
}
