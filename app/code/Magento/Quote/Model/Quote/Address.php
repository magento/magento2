<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model\Quote;

use Magento\Customer\Api\AddressMetadataInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\AddressInterfaceFactory;
use Magento\Customer\Api\Data\RegionInterfaceFactory;
use Magento\Customer\Model\Address\AbstractAddress;
use Magento\Customer\Model\Address\AbstractAddress\CountryModelsCache;
use Magento\Customer\Model\Address\AbstractAddress\RegionModelsCache;
use Magento\Customer\Model\Address\CompositeValidator;
use Magento\Customer\Model\Address\Mapper;
use Magento\Directory\Helper\Data;
use Magento\Directory\Model\CountryFactory;
use Magento\Directory\Model\RegionFactory;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\DataObject\Copy;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Quote\Api\Data\AddressExtensionInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address\Rate;
use Magento\Quote\Model\Quote\Address\RateCollectorInterfaceFactory;
use Magento\Quote\Model\Quote\Address\RateFactory;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Quote\Model\Quote\Address\RateRequestFactory;
use Magento\Quote\Model\Quote\Address\Total;
use Magento\Quote\Model\Quote\Address\Total\Collector;
use Magento\Quote\Model\Quote\Address\Total\CollectorFactory;
use Magento\Quote\Model\Quote\Address\TotalFactory;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\Quote\Model\ResourceModel\Quote\Address\Rate\CollectionFactory;
use Magento\Shipping\Model\CarrierFactoryInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Sales Quote address model
 *
 * @api
 * @method int getQuoteId()
 * @method Address setQuoteId(int $value)
 * @method string getCreatedAt()
 * @method Address setCreatedAt(string $value)
 * @method string getUpdatedAt()
 * @method Address setUpdatedAt(string $value)
 * @method AddressInterface getCustomerAddress()
 * @method Address setCustomerAddressData(AddressInterface $value)
 * @method string getAddressType()
 * @method Address setAddressType(string $value)
 * @method int getFreeShipping()
 * @method Address setFreeShipping(int $value)
 * @method bool getCollectShippingRates()
 * @method Address setCollectShippingRates(bool $value)
 * @method Address setShippingMethod(string $value)
 * @method string getShippingDescription()
 * @method Address setShippingDescription(string $value)
 * @method float getWeight()
 * @method Address setWeight(float $value)
 * @method float getSubtotal()
 * @method Address setSubtotal(float $value)
 * @method float getBaseSubtotal()
 * @method Address setBaseSubtotal(float $value)
 * @method Address setSubtotalWithDiscount(float $value)
 * @method Address setBaseSubtotalWithDiscount(float $value)
 * @method float getTaxAmount()
 * @method Address setTaxAmount(float $value)
 * @method float getBaseTaxAmount()
 * @method Address setBaseTaxAmount(float $value)
 * @method float getShippingAmount()
 * @method float getBaseShippingAmount()
 * @method float getShippingTaxAmount()
 * @method Address setShippingTaxAmount(float $value)
 * @method float getBaseShippingTaxAmount()
 * @method Address setBaseShippingTaxAmount(float $value)
 * @method float getDiscountAmount()
 * @method Address setDiscountAmount(float $value)
 * @method float getBaseDiscountAmount()
 * @method float getGrandTotal()
 * @method Address setGrandTotal(float $value)
 * @method float getBaseGrandTotal()
 * @method Address setBaseGrandTotal(float $value)
 * @method string getCustomerNotes()
 * @method Address setCustomerNotes(string $value)
 * @method string getDiscountDescription()
 * @method Address setDiscountDescription(string $value)
 * @method null|array getDiscountDescriptionArray()
 * @method Address setDiscountDescriptionArray(array $value)
 * @method float getShippingDiscountAmount()
 * @method Address setShippingDiscountAmount(float $value)
 * @method float getBaseShippingDiscountAmount()
 * @method Address setBaseShippingDiscountAmount(float $value)
 * @method float getSubtotalInclTax()
 * @method Address setSubtotalInclTax(float $value)
 * @method float getBaseSubtotalTotalInclTax()
 * @method Address setBaseSubtotalTotalInclTax(float $value)
 * @method int getGiftMessageId()
 * @method Address setGiftMessageId(int $value)
 * @method float getDiscountTaxCompensationAmount()
 * @method Address setDiscountTaxCompensationAmount(float $value)
 * @method float getBaseDiscountTaxCompensationAmount()
 * @method Address setBaseDiscountTaxCompensationAmount(float $value)
 * @method float getShippingDiscountTaxCompensationAmount()
 * @method Address setShippingDiscountTaxCompensationAmount(float $value)
 * @method float getBaseShippingDiscountTaxCompensationAmnt()
 * @method Address setBaseShippingDiscountTaxCompensationAmnt(float $value)
 * @method float getShippingInclTax()
 * @method Address setShippingInclTax(float $value)
 * @method float getBaseShippingInclTax()
 * @method \Magento\SalesRule\Model\Rule[] getCartFixedRules()
 * @method int[] getAppliedRuleIds()
 * @method Address setBaseShippingInclTax(float $value)
 *
 * @property $objectCopyService \Magento\Framework\DataObject\Copy
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 100.0.2
 */
