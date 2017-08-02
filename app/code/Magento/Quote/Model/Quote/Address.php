<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model\Quote;

use Magento\Customer\Api\AddressMetadataInterface;
use Magento\Customer\Api\Data\AddressInterfaceFactory;
use Magento\Customer\Api\Data\RegionInterfaceFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Serialize\Serializer\Json;
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
 * @method \Magento\Customer\Api\Data\AddressInterface getCustomerAddress()
 * @method Address setCustomerAddressData(\Magento\Customer\Api\Data\AddressInterface $value)
 * @method string getAddressType()
 * @method Address setAddressType(string $value)
 * @method int getFreeShipping()
 * @method Address setFreeShipping(int $value)
 * @method int getCollectShippingRates()
 * @method Address setCollectShippingRates(int $value)
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
 * @method Address setBaseDiscountAmount(float $value)
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
 * @property $_objectCopyService \Magento\Framework\DataObject\Copy
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 2.0.0
 */
class Address extends \Magento\Customer\Model\Address\AbstractAddress implements
    \Magento\Quote\Api\Data\AddressInterface
{
    const RATES_FETCH = 1;

    const RATES_RECALCULATE = 2;

    const ADDRESS_TYPE_BILLING = 'billing';

    const ADDRESS_TYPE_SHIPPING = 'shipping';

    /**
     * Prefix of model events
     *
     * @var string
     * @since 2.0.0
     */
    protected $_eventPrefix = 'sales_quote_address';

    /**
     * Name of event object
     *
     * @var string
     * @since 2.0.0
     */
    protected $_eventObject = 'quote_address';

    /**
     * Quote object
     *
     * @var \Magento\Quote\Model\Quote
     * @since 2.0.0
     */
    protected $_items;

    /**
     * Quote object
     *
     * @var \Magento\Quote\Model\Quote
     * @since 2.0.0
     */
    protected $_quote;

    /**
     * Sales Quote address rates
     *
     * @var \Magento\Quote\Model\Quote\Address\Rate
     * @since 2.0.0
     */
    protected $_rates;

    /**
     * Total models collector
     *
     * @var \Magento\Quote\Model\Quote\Address\Total\Collector
     * @since 2.0.0
     */
    protected $_totalCollector;

    /**
     * Total data as array
     *
     * @var array
     * @since 2.0.0
     */
    protected $_totals = [];

    /**
     * @var array
     * @since 2.0.0
     */
    protected $_totalAmounts = [];

    /**
     * @var array
     * @since 2.0.0
     */
    protected $_baseTotalAmounts = [];

    /**
     * Core store config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     * @since 2.0.0
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Quote\Model\Quote\Address\ItemFactory
     * @since 2.0.0
     */
    protected $_addressItemFactory;

    /**
     * @var \Magento\Quote\Model\ResourceModel\Quote\Address\Item\CollectionFactory
     * @since 2.0.0
     */
    protected $_itemCollectionFactory;

    /**
     * @var \Magento\Quote\Model\Quote\Address\RateCollectorInterfaceFactory
     * @since 2.0.0
     */
    protected $_rateCollector;

    /**
     * @var \Magento\Quote\Model\ResourceModel\Quote\Address\Rate\CollectionFactory
     * @since 2.0.0
     */
    protected $_rateCollectionFactory;

    /**
     * @var \Magento\Quote\Model\Quote\Address\Total\CollectorFactory
     * @since 2.0.0
     */
    protected $_totalCollectorFactory;

    /**
     * @var \Magento\Quote\Model\Quote\Address\TotalFactory
     * @since 2.0.0
     */
    protected $_addressTotalFactory;

    /**
     * @var \Magento\Quote\Model\Quote\Address\RateFactory
     * @since 2.2.0
     */
    protected $_addressRateFactory;

    /**
     * @var \Magento\Customer\Api\Data\AddressInterfaceFactory
     * @since 2.0.0
     */
    protected $addressDataFactory;

    /**
     * @var Address\Validator
     * @since 2.0.0
     */
    protected $validator;

    /**
     * @var \Magento\Customer\Model\Address\Mapper
     * @since 2.0.0
     */
    protected $addressMapper;

    /**
     * @var Address\RateRequestFactory
     * @since 2.0.0
     */
    protected $_rateRequestFactory;

    /**
     * @var Address\CustomAttributeListInterface
     * @since 2.0.0
     */
    protected $attributeList;

    /**
     * @var TotalsCollector
     * @since 2.0.0
     */
    protected $totalsCollector;

    /**
     * @var \Magento\Quote\Model\Quote\TotalsReader
     * @since 2.0.0
     */
    protected $totalsReader;

    /**
     * @var Json
     * @since 2.2.0
     */
    private $serializer;

    /**
     * @var StoreManagerInterface
     * @since 2.2.0
     */
    private $storeManager;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory
     * @param \Magento\Directory\Helper\Data $directoryData
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Customer\Model\Address\Config $addressConfig
     * @param \Magento\Directory\Model\RegionFactory $regionFactory
     * @param \Magento\Directory\Model\CountryFactory $countryFactory
     * @param AddressMetadataInterface $metadataService
     * @param AddressInterfaceFactory $addressDataFactory
     * @param RegionInterfaceFactory $regionDataFactory
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param Address\ItemFactory $addressItemFactory
     * @param \Magento\Quote\Model\ResourceModel\Quote\Address\Item\CollectionFactory $itemCollectionFactory
     * @param \Magento\Quote\Model\Quote\Address\RateFactory $addressRateFactory
     * @param Address\RateCollectorInterfaceFactory $rateCollector
     * @param \Magento\Quote\Model\ResourceModel\Quote\Address\Rate\CollectionFactory $rateCollectionFactory
     * @param Address\RateRequestFactory $rateRequestFactory
     * @param Address\Total\CollectorFactory $totalCollectorFactory
     * @param Address\TotalFactory $addressTotalFactory
     * @param \Magento\Framework\DataObject\Copy $objectCopyService
     * @param \Magento\Shipping\Model\CarrierFactoryInterface $carrierFactory
     * @param Address\Validator $validator
     * @param \Magento\Customer\Model\Address\Mapper $addressMapper
     * @param Address\CustomAttributeListInterface $attributeList
     * @param TotalsCollector $totalsCollector
     * @param \Magento\Quote\Model\Quote\TotalsReader $totalsReader
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     * @param Json $serializer
     * @param StoreManagerInterface $storeManager
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Directory\Helper\Data $directoryData,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Customer\Model\Address\Config $addressConfig,
        \Magento\Directory\Model\RegionFactory $regionFactory,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        AddressMetadataInterface $metadataService,
        AddressInterfaceFactory $addressDataFactory,
        RegionInterfaceFactory $regionDataFactory,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Quote\Model\Quote\Address\ItemFactory $addressItemFactory,
        \Magento\Quote\Model\ResourceModel\Quote\Address\Item\CollectionFactory $itemCollectionFactory,
        \Magento\Quote\Model\Quote\Address\RateFactory $addressRateFactory,
        \Magento\Quote\Model\Quote\Address\RateCollectorInterfaceFactory $rateCollector,
        \Magento\Quote\Model\ResourceModel\Quote\Address\Rate\CollectionFactory $rateCollectionFactory,
        \Magento\Quote\Model\Quote\Address\RateRequestFactory $rateRequestFactory,
        \Magento\Quote\Model\Quote\Address\Total\CollectorFactory $totalCollectorFactory,
        \Magento\Quote\Model\Quote\Address\TotalFactory $addressTotalFactory,
        \Magento\Framework\DataObject\Copy $objectCopyService,
        \Magento\Shipping\Model\CarrierFactoryInterface $carrierFactory,
        Address\Validator $validator,
        \Magento\Customer\Model\Address\Mapper $addressMapper,
        Address\CustomAttributeListInterface $attributeList,
        \Magento\Quote\Model\Quote\TotalsCollector $totalsCollector,
        \Magento\Quote\Model\Quote\TotalsReader $totalsReader,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = [],
        Json $serializer = null,
        StoreManagerInterface $storeManager = null
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
        $this->_objectCopyService = $objectCopyService;
        $this->_carrierFactory = $carrierFactory;
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
            $data
        );
    }

    /**
     * Initialize resource
     *
     * @return void
     * @since 2.0.0
     */
    protected function _construct()
    {
        $this->_init(\Magento\Quote\Model\ResourceModel\Quote\Address::class);
    }

    /**
     * Initialize quote identifier before save
     *
     * @return $this
     * @since 2.0.0
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
     * @since 2.0.0
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

            if (!$this->getId()) {
                $this->setSameAsBilling((int)$this->_isSameAsBilling());
            }
        }
    }

    /**
     * Returns true if shipping address is same as billing
     *
     * @return bool
     * @since 2.0.0
     */
    protected function _isSameAsBilling()
    {
        return $this->getAddressType() == \Magento\Quote\Model\Quote\Address::TYPE_SHIPPING &&
            ($this->_isNotRegisteredCustomer() ||
            $this->_isDefaultShippingNullOrSameAsBillingAddress());
    }

    /**
     * Checks if the user is a registered customer
     *
     * @return bool
     * @since 2.0.0
     */
    protected function _isNotRegisteredCustomer()
    {
        return !$this->getQuote()->getCustomerId() || $this->getCustomerAddressId() === null;
    }

    /**
     * Returns true if shipping address is same as billing or it is undefined
     *
     * @return bool
     * @since 2.0.0
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
     * @param   \Magento\Quote\Model\Quote $quote
     * @return $this
     * @since 2.0.0
     */
    public function setQuote(\Magento\Quote\Model\Quote $quote)
    {
        $this->_quote = $quote;
        $this->setQuoteId($quote->getId());
        return $this;
    }

    /**
     * Retrieve quote object
     *
     * @return \Magento\Quote\Model\Quote
     * @since 2.0.0
     */
    public function getQuote()
    {
        return $this->_quote;
    }

    /**
     * Import quote address data from customer address Data Object.
     *
     * @param \Magento\Customer\Api\Data\AddressInterface $address
     * @return $this
     * @since 2.0.0
     */
    public function importCustomerAddressData(\Magento\Customer\Api\Data\AddressInterface $address)
    {
        $this->_objectCopyService->copyFieldsetToTarget(
            'customer_address',
            'to_quote_address',
            $this->addressMapper->toFlatArray($address),
            $this
        );

        $quote = $this->getQuote();
        if ($address->getCustomerId() && (!empty($quote) && $address->getCustomerId() == $quote->getCustomerId())) {
            $customer = $quote->getCustomer();
            $this->setEmail($customer->getEmail());
        }
        return $this;
    }

    /**
     * Export data to customer address Data Object.
     *
     * @return \Magento\Customer\Api\Data\AddressInterface
     * @since 2.0.0
     */
    public function exportCustomerAddress()
    {
        $customerAddressData = $this->_objectCopyService->getDataFromFieldset(
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
            \Magento\Customer\Api\Data\AddressInterface::class
        );
        return $addressDataObject;
    }

    /**
     * Convert object to array
     *
     * @param   array $arrAttributes
     * @return  array
     * @since 2.0.0
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
     * @return \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function getAllItems()
    {
        // We calculate item list once and cache it in three arrays - all items
        $key = 'cached_items_all';
        if (!$this->hasData($key)) {
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
            $this->setData('cached_items_all', $items);
        }

        $items = $this->getData($key);

        return $items;
    }

    /**
     * Retrieve all visible items
     *
     * @return array
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function hasItems()
    {
        return sizeof($this->getAllItems()) > 0;
    }

    /**
     * Get address item object by id without
     *
     * @param int $itemId
     * @return \Magento\Quote\Model\Quote\Address\Item|false
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $item
     * @param int $qty
     * @return $this
     * @since 2.0.0
     */
    public function addItem(\Magento\Quote\Model\Quote\Item\AbstractItem $item, $qty = null)
    {
        if ($item instanceof \Magento\Quote\Model\Quote\Item) {
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
     * @return \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function getGroupedAllShippingRates()
    {
        $rates = [];
        foreach ($this->getShippingRatesCollection() as $rate) {
            if (!$rate->isDeleted() && $this->_carrierFactory->get($rate->getCarrier())) {
                if (!isset($rates[$rate->getCarrier()])) {
                    $rates[$rate->getCarrier()] = [];
                }

                $rates[$rate->getCarrier()][] = $rate;
                $rates[$rate->getCarrier()][0]->carrier_sort_order = $this->_carrierFactory->get(
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
     * @since 2.0.0
     */
    protected function _sortRates($firstItem, $secondItem)
    {
        if ((int)$firstItem[0]->carrier_sort_order < (int)$secondItem[0]->carrier_sort_order) {
            return -1;
        } elseif ((int)$firstItem[0]->carrier_sort_order > (int)$secondItem[0]->carrier_sort_order) {
            return 1;
        } else {
            return 0;
        }
    }

    /**
     * Retrieve shipping rate by identifier
     *
     * @param   int $rateId
     * @return  \Magento\Quote\Model\Quote\Address\Rate|false
     * @since 2.0.0
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
     * @return  \Magento\Quote\Model\Quote\Address\Rate|false
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @param \Magento\Quote\Model\Quote\Address\Rate $rate
     * @return $this
     * @since 2.0.0
     */
    public function addShippingRate(\Magento\Quote\Model\Quote\Address\Rate $rate)
    {
        $rate->setAddress($this);
        $this->getShippingRatesCollection()->addItem($rate);

        return $this;
    }

    /**
     * Collecting shipping rates by address
     *
     * @return $this
     * @since 2.0.0
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
     * Returns true if current selected shipping method code corresponds to one of the found rates
     *
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $item
     * @return bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @since 2.0.0
     */
    public function requestShippingRates(\Magento\Quote\Model\Quote\Item\AbstractItem $item = null)
    {
        /** @var $request \Magento\Quote\Model\Quote\Address\RateRequest */
        $request = $this->_rateRequestFactory->create();
        $request->setAllItems($item ? [$item] : $this->getAllItems());
        $request->setDestCountryId($this->getCountryId());
        $request->setDestRegionId($this->getRegionId());
        $request->setDestRegionCode($this->getRegionCode());
        $request->setDestStreet($this->getStreetFull());
        $request->setDestCity($this->getCity());
        $request->setDestPostcode($this->getPostcode());
        $request->setPackageValue($item ? $item->getBaseRowTotal() : $this->getBaseSubtotal());
        $packageWithDiscount = $item ? $item->getBaseRowTotal() -
            $item->getBaseDiscountAmount() : $this->getBaseSubtotalWithDiscount();
        $request->setPackageValueWithDiscount($packageWithDiscount);
        $request->setPackageWeight($item ? $item->getRowWeight() : $this->getWeight());
        $request->setPackageQty($item ? $item->getQty() : $this->getItemQty());

        /**
         * Need for shipping methods that use insurance based on price of physical products
         */
        $packagePhysicalValue = $item ? $item->getBaseRowTotal() : $this->getBaseSubtotal() -
            $this->getBaseVirtualAmount();
        $request->setPackagePhysicalValue($packagePhysicalValue);

        $request->setFreeMethodWeight($item ? 0 : $this->getFreeMethodWeight());

        /**
         * Store and website identifiers specified from StoreManager
         */
        $request->setStoreId($this->storeManager->getStore()->getId());
        $request->setWebsiteId($this->storeManager->getWebsite()->getId());
        $request->setFreeShipping($this->getFreeShipping());
        /**
         * Currencies need to convert in free shipping
         */
        $request->setBaseCurrency($this->storeManager->getStore()->getBaseCurrency());
        $request->setPackageCurrency($this->storeManager->getStore()->getCurrentCurrency());
        $request->setLimitCarrier($this->getLimitCarrier());
        $baseSubtotalInclTax = $this->getBaseSubtotalTotalInclTax();
        $request->setBaseSubtotalInclTax($baseSubtotalInclTax);

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

                        /** @var \Magento\Store\Api\Data\StoreInterface */
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
     * @since 2.0.0
     */
    public function getTotals()
    {
        $totalsData = array_merge($this->getData(), ['address_quote_items' => $this->getAllItems()]);
        $totals = $this->totalsReader->fetch($this->getQuote(), $totalsData);
        foreach ($totals as $total) {
            $this->addTotal($total);
        }

        return $this->_totals;
    }

    /**
     * Add total data or model
     *
     * @param \Magento\Quote\Model\Quote\Address\Total|array $total
     * @return $this
     * @since 2.0.0
     */
    public function addTotal($total)
    {
        $addressTotal = null;
        if (is_array($total)) {
            /** @var \Magento\Quote\Model\Quote\Address\Total $addressTotal */
            $addressTotal = $this->_addressTotalFactory->create(\Magento\Quote\Model\Quote\Address\Total::class);
            $addressTotal->setData($total);
        } elseif ($total instanceof \Magento\Quote\Model\Quote\Address\Total) {
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
     * @return \Magento\Quote\Model\Quote\Address
     * @since 2.0.0
     */
    public function __clone()
    {
        $this->setId(null);
    }

    /**
     * Checks if it was set
     *
     * @return bool
     * @since 2.0.0
     */
    public function itemsCollectionWasSet()
    {
        return null !== $this->_items;
    }

    /**
     * Checks if it was set
     *
     * @return bool
     * @since 2.0.0
     */
    public function shippingRatesCollectionWasSet()
    {
        return null !== $this->_rates;
    }

    /**
     * Validate minimum amount
     *
     * @return bool
     * @since 2.0.0
     */
    public function validateMinimumAmount()
    {
        $storeId = $this->getQuote()->getStoreId();
        $validateEnabled = $this->_scopeConfig->isSetFlag(
            'sales/minimum_order/active',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
        if (!$validateEnabled) {
            return true;
        }

        if (!$this->getQuote()->getIsVirtual() xor $this->getAddressType() == self::TYPE_SHIPPING) {
            return true;
        }

        $amount = $this->_scopeConfig->getValue(
            'sales/minimum_order/amount',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
        $taxInclude = $this->_scopeConfig->getValue(
            'sales/minimum_order/tax_including',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
        $taxes = $taxInclude ? $this->getBaseTaxAmount() : 0;

        return ($this->getBaseSubtotalWithDiscount() + $taxes >= $amount);
    }

    /**
     * Retrieve applied taxes
     *
     * @return array
     * @since 2.0.0
     */
    public function getAppliedTaxes()
    {
        return $this->serializer->unserialize($this->getData('applied_taxes'));
    }

    /**
     * Set applied taxes
     *
     * @param array $data
     * @return $this
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function setBaseShippingAmount($value, $alreadyExclTax = false)
    {
        return $this->setData('base_shipping_amount', $value);
    }

    /**
     * Set total amount value
     *
     * @param   string $code
     * @param   float $amount
     * @return $this
     * @since 2.0.0
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
     * @param   string $code
     * @param   float $amount
     * @return $this
     * @since 2.0.0
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
     * @param   string $code
     * @param   float $amount
     * @return $this
     * @since 2.0.0
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
     * @param   string $code
     * @param   float $amount
     * @return $this
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function getBaseSubtotalWithDiscount()
    {
        return $this->getBaseSubtotal() + $this->getBaseDiscountAmount();
    }

    /**
     * Get subtotal amount with applied discount
     *
     * @return float
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function getAllTotalAmounts()
    {
        return $this->_totalAmounts;
    }

    /**
     * Get all total amount values in base currency
     *
     * @return array
     * @since 2.0.0
     */
    public function getAllBaseTotalAmounts()
    {
        return $this->_baseTotalAmounts;
    }

    /******************************* End Total Collector Interface *******************************************/

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    protected function _getValidationRulesBeforeSave()
    {
        return $this->validator;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getCountryId()
    {
        return $this->getData(self::KEY_COUNTRY_ID);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setCountryId($countryId)
    {
        return $this->setData(self::KEY_COUNTRY_ID, $countryId);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getStreet()
    {
        $street = $this->getData(self::KEY_STREET);
        return is_array($street) ? $street : explode("\n", $street);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setStreet($street)
    {
        return $this->setData(self::KEY_STREET, $street);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getCompany()
    {
        return $this->getData(self::KEY_COMPANY);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setCompany($company)
    {
        return $this->setData(self::KEY_COMPANY, $company);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getTelephone()
    {
        return $this->getData(self::KEY_TELEPHONE);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setTelephone($telephone)
    {
        return $this->setData(self::KEY_TELEPHONE, $telephone);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getFax()
    {
        return $this->getData(self::KEY_FAX);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setFax($fax)
    {
        return $this->setData(self::KEY_FAX, $fax);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getPostcode()
    {
        return $this->getData(self::KEY_POSTCODE);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setPostcode($postcode)
    {
        return $this->setData(self::KEY_POSTCODE, $postcode);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getCity()
    {
        return $this->getData(self::KEY_CITY);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setCity($city)
    {
        return $this->setData(self::KEY_CITY, $city);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getFirstname()
    {
        return $this->getData(self::KEY_FIRSTNAME);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setFirstname($firstname)
    {
        return $this->setData(self::KEY_FIRSTNAME, $firstname);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getLastname()
    {
        return $this->getData(self::KEY_LASTNAME);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setLastname($lastname)
    {
        return $this->setData(self::KEY_LASTNAME, $lastname);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getMiddlename()
    {
        return $this->getData(self::KEY_MIDDLENAME);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setMiddlename($middlename)
    {
        return $this->setData(self::KEY_MIDDLENAME, $middlename);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getPrefix()
    {
        return $this->getData(self::KEY_PREFIX);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setPrefix($prefix)
    {
        return $this->setData(self::KEY_PREFIX, $prefix);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getSuffix()
    {
        return $this->getData(self::KEY_SUFFIX);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setSuffix($suffix)
    {
        return $this->setData(self::KEY_SUFFIX, $suffix);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getVatId()
    {
        return $this->getData(self::KEY_VAT_ID);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setVatId($vatId)
    {
        return $this->setData(self::KEY_VAT_ID, $vatId);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getCustomerId()
    {
        return $this->getData(self::KEY_CUSTOMER_ID);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setCustomerId($customerId)
    {
        return $this->setData(self::KEY_CUSTOMER_ID, $customerId);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getEmail()
    {
        $email = $this->getData(self::KEY_EMAIL);
        if (!$email && $this->getQuote()) {
            $email = $this->getQuote()->getCustomerEmail();
            $this->setEmail($email);
        }
        return $email;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setEmail($email)
    {
        return $this->setData(self::KEY_EMAIL, $email);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setRegion($region)
    {
        return $this->setData(self::KEY_REGION, $region);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setRegionId($regionId)
    {
        return $this->setData(self::KEY_REGION_ID, $regionId);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setRegionCode($regionCode)
    {
        return $this->setData(self::KEY_REGION_CODE, $regionCode);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getSameAsBilling()
    {
        return $this->getData(self::SAME_AS_BILLING);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setSameAsBilling($sameAsBilling)
    {
        return $this->setData(self::SAME_AS_BILLING, $sameAsBilling);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getCustomerAddressId()
    {
        return $this->getData(self::CUSTOMER_ADDRESS_ID);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setCustomerAddressId($customerAddressId)
    {
        return $this->setData(self::CUSTOMER_ADDRESS_ID, $customerAddressId);
    }

    /**
     * Get save in address book flag
     *
     * @return int|null
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function setSaveInAddressBook($saveInAddressBook)
    {
        return $this->setData(self::SAVE_IN_ADDRESS_BOOK, $saveInAddressBook);
    }

    //@codeCoverageIgnoreEnd

    /**
     * {@inheritdoc}
     *
     * @return \Magento\Quote\Api\Data\AddressExtensionInterface|null
     * @since 2.0.0
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * {@inheritdoc}
     *
     * @param \Magento\Quote\Api\Data\AddressExtensionInterface $extensionAttributes
     * @return $this
     * @since 2.0.0
     */
    public function setExtensionAttributes(\Magento\Quote\Api\Data\AddressExtensionInterface $extensionAttributes)
    {
        return $this->_setExtensionAttributes($extensionAttributes);
    }

    /**
     * Shipping method
     *
     * @return string
     * @since 2.0.0
     */
    public function getShippingMethod()
    {
        return $this->getData('shipping_method');
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    protected function getCustomAttributesCodes()
    {
        return array_keys($this->attributeList->getAttributes());
    }
}