class Address extends AbstractAddress implements
    \Magento\Quote\Api\Data\AddressInterface
{
    public const RATES_FETCH = 1;

    public const RATES_RECALCULATE = 2;

    public const ADDRESS_TYPE_BILLING = 'billing';

    public const ADDRESS_TYPE_SHIPPING = 'shipping';

    private const CACHED_ITEMS_ALL = 'cached_items_all';

    private const BASE_DISCOUNT_AMOUNT = 'base_discount_amount';

    /**
     * Prefix of model events
     *
     * @var string
     */
    protected $_eventPrefix = 'sales_quote_address';

    /**
     * Name of event object
     *
     * @var string
     */
    protected $_eventObject = 'quote_address';

    /**
     * Quote object
     *
     * @var Quote
     */
    protected $_items;

    /**
     * Quote object
     *
     * @var Quote
     */
    protected $_quote;

    /**
     * Sales Quote address rates
     *
     * @var Rate
     */
    protected $_rates;

    /**
     * Total models collector
     *
     * @var Collector
     */
    protected $_totalCollector;

    /**
     * Total data as array
     *
     * @var array
     */
    protected $_totals = [];

    /**
     * @var array
     */
    protected $_totalAmounts = [];

    /**
     * @var array
     */
    protected $_baseTotalAmounts = [];

    /**
     * Core store config
     *
     * @var ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Quote\Model\Quote\Address\ItemFactory
     */
    protected $_addressItemFactory;

    /**
     * @var \Magento\Quote\Model\ResourceModel\Quote\Address\Item\CollectionFactory
     */
    protected $_itemCollectionFactory;

    /**
     * @var RateCollectorInterfaceFactory
     */
    protected $_rateCollector;

    /**
     * @var CollectionFactory
     */
    protected $_rateCollectionFactory;

    /**
     * @var CollectorFactory
     */
    protected $_totalCollectorFactory;

    /**
     * @var TotalFactory
     */
    protected $_addressTotalFactory;

    /**
     * @var RateFactory
     * @since 101.0.0
     */
    protected $_addressRateFactory;

    /**
     * @var AddressInterfaceFactory
     */
    protected $addressDataFactory;

    /**
     * @var Address\Validator
     */
    protected $validator;

    /**
     * @var Mapper
     */
    protected $addressMapper;

    /**
     * @var Address\RateRequestFactory
     */
    protected $_rateRequestFactory;

    /**
     * @var Address\CustomAttributeListInterface
     */
    protected $attributeList;

    /**
     * @var TotalsCollector
     */
    protected $totalsCollector;

    /**
     * @var TotalsReader
     */
    protected $totalsReader;

    /**
     * @var Json
     */
    private $serializer;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Copy
     */
    private $objectCopyService;

    /**
     * @var /Magento\Shipping\Model\CarrierFactoryInterface
     */
    private $carrierFactory;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param Data $directoryData
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Customer\Model\Address\Config $addressConfig
     * @param RegionFactory $regionFactory
     * @param CountryFactory $countryFactory
     * @param AddressMetadataInterface $metadataService
     * @param AddressInterfaceFactory $addressDataFactory
     * @param RegionInterfaceFactory $regionDataFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param ScopeConfigInterface $scopeConfig
     * @param Address\ItemFactory $addressItemFactory
     * @param \Magento\Quote\Model\ResourceModel\Quote\Address\Item\CollectionFactory $itemCollectionFactory
     * @param RateFactory $addressRateFactory
     * @param Address\RateCollectorInterfaceFactory $rateCollector
     * @param CollectionFactory $rateCollectionFactory
     * @param Address\RateRequestFactory $rateRequestFactory
     * @param Address\Total\CollectorFactory $totalCollectorFactory
     * @param Address\TotalFactory $addressTotalFactory
     * @param Copy $objectCopyService
     * @param CarrierFactoryInterface $carrierFactory
     * @param Address\Validator $validator
     * @param Mapper $addressMapper
     * @param Address\CustomAttributeListInterface $attributeList
     * @param TotalsCollector $totalsCollector
     * @param TotalsReader $totalsReader
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     * @param Json $serializer
     * @param StoreManagerInterface $storeManager
     * @param CompositeValidator|null $compositeValidator
     * @param CountryModelsCache|null $countryModelsCache
     * @param RegionModelsCache|null $regionModelsCache
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        Data $directoryData,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Customer\Model\Address\Config $addressConfig,
        RegionFactory $regionFactory,
        CountryFactory $countryFactory,
        AddressMetadataInterface $metadataService,
        AddressInterfaceFactory $addressDataFactory,
        RegionInterfaceFactory $regionDataFactory,
        DataObjectHelper $dataObjectHelper,
        ScopeConfigInterface $scopeConfig,
        \Magento\Quote\Model\Quote\Address\ItemFactory $addressItemFactory,
        \Magento\Quote\Model\ResourceModel\Quote\Address\Item\CollectionFactory $itemCollectionFactory,
        RateFactory $addressRateFactory,
        RateCollectorInterfaceFactory $rateCollector,
        CollectionFactory $rateCollectionFactory,
        RateRequestFactory $rateRequestFactory,
        CollectorFactory $totalCollectorFactory,
        TotalFactory $addressTotalFactory,
        Copy $objectCopyService,
        CarrierFactoryInterface $carrierFactory,
        Address\Validator $validator,
        Mapper $addressMapper,
        Address\CustomAttributeListInterface $attributeList,
        TotalsCollector $totalsCollector,
        TotalsReader $totalsReader,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = [],
        Json $serializer = null,
        StoreManagerInterface $storeManager = null,
        ?CompositeValidator $compositeValidator = null,
        ?CountryModelsCache $countryModelsCache = null,
        ?RegionModelsCache $regionModelsCache = null,
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->_addressItemFactory = $addressItemFactory;
        $this->_itemCollectionFactory = $itemCollectionFactory;
        $this->_addressRateFactory = $addressRateFactory;
        $this->_rateCollector = $rateCollector;
        $this->_rateCollectionFactory = $rateCollectionFactory;
        $this->_rateRequestFactory = $rateRequestFactory;
        $this->_totalCollectorFactory = $totalCollectorFactory;
        $this->_addressTotalFactory = $addressTotalFactory;
        $this->objectCopyService = $objectCopyService;
        $this->carrierFactory = $carrierFactory;
        $this->addressDataFactory = $addressDataFactory;
        $this->validator = $validator;
        $this->addressMapper = $addressMapper;
        $this->attributeList = $attributeList;
        $this->totalsCollector = $totalsCollector;
        $this->totalsReader = $totalsReader;
        $this->serializer = $serializer ?: ObjectManager::getInstance()->get(Json::class);
        $this->storeManager = $storeManager ?: ObjectManager::getInstance()->get(StoreManagerInterface::class);
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $directoryData,
            $eavConfig,
            $addressConfig,
            $regionFactory,
            $countryFactory,
            $metadataService,
            $addressDataFactory,
            $regionDataFactory,
            $dataObjectHelper,
            $resource,
            $resourceCollection,
            $data,
            $compositeValidator,
            $countryModelsCache,
            $regionModelsCache,
        );
    }

    /**
     * Initialize resource
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Magento\Quote\Model\ResourceModel\Quote\Address::class);
    }

    /**
     * Initialize quote identifier before save
     *
     * @return $this
     */
    public function beforeSave()
    {
        parent::beforeSave();
        $this->_populateBeforeSaveData();
        return $this;
    }

    /**
     * Set the required fields
     *
     * @return void
     */
    protected function _populateBeforeSaveData()
    {
        if ($this->getQuote()) {
            $this->_dataSaveAllowed = (bool)$this->getQuote()->getId();

            if ($this->getQuote()->getId()) {
                $this->setQuoteId($this->getQuote()->getId());
            }
            $this->setCustomerId($this->getQuote()->getCustomerId());

            /**
             * Init customer address id if customer address is assigned
             */
            if ($this->getCustomerAddressData()) {
                $this->setCustomerAddressId($this->getCustomerAddressData()->getId());
            }

            if (!$this->getId() || $this->getQuote()->dataHasChangedFor('customer_id')) {
                $this->setSameAsBilling((int)$this->_isSameAsBilling());
            }
        }
    }

    /**
     * Returns true if shipping address is same as billing
     *
     * @return bool
     */
    protected function _isSameAsBilling()
    {
        $quoteSameAsBilling = $this->getSameAsBilling();

        return $this->getAddressType() == Address::TYPE_SHIPPING &&
            ($this->_isNotRegisteredCustomer() || $this->_isDefaultShippingNullOrSameAsBillingAddress()) &&
            ($quoteSameAsBilling || $quoteSameAsBilling === 0 || $quoteSameAsBilling === null);
    }

    /**
     * Checks if the user is a registered customer
     *
     * @return bool
     */
    protected function _isNotRegisteredCustomer()
    {
        return !$this->getQuote()->getCustomerId() || $this->getCustomerAddressId() === null;
    }

    /**
     * Returns true if shipping address is same as billing or it is undefined
     *
     * @return bool
     */
    protected function _isDefaultShippingNullOrSameAsBillingAddress()
    {
        $customer = $this->getQuote()->getCustomer();
        $customerId = $customer->getId();
        $defaultBillingAddress = null;
        $defaultShippingAddress = null;

        if ($customerId) {
            /* we should load data from the service once customer is saved */
            $defaultBillingAddress = $customer->getDefaultBilling();
            $defaultShippingAddress = $customer->getDefaultShipping();
        } else {
            /* we should load data from the quote if customer is not saved yet */
            $defaultBillingAddress = $customer->getDefaultBilling();
            $defaultShippingAddress = $customer->getDefaultShipping();
        }

        return !$defaultShippingAddress
            || $defaultBillingAddress
            && $defaultShippingAddress
            && $defaultBillingAddress == $defaultShippingAddress;
    }

    /**
     * Declare address quote model object
     *
     * @param Quote $quote
     * @return $this
     */
    public function setQuote(Quote $quote)
    {
        $this->_quote = $quote;
        $this->setQuoteId($quote->getId());
        return $this;
    }

    /**
     * Retrieve quote object
     *
     * @return Quote
     */
    public function getQuote()
    {
        return $this->_quote;
    }

    /**
     * Import quote address data from customer address Data Object.
     *
     * @param AddressInterface $address
     * @return $this
     */
    public function importCustomerAddressData(AddressInterface $address)
    {
        $this->objectCopyService->copyFieldsetToTarget(
            'customer_address',
            'to_quote_address',
            $this->addressMapper->toFlatArray($address),
            $this
        );

        $quote = $this->getQuote();
        // @phpstan-ignore-next-line as $quote can be empty
        if ($address->getCustomerId() && (!empty($quote) && $address->getCustomerId() == $quote->getCustomerId())) {
            $customer = $quote->getCustomer();
            $this->setEmail($customer->getEmail());
        }
        return $this;
    }

    /**
     * Export data to customer address Data Object.
     *
     * @return AddressInterface
     */
    public function exportCustomerAddress()
    {
        $customerAddressData = $this->objectCopyService->getDataFromFieldset(
            'sales_convert_quote_address',
            'to_customer_address',
            $this
        );
        $customerAddressDataWithRegion = [];
        $customerAddressDataWithRegion['region']['region'] = $customerAddressData['region'];
        if (isset($customerAddressData['region_code'])) {
            $customerAddressDataWithRegion['region']['region_code'] = $customerAddressData['region_code'];
        }
        if ($customerAddressData['region_id']) {
            $customerAddressDataWithRegion['region']['region_id'] = $customerAddressData['region_id'];
        }
        $customerAddressData = array_merge($customerAddressData, $customerAddressDataWithRegion);

        $addressDataObject = $this->addressDataFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $addressDataObject,
            $customerAddressData,
            AddressInterface::class
        );
        return $addressDataObject;
    }

    /**
     * Convert object to array
     *
     * @param   array $arrAttributes
     * @return  array
     */
    public function toArray(array $arrAttributes = [])
    {
        $arr = parent::toArray($arrAttributes);
        $arr['rates'] = $this->getShippingRatesCollection()->toArray($arrAttributes);
        $arr['items'] = $this->getItemsCollection()->toArray($arrAttributes);
        foreach ($this->getTotals() as $k => $total) {
            $arr['totals'][$k] = $total->toArray();
        }

        return $arr;
    }

    /**
     * Retrieve address items collection
     *
     * @return AbstractCollection
     */
    public function getItemsCollection()
    {
        if (null === $this->_items) {
            $this->_items = $this->_itemCollectionFactory->create()->setAddressFilter($this->getId());
            if ($this->getId()) {
                foreach ($this->_items as $item) {
                    $item->setAddress($this);
                }
            }
        }

        return $this->_items;
    }

    /**
     * Get all available address items
     *
     * @return \Magento\Quote\Model\Quote\Address\Item[]
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function getAllItems()
    {
        // We calculate item list once and cache it in three arrays - all items
        if (!$this->hasData(self::CACHED_ITEMS_ALL)) {
            $quoteItems = $this->getQuote()->getItemsCollection();
            $addressItems = $this->getItemsCollection();

            $items = [];
            if ($this->getQuote()->getIsMultiShipping() && $addressItems->count() > 0) {
                foreach ($addressItems as $aItem) {
                    if ($aItem->isDeleted()) {
                        continue;
                    }

                    if (!$aItem->getQuoteItemImported()) {
                        $qItem = $this->getQuote()->getItemById($aItem->getQuoteItemId());
                        if ($qItem) {
                            $aItem->importQuoteItem($qItem);
                        }
                    }
                    $items[] = $aItem;
                }
            } else {
                /*
                 * For virtual quote we assign items only to billing address, otherwise - only to shipping address
                 */
                $addressType = $this->getAddressType();
                $canAddItems = $this->getQuote()->isVirtual()
                    ? $addressType == self::TYPE_BILLING
                    : $addressType == self::TYPE_SHIPPING;

                if ($canAddItems) {
                    foreach ($quoteItems as $qItem) {
                        if ($qItem->isDeleted()) {
                            continue;
                        }
                        $items[] = $qItem;
                    }
                }
            }

            // Cache calculated lists
            $this->setData(self::CACHED_ITEMS_ALL, $items);
        }

        $items = $this->getData(self::CACHED_ITEMS_ALL);

        return $items;
    }

    /**
     * Retrieve all visible items
     *
     * @return array
     */
    public function getAllVisibleItems()
    {
        $items = [];
        foreach ($this->getAllItems() as $item) {
            if (!$item->getParentItemId()) {
                $items[] = $item;
            }
        }

        return $items;
    }

    /**
     * Retrieve item quantity by id
     *
     * @param int $itemId
     * @return float|int
     */
    public function getItemQty($itemId = 0)
    {
        if ($this->hasData('item_qty')) {
            return $this->getData('item_qty');
        }

        $qty = 0;
        if ($itemId == 0) {
            foreach ($this->getAllItems() as $item) {
                $qty += $item->getQty();
            }
        } else {
            $item = $this->getItemById($itemId);
            if ($item) {
                $qty = $item->getQty();
            }
        }

        return $qty;
    }

    /**
     * Check Quote address has Items
     *
     * @return bool
     */
    public function hasItems()
    {
        return count($this->getAllItems()) > 0;
    }

    /**
     * Get address item object by id without
     *
     * @param int $itemId
     * @return \Magento\Quote\Model\Quote\Address\Item|false
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
     * Get prepared not deleted item
     *
     * @param int $itemId
     * @return \Magento\Quote\Model\Quote\Address\Item|false
     */
    public function getValidItemById($itemId)
    {
        foreach ($this->getAllItems() as $item) {
            if ($item->getId() == $itemId) {
                return $item;
            }
        }

        return false;
    }

    /**
     * Retrieve item object by quote item Id
     *
     * @param int $itemId
     * @return \Magento\Quote\Model\Quote\Address\Item|false
     */
    public function getItemByQuoteItemId($itemId)
    {
        foreach ($this->getItemsCollection() as $item) {
            if ($item->getQuoteItemId() == $itemId) {
                return $item;
            }
        }
        return false;
    }

    /**
     * Remove item from collection
     *
     * @param int $itemId
     * @return $this
     */
    public function removeItem($itemId)
    {
        $item = $this->getItemById($itemId);
        if ($item) {
            $item->isDeleted(true);
        }

        return $this;
    }

    /**
     * Add item to address
     *
     * @param AbstractItem $item
     * @param int $qty
     * @return $this
     */
    public function addItem(AbstractItem $item, $qty = null)
    {
        if ($item instanceof Item) {
            if ($item->getParentItemId()) {
                return $this;
            }
            $addressItem = $this->_addressItemFactory->create()->setAddress($this)->importQuoteItem($item);
            $this->getItemsCollection()->addItem($addressItem);

            if ($item->getHasChildren()) {
                foreach ($item->getChildren() as $child) {
                    $addressChildItem = $this->_addressItemFactory->create()->setAddress(
                        $this
                    )->importQuoteItem(
                        $child
                    )->setParentItem(
                        $addressItem
                    );
                    $this->getItemsCollection()->addItem($addressChildItem);
                }
            }
        } else {
            $addressItem = $item;
            $addressItem->setAddress($this);
            if (!$addressItem->getId()) {
                $this->getItemsCollection()->addItem($addressItem);
            }
        }

        if ($qty) {
            $addressItem->setQty($qty);
        }

        return $this;
    }

    /**
     * Retrieve collection of quote shipping rates
     *
     * @return AbstractCollection
     */
    public function getShippingRatesCollection()
    {
        if (null === $this->_rates) {
            $this->_rates = $this->_rateCollectionFactory->create()->setAddressFilter($this->getId());
            if ($this->getId()) {
                foreach ($this->_rates as $rate) {
                    $rate->setAddress($this);
                }
            }
        }
        return $this->_rates;
    }

    /**
     * Retrieve all address shipping rates
     *
     * @return array
     */
    public function getAllShippingRates()
    {
        $rates = [];
        foreach ($this->getShippingRatesCollection() as $rate) {
            if (!$rate->isDeleted()) {
                $rates[] = $rate;
            }
        }

        return $rates;
    }

    /**
     * Retrieve all grouped shipping rates
     *
     * @return array
     */
    public function getGroupedAllShippingRates()
    {
        $rates = [];
        foreach ($this->getShippingRatesCollection() as $rate) {
            if (!$rate->isDeleted() && $this->carrierFactory->get($rate->getCarrier())) {
                if (!isset($rates[$rate->getCarrier()])) {
                    $rates[$rate->getCarrier()] = [];
                }

                $rates[$rate->getCarrier()][] = $rate;
                $rates[$rate->getCarrier()][0]->carrier_sort_order = $this->carrierFactory->get(
                    $rate->getCarrier()
                )->getSortOrder();
            }
        }
        uasort($rates, [$this, '_sortRates']);

        return $rates;
    }

    /**
     * Sort rates recursive callback
     *
     * @param array $firstItem
     * @param array $secondItem
     * @return int
     */
    protected function _sortRates($firstItem, $secondItem)
    {
        return (int) $firstItem[0]->carrier_sort_order <=> (int) $secondItem[0]->carrier_sort_order;
    }

    /**
     * Retrieve shipping rate by identifier
     *
     * @param   int $rateId
     * @return  Rate|false
     */
    public function getShippingRateById($rateId)
    {
        foreach ($this->getShippingRatesCollection() as $rate) {
            if ($rate->getId() == $rateId) {
                return $rate;
            }
        }

        return false;
    }

    /**
     * Retrieve shipping rate by code
     *
     * @param   string $code
     * @return  Rate|false
     */
    public function getShippingRateByCode($code)
    {
        foreach ($this->getShippingRatesCollection() as $rate) {
            if ($rate->getCode() == $code) {
                return $rate;
            }
        }

        return false;
    }

    /**
     * Mark all shipping rates as deleted
     *
     * @return $this
     */
    public function removeAllShippingRates()
    {
        foreach ($this->getShippingRatesCollection() as $rate) {
            $rate->isDeleted(true);
        }
        return $this;
    }

    /**
     * Add shipping rate
     *
     * @param Rate $rate
     * @return $this
     */
    public function addShippingRate(Rate $rate)
    {
        $rate->setAddress($this);
        $this->getShippingRatesCollection()->addItem($rate);

        return $this;
    }

    /**
     * Collecting shipping rates by address
     *
     * @return $this
     */
    public function collectShippingRates()
    {
        if (!$this->getCollectShippingRates()) {
            return $this;
        }

        $this->setCollectShippingRates(false);

        $this->removeAllShippingRates();

        if (!$this->getCountryId()) {
            return $this;
        }

        $found = $this->requestShippingRates();
        if (!$found) {
            $this->setShippingAmount(0)->setBaseShippingAmount(0)->setShippingMethod('')->setShippingDescription('');
        }

        return $this;
    }

    /**
     * Request shipping rates for entire address or specified address item
     *
     * Returns true if current selected shipping method code corresponds to one of the found rates
     *
     * @param AbstractItem $item
     * @return bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function requestShippingRates(AbstractItem $item = null)
    {
        $storeId = $this->getQuote()->getStoreId() ?: $this->storeManager->getStore()->getId();
        $taxInclude = $this->_scopeConfig->getValue(
            'tax/calculation/price_includes_tax',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        /** @var $request RateRequest */
        $request = $this->_rateRequestFactory->create();
        $request->setAllItems($item ? [$item] : $this->getAllItems());
        $request->setDestCountryId($this->getCountryId());
        $request->setDestRegionId($this->getRegionId());
        $request->setDestRegionCode($this->getRegionCode());
        $request->setDestStreet($this->getStreetFull());
        $request->setDestCity($this->getCity());
        $request->setDestPostcode($this->getPostcode());
        $baseSubtotal = $taxInclude ? $this->getBaseSubtotalTotalInclTax() : $this->getBaseSubtotal();
        $request->setPackageValue($item ? $item->getBaseRowTotal() : $baseSubtotal);
        $baseSubtotalWithDiscount = $baseSubtotal + $this->getBaseDiscountAmount();
        $packageWithDiscount = $item ? $item->getBaseRowTotal() -
            $item->getBaseDiscountAmount() : $baseSubtotalWithDiscount;
        $request->setPackageValueWithDiscount($packageWithDiscount);
        $request->setPackageWeight($item ? $item->getRowWeight() : $this->getWeight());
        $request->setPackageQty($item ? $item->getQty() : $this->getItemQty());

        /**
         * Need for shipping methods that use insurance based on price of physical products
         */
        $packagePhysicalValue = $item ? $item->getBaseRowTotal() : $baseSubtotal - $this->getBaseVirtualAmount();
        $request->setPackagePhysicalValue($packagePhysicalValue);

        $request->setFreeMethodWeight($item ? 0 : $this->getFreeMethodWeight());

        /**
         * Store and website identifiers specified from StoreManager
         */
        $request->setStoreId($storeId);
        if ($this->getQuote()->getStoreId()) {
            $request->setWebsiteId($this->storeManager->getStore($storeId)->getWebsiteId());
        } else {
            $request->setWebsiteId($this->storeManager->getWebsite()->getId());
        }
        $request->setFreeShipping($this->getFreeShipping());
        /**
         * Currencies need to convert in free shipping
         */
        $request->setBaseCurrency($this->storeManager->getStore()->getBaseCurrency());
        $request->setPackageCurrency($this->storeManager->getStore()->getCurrentCurrency());
        $request->setLimitCarrier($this->getLimitCarrier());
        $baseSubtotalInclTax = $this->getBaseSubtotalTotalInclTax();
        $request->setBaseSubtotalInclTax($baseSubtotalInclTax);
        $request->setBaseSubtotalWithDiscountInclTax($this->getBaseSubtotalWithDiscount() + $this->getBaseTaxAmount());

        $result = $this->_rateCollector->create()->collectRates($request)->getResult();

        $found = false;
        if ($result) {
            $shippingRates = $result->getAllRates();

            foreach ($shippingRates as $shippingRate) {
                $rate = $this->_addressRateFactory->create()->importShippingRate($shippingRate);
                if (!$item) {
                    $this->addShippingRate($rate);
                }

                if ($this->getShippingMethod() == $rate->getCode()) {
                    if ($item) {
                        $item->setBaseShippingAmount($rate->getPrice());
                    } else {

                        /** @var StoreInterface */
                        $store = $this->storeManager->getStore();
                        $amountPrice = $store->getBaseCurrency()
                            ->convert($rate->getPrice(), $store->getCurrentCurrencyCode());
                        $this->setBaseShippingAmount($rate->getPrice());
                        $this->setShippingAmount($amountPrice);
                    }

                    $found = true;
                }
            }
        }

        return $found;
    }

    /******************************* Total Collector Interface *******************************************/

    /**
     * Get address totals as array
     *
     * @return array
     */
    public function getTotals()
    {
        $totalsData = array_merge(
            $this->getData(),
            [
                'address_quote_items' => $this->getAllItems(),
                'quote_items' => $this->getQuote()->getAllItems(),
            ]
        );
        $totals = $this->totalsReader->fetch($this->getQuote(), $totalsData);
        foreach ($totals as $total) {
            $this->addTotal($total);
        }

        return $this->_totals;
    }

    /**
     * Add total data or model
     *
     * @param Total|array $total
     * @return $this
     */
    public function addTotal($total)
    {
        $addressTotal = null;
        if (is_array($total)) {
            /** @var Total $addressTotal */
            $addressTotal = $this->_addressTotalFactory->create(Total::class);
            $addressTotal->setData($total);
        } elseif ($total instanceof Total) {
            $addressTotal = $total;
        }

        if ($addressTotal !== null) {
            $addressTotal->setAddress($this);
            $this->_totals[$addressTotal->getCode()] = $addressTotal;
        }
        return $this;
    }

    /******************************* End Total Collector Interface *******************************************/

    /**
     * Rewrite clone method
     *
     * @return Address
     */
    public function __clone()
    {
        $this->setId(null);
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
    public function shippingRatesCollectionWasSet()
    {
        return null !== $this->_rates;
    }

    /**
     * Validate minimum amount
     *
     * @return bool
     */
    public function validateMinimumAmount()
    {
        $storeId = $this->getQuote()->getStoreId();
        $validateEnabled = $this->_scopeConfig->isSetFlag(
            'sales/minimum_order/active',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        if (!$validateEnabled) {
            return true;
        }

        if (!$this->getQuote()->getIsVirtual() xor $this->getAddressType() == self::TYPE_SHIPPING) {
            return true;
        }

        $includeDiscount = $this->_scopeConfig->getValue(
            'sales/minimum_order/include_discount_amount',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        $amount = $this->_scopeConfig->getValue(
            'sales/minimum_order/amount',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        $taxInclude = $this->_scopeConfig->getValue(
            'sales/minimum_order/tax_including',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        $taxes = $taxInclude
            ? $this->getBaseTaxAmount() + $this->getBaseDiscountTaxCompensationAmount()
            : 0;

        // Note: ($x > $y - 0.0001) means ($x >= $y) for floats
        return $includeDiscount ?
            ($this->getBaseSubtotalWithDiscount() + $taxes > $amount - 0.0001) :
            ($this->getBaseSubtotal() + $taxes > $amount - 0.0001);
    }

    /**
     * Retrieve applied taxes
     *
     * @return array
     */
    public function getAppliedTaxes()
    {
        $taxes = $this->getData('applied_taxes');
        return $taxes ? $this->serializer->unserialize($taxes) : [];
    }

    /**
     * Set applied taxes
     *
     * @param array $data
     * @return $this
     */
    public function setAppliedTaxes($data)
    {
        return $this->setData('applied_taxes', $this->serializer->serialize($data));
    }

    /******************************* Start Total Collector Interface *******************************************/

    /**
     * Set shipping amount
     *
     * @param float $value
     * @param bool $alreadyExclTax
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setShippingAmount($value, $alreadyExclTax = false)
    {
        return $this->setData('shipping_amount', $value);
    }

    /**
     * Set base shipping amount
     *
     * @param float $value
     * @param bool $alreadyExclTax
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setBaseShippingAmount($value, $alreadyExclTax = false)
    {
        return $this->setData('base_shipping_amount', $value);
    }

    /**
     * Set total amount value
     *
     * @param string $code
     * @param float $amount
     * @return $this
     */
    public function setTotalAmount($code, $amount)
    {
        $this->_totalAmounts[$code] = $amount;
        if ($code != 'subtotal') {
            $code = $code . '_amount';
        }
        $this->setData($code, $amount);

        return $this;
    }

    /**
     * Set total amount value in base store currency
     *
     * @param string $code
     * @param float $amount
     * @return $this
     */
    public function setBaseTotalAmount($code, $amount)
    {
        $this->_baseTotalAmounts[$code] = $amount;
        if ($code != 'subtotal') {
            $code = $code . '_amount';
        }
        $this->setData('base_' . $code, $amount);

        return $this;
    }

    /**
     * Add amount total amount value
     *
     * @param string $code
     * @param float $amount
     * @return $this
     */
    public function addTotalAmount($code, $amount)
    {
        $amount = $this->getTotalAmount($code) + $amount;
        $this->setTotalAmount($code, $amount);

        return $this;
    }

    /**
     * Add amount total amount value in base store currency
     *
     * @param string $code
     * @param float $amount
     * @return $this
     */
    public function addBaseTotalAmount($code, $amount)
    {
        $amount = $this->getBaseTotalAmount($code) + $amount;
        $this->setBaseTotalAmount($code, $amount);

        return $this;
    }

    /**
     * Get total amount value by code
     *
     * @param   string $code
     * @return  float|int
     */
    public function getTotalAmount($code)
    {
        if (isset($this->_totalAmounts[$code])) {
            return $this->_totalAmounts[$code];
        }

        return 0;
    }

    /**
     * Get total amount value by code in base store currency
     *
     * @param   string $code
     * @return  float|int
     */
    public function getBaseTotalAmount($code)
    {
        if (isset($this->_baseTotalAmounts[$code])) {
            return $this->_baseTotalAmounts[$code];
        }

        return 0;
    }

    /**
     * Get subtotal amount with applied discount in base currency
     *
     * @return float
     */
    public function getBaseSubtotalWithDiscount()
    {
        return $this->getBaseSubtotal() + $this->getBaseDiscountAmount() + $this->getBaseShippingDiscountAmount();
    }

    /**
     * Get subtotal amount with applied discount
     *
     * @return float
     */
    public function getSubtotalWithDiscount()
    {
        return $this->getSubtotal() + $this->getDiscountAmount();
    }

    //@codeCoverageIgnoreStart

    /**
     * Get all total amount values
     *
     * @return array
     */
    public function getAllTotalAmounts()
    {
        return $this->_totalAmounts;
    }

    /**
     * Get all total amount values in base currency
     *
     * @return array
     */
    public function getAllBaseTotalAmounts()
    {
        return $this->_baseTotalAmounts;
    }

    /******************************* End Total Collector Interface *******************************************/

    /**
     * @inheritdoc
     */
    protected function _getValidationRulesBeforeSave()
    {
        return $this->validator;
    }

    /**
     * @inheritdoc
     */
    public function getCountryId()
    {
        return $this->getData(self::KEY_COUNTRY_ID);
    }

    /**
     * @inheritdoc
     */
    public function setCountryId($countryId)
    {
        return $this->setData(self::KEY_COUNTRY_ID, $countryId);
    }

    /**
     * @inheritdoc
     */
    public function getStreet()
    {
        $street = $this->getData(self::KEY_STREET) ?? [''];

        return is_array($street) ? $street : explode("\n", $street);
    }

    /**
     * @inheritdoc
     */
    public function setStreet($street)
    {
        return $this->setData(self::KEY_STREET, $street);
    }

    /**
     * @inheritdoc
     */
    public function getCompany()
    {
        return $this->getData(self::KEY_COMPANY);
    }

    /**
     * @inheritdoc
     */
    public function setCompany($company)
    {
        return $this->setData(self::KEY_COMPANY, $company);
    }

    /**
     * @inheritdoc
     */
    public function getTelephone()
    {
        return $this->getData(self::KEY_TELEPHONE);
    }

    /**
     * @inheritdoc
     */
    public function setTelephone($telephone)
    {
        return $this->setData(self::KEY_TELEPHONE, $telephone);
    }

    /**
     * @inheritdoc
     */
    public function getFax()
    {
        return $this->getData(self::KEY_FAX);
    }

    /**
     * @inheritdoc
     */
    public function setFax($fax)
    {
        return $this->setData(self::KEY_FAX, $fax);
    }

    /**
     * @inheritdoc
     */
    public function getPostcode()
    {
        return $this->getData(self::KEY_POSTCODE);
    }

    /**
     * @inheritdoc
     */
    public function setPostcode($postcode)
    {
        return $this->setData(self::KEY_POSTCODE, $postcode);
    }

    /**
     * @inheritdoc
     */
    public function getCity()
    {
        return $this->getData(self::KEY_CITY);
    }

    /**
     * @inheritdoc
     */
    public function setCity($city)
    {
        return $this->setData(self::KEY_CITY, $city);
    }

    /**
     * @inheritdoc
     */
    public function getFirstname()
    {
        return $this->getData(self::KEY_FIRSTNAME);
    }

    /**
     * @inheritdoc
     */
    public function setFirstname($firstname)
    {
        return $this->setData(self::KEY_FIRSTNAME, $firstname);
    }

    /**
     * @inheritdoc
     */
    public function getLastname()
    {
        return $this->getData(self::KEY_LASTNAME);
    }

    /**
     * @inheritdoc
     */
    public function setLastname($lastname)
    {
        return $this->setData(self::KEY_LASTNAME, $lastname);
    }

    /**
     * @inheritdoc
     */
    public function getMiddlename()
    {
        return $this->getData(self::KEY_MIDDLENAME);
    }

    /**
     * @inheritdoc
     */
    public function setMiddlename($middlename)
    {
        return $this->setData(self::KEY_MIDDLENAME, $middlename);
    }

    /**
     * @inheritdoc
     */
    public function getPrefix()
    {
        return $this->getData(self::KEY_PREFIX);
    }

    /**
     * @inheritdoc
     */
    public function setPrefix($prefix)
    {
        return $this->setData(self::KEY_PREFIX, $prefix);
    }

    /**
     * @inheritdoc
     */
    public function getSuffix()
    {
        return $this->getData(self::KEY_SUFFIX);
    }

    /**
     * @inheritdoc
     */
    public function setSuffix($suffix)
    {
        return $this->setData(self::KEY_SUFFIX, $suffix);
    }

    /**
     * @inheritdoc
     */
    public function getVatId()
    {
        return $this->getData(self::KEY_VAT_ID);
    }

    /**
     * @inheritdoc
     */
    public function setVatId($vatId)
    {
        return $this->setData(self::KEY_VAT_ID, $vatId);
    }

    /**
     * @inheritdoc
     */
    public function getCustomerId()
    {
        return $this->getData(self::KEY_CUSTOMER_ID);
    }

    /**
     * @inheritdoc
     */
    public function setCustomerId($customerId)
    {
        return $this->setData(self::KEY_CUSTOMER_ID, $customerId);
    }

    /**
     * @inheritdoc
     */
    public function getEmail()
    {
        $email = $this->getData(self::KEY_EMAIL);
        if ($this->getQuote() && !$email) {
            $email = $this->getQuote()->getCustomerEmail();
            $this->setEmail($email);
        }
        return $email;
    }

    /**
     * @inheritdoc
     */
    public function setEmail($email)
    {
        return $this->setData(self::KEY_EMAIL, $email);
    }

    /**
     * @inheritdoc
     */
    public function setRegion($region)
    {
        return $this->setData(self::KEY_REGION, $region);
    }

    /**
     * @inheritdoc
     */
    public function setRegionId($regionId)
    {
        return $this->setData(self::KEY_REGION_ID, $regionId);
    }

    /**
     * @inheritdoc
     */
    public function setRegionCode($regionCode)
    {
        return $this->setData(self::KEY_REGION_CODE, $regionCode);
    }

    /**
     * @inheritdoc
     */
    public function getSameAsBilling()
    {
        return $this->getData(self::SAME_AS_BILLING);
    }

    /**
     * @inheritdoc
     */
    public function setSameAsBilling($sameAsBilling)
    {
        return $this->setData(self::SAME_AS_BILLING, $sameAsBilling);
    }

    /**
     * @inheritdoc
     */
    public function getCustomerAddressId()
    {
        return $this->getData(self::CUSTOMER_ADDRESS_ID);
    }

    /**
     * @inheritdoc
     */
    public function setCustomerAddressId($customerAddressId)
    {
        return $this->setData(self::CUSTOMER_ADDRESS_ID, $customerAddressId);
    }

    /**
     * Get save in address book flag
     *
     * @return int|null
     */
    public function getSaveInAddressBook()
    {
        return $this->getData(self::SAVE_IN_ADDRESS_BOOK);
    }

    /**
     * Set save in address book flag
     *
     * @param int|null $saveInAddressBook
     * @return $this
     */
    public function setSaveInAddressBook($saveInAddressBook)
    {
        return $this->setData(self::SAVE_IN_ADDRESS_BOOK, $saveInAddressBook);
    }

    //@codeCoverageIgnoreEnd

    /**
     * @inheritdoc
     *
     * @return AddressExtensionInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * @inheritdoc
     *
     * @param AddressExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(AddressExtensionInterface $extensionAttributes)
    {
        return $this->_setExtensionAttributes($extensionAttributes);
    }

    /**
     * Shipping method
     *
     * @return string
     */
    public function getShippingMethod()
    {
        return $this->getData('shipping_method');
    }

    /**
     * @inheritdoc
     */
    protected function getCustomAttributesCodes()
    {
        return array_keys($this->attributeList->getAttributes());
    }

    /**
     * Realization of the actual set method to boost performance
     *
     * @param float $value
     * @return $this
     */
    public function setBaseDiscountAmount(float $value)
    {
        $this->_data[self::BASE_DISCOUNT_AMOUNT] = $value;

        return $this;
    }
}
