<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Quote;

use Magento\Customer\Api\AddressMetadataInterface;
use Magento\Customer\Api\Data\AddressDataBuilder;
use Magento\Customer\Api\Data\RegionDataBuilder;
use Magento\Framework\Api\AttributeDataBuilder;

/**
 * Sales Quote address model
 *
 * @method int getQuoteId()
 * @method Address setQuoteId(int $value)
 * @method string getCreatedAt()
 * @method Address setCreatedAt(string $value)
 * @method string getUpdatedAt()
 * @method Address setUpdatedAt(string $value)
 * @method int getCustomerId()
 * @method Address setCustomerId(int $value)
 * @method int getSaveInAddressBook()
 * @method Address setSaveInAddressBook(int $value)
 * @method int getCustomerAddressId()
 * @method Address setCustomerAddressId(int $value)
 * @method \Magento\Customer\Api\Data\AddressInterface getCustomerAddress()
 * @method Address setCustomerAddressData(\Magento\Customer\Api\Data\AddressInterface $value)
 * @method string getAddressType()
 * @method Address setAddressType(string $value)
 * @method string getEmail()
 * @method Address setEmail(string $value)
 * @method Address setPrefix(string $value)
 * @method Address setFirstname(string $value)
 * @method Address setMiddlename(string $value)
 * @method Address setLastname(string $value)
 * @method Address setSuffix(string $value)
 * @method string getCompany()
 * @method Address setCompany(string $value)
 * @method Address setCity(string $value)
 * @method Address setRegion(string $value)
 * @method Address setRegionId(int $value)
 * @method Address setPostcode(string $value)
 * @method Address setCountryId(string $value)
 * @method Address setTelephone(string $value)
 * @method string getFax()
 * @method Address setFax(string $value)
 * @method int getSameAsBilling()
 * @method Address setSameAsBilling(int $value)
 * @method int getFreeShipping()
 * @method Address setFreeShipping(int $value)
 * @method int getCollectShippingRates()
 * @method Address setCollectShippingRates(int $value)
 * @method string getShippingMethod()
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
 * @method float getHiddenTaxAmount()
 * @method Address setHiddenTaxAmount(float $value)
 * @method float getBaseHiddenTaxAmount()
 * @method Address setBaseHiddenTaxAmount(float $value)
 * @method float getShippingHiddenTaxAmount()
 * @method Address setShippingHiddenTaxAmount(float $value)
 * @method float getBaseShippingHiddenTaxAmnt()
 * @method Address setBaseShippingHiddenTaxAmnt(float $value)
 * @method float getShippingInclTax()
 * @method Address setShippingInclTax(float $value)
 * @method float getBaseShippingInclTax()
 * @method \Magento\SalesRule\Model\Rule[] getCartFixedRules()
 * @method int[] getAppliedRuleIds()
 * @method Address setBaseShippingInclTax(float $value)
 */
class Address extends \Magento\Customer\Model\Address\AbstractAddress
{
    const RATES_FETCH = 1;

    const RATES_RECALCULATE = 2;

    const ADDRESS_TYPE_BILLING = 'billing';

    const ADDRESS_TYPE_SHIPPING = 'shipping';

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
     * @var \Magento\Sales\Model\Quote
     */
    protected $_items;

    /**
     * Quote object
     *
     * @var \Magento\Sales\Model\Quote
     */
    protected $_quote;

    /**
     * Sales Quote address rates
     *
     * @var \Magento\Sales\Model\Quote\Address\Rate
     */
    protected $_rates;

    /**
     * Total models collector
     *
     * @var \Magento\Sales\Model\Quote\Address\Total\Collector
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
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Sales\Model\Quote\Address\ItemFactory
     */
    protected $_addressItemFactory;

    /**
     * @var \Magento\Sales\Model\Resource\Quote\Address\Item\CollectionFactory
     */
    protected $_itemCollectionFactory;

    /**
     * @var \Magento\Sales\Model\Quote\Address\RateCollectorInterfaceFactory
     */
    protected $_rateCollector;

    /**
     * @var \Magento\Sales\Model\Resource\Quote\Address\Rate\CollectionFactory
     */
    protected $_rateCollectionFactory;

    /**
     * @var \Magento\Sales\Model\Quote\Address\Total\CollectorFactory
     */
    protected $_totalCollectorFactory;

    /**
     * @var \Magento\Sales\Model\Quote\Address\TotalFactory
     */
    protected $_addressTotalFactory;

    /**
     * @var \Magento\Customer\Api\Data\AddressDataBuilder
     */
    protected $addressBuilder;

    /**
     * @var Address\Validator
     */
    protected $validator;

    /**
     * @var \Magento\Customer\Model\Address\Mapper
     */
    protected $addressMapper;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\MetadataServiceInterface $metadataService
     * @param AttributeDataBuilder $customAttributeBuilder
     * @param \Magento\Directory\Helper\Data $directoryData
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Customer\Model\Address\Config $addressConfig
     * @param \Magento\Directory\Model\RegionFactory $regionFactory
     * @param \Magento\Directory\Model\CountryFactory $countryFactory
     * @param AddressMetadataInterface $addressMetadataService
     * @param AddressDataBuilder $addressBuilder
     * @param RegionDataBuilder $regionBuilder
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param Address\ItemFactory $addressItemFactory
     * @param \Magento\Sales\Model\Resource\Quote\Address\Item\CollectionFactory $itemCollectionFactory
     * @param Address\RateFactory $addressRateFactory
     * @param Address\RateCollectorInterfaceFactory $rateCollector
     * @param \Magento\Sales\Model\Resource\Quote\Address\Rate\CollectionFactory $rateCollectionFactory
     * @param Address\RateRequestFactory $rateRequestFactory
     * @param Address\Total\CollectorFactory $totalCollectorFactory
     * @param Address\TotalFactory $addressTotalFactory
     * @param \Magento\Framework\Object\Copy $objectCopyService
     * @param Address\CarrierFactoryInterface $carrierFactory
     * @param Address\Validator $validator
     * @param \Magento\Customer\Model\Address\Mapper $addressMapper
     * @param \Magento\Framework\Model\Resource\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\MetadataServiceInterface $metadataService,
        AttributeDataBuilder $customAttributeBuilder,
        \Magento\Directory\Helper\Data $directoryData,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Customer\Model\Address\Config $addressConfig,
        \Magento\Directory\Model\RegionFactory $regionFactory,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        AddressMetadataInterface $addressMetadataService,
        AddressDataBuilder $addressBuilder,
        RegionDataBuilder $regionBuilder,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Sales\Model\Quote\Address\ItemFactory $addressItemFactory,
        \Magento\Sales\Model\Resource\Quote\Address\Item\CollectionFactory $itemCollectionFactory,
        \Magento\Sales\Model\Quote\Address\RateFactory $addressRateFactory,
        \Magento\Sales\Model\Quote\Address\RateCollectorInterfaceFactory $rateCollector,
        \Magento\Sales\Model\Resource\Quote\Address\Rate\CollectionFactory $rateCollectionFactory,
        \Magento\Sales\Model\Quote\Address\RateRequestFactory $rateRequestFactory,
        \Magento\Sales\Model\Quote\Address\Total\CollectorFactory $totalCollectorFactory,
        \Magento\Sales\Model\Quote\Address\TotalFactory $addressTotalFactory,
        \Magento\Framework\Object\Copy $objectCopyService,
        \Magento\Sales\Model\Quote\Address\CarrierFactoryInterface $carrierFactory,
        Address\Validator $validator,
        \Magento\Customer\Model\Address\Mapper $addressMapper,
        \Magento\Framework\Model\Resource\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\Db $resourceCollection = null,
        array $data = []
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
        $this->addressBuilder = $addressBuilder;
        $this->validator = $validator;
        $this->addressMapper = $addressMapper;
        parent::__construct(
            $context,
            $registry,
            $metadataService,
            $customAttributeBuilder,
            $directoryData,
            $eavConfig,
            $addressConfig,
            $regionFactory,
            $countryFactory,
            $addressMetadataService,
            $addressBuilder,
            $regionBuilder,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * Initialize resource
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\Sales\Model\Resource\Quote\Address');
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

            if (!$this->getId()) {
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
        return $this->getAddressType() == \Magento\Sales\Model\Quote\Address::TYPE_SHIPPING &&
            ($this->_isNotRegisteredCustomer() ||
            $this->_isDefaultShippingNullOrSameAsBillingAddress());
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
     * Save child collections
     *
     * @return $this
     */
    public function afterSave()
    {
        parent::afterSave();
        if (null !== $this->_items) {
            $this->getItemsCollection()->save();
        }
        if (null !== $this->_rates) {
            $this->getShippingRatesCollection()->save();
        }
        return $this;
    }

    /**
     * Declare address quote model object
     *
     * @param   \Magento\Sales\Model\Quote $quote
     * @return $this
     */
    public function setQuote(\Magento\Sales\Model\Quote $quote)
    {
        $this->_quote = $quote;
        $this->setQuoteId($quote->getId());
        return $this;
    }

    /**
     * Retrieve quote object
     *
     * @return \Magento\Sales\Model\Quote
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
     */
    public function importCustomerAddressData(\Magento\Customer\Api\Data\AddressInterface $address)
    {
        $this->_objectCopyService->copyFieldsetToTarget(
            'customer_address',
            'to_quote_address',
            $this->addressMapper->toFlatArray($address),
            $this
        );
        $region = $this->getRegion();
        $regionId = $this->getRegionId();
        if (isset($regionId) && isset($region)) {
            $this->setRegionId($regionId);
            $this->setRegion($region);
        }
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

        return $this->addressBuilder->populateWithArray($customerAddressData)->create();
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
     * @return \Magento\Eav\Model\Entity\Collection\AbstractCollection
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
     * @return \Magento\Sales\Model\Quote\Address\Item[]
     */
    public function getAllItems()
    {
        // We calculate item list once and cache it in three arrays - all items
        $cachedItems = 'all';
        $key = 'cached_items_' . $cachedItems;
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
        return sizeof($this->getAllItems()) > 0;
    }

    /**
     * Get address item object by id without
     *
     * @param int $itemId
     * @return \Magento\Sales\Model\Quote\Address\Item|false
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
     * @return \Magento\Sales\Model\Quote\Address\Item|false
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
     * @return \Magento\Sales\Model\Quote\Address\Item|false
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
     * @param \Magento\Sales\Model\Quote\Item\AbstractItem $item
     * @param int $qty
     * @return $this
     */
    public function addItem(\Magento\Sales\Model\Quote\Item\AbstractItem $item, $qty = null)
    {
        if ($item instanceof \Magento\Sales\Model\Quote\Item) {
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
     * @return \Magento\Eav\Model\Entity\Collection\AbstractCollection
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
     * @return  \Magento\Sales\Model\Quote\Address\Rate|false
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
     * @return  \Magento\Sales\Model\Quote\Address\Rate|false
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
     * @param \Magento\Sales\Model\Quote\Address\Rate $rate
     * @return $this
     */
    public function addShippingRate(\Magento\Sales\Model\Quote\Address\Rate $rate)
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
     * Returns true if current selected shipping method code corresponds to one of the found rates
     *
     * @param \Magento\Sales\Model\Quote\Item\AbstractItem $item
     * @return bool
     */
    public function requestShippingRates(\Magento\Sales\Model\Quote\Item\AbstractItem $item = null)
    {
        /** @var $request \Magento\Sales\Model\Quote\Address\RateRequest */
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
         * Store and website identifiers need specify from quote
         */
        /*$request->setStoreId($this->_storeManager->getStore()->getId());
          $request->setWebsiteId($this->_storeManager->getStore()->getWebsiteId());*/

        $request->setStoreId($this->getQuote()->getStore()->getId());
        $request->setWebsiteId($this->getQuote()->getStore()->getWebsiteId());
        $request->setFreeShipping($this->getFreeShipping());
        /**
         * Currencies need to convert in free shipping
         */
        $request->setBaseCurrency($this->getQuote()->getStore()->getBaseCurrency());
        $request->setPackageCurrency($this->getQuote()->getStore()->getCurrentCurrency());
        $request->setLimitCarrier($this->getLimitCarrier());

        $request->setBaseSubtotalInclTax($this->getBaseSubtotalInclTax());

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
                        /**
                         * possible bug: this should be setBaseShippingAmount(),
                         * see \Magento\Sales\Model\Quote\Address\Total\Shipping::collect()
                         * where this value is set again from the current specified rate price
                         * (looks like a workaround for this bug)
                         */
                        $this->setShippingAmount($rate->getPrice());
                    }

                    $found = true;
                }
            }
        }

        return $found;
    }

    /**
     * Get totals collector model
     *
     * @return \Magento\Sales\Model\Quote\Address\Total\Collector
     */
    public function getTotalCollector()
    {
        if ($this->_totalCollector === null) {
            $this->_totalCollector = $this->_totalCollectorFactory->create(
                ['store' => $this->getQuote()->getStore()]
            );
        }

        return $this->_totalCollector;
    }

    /**
     * Collect address totals
     *
     * @return $this
     */
    public function collectTotals()
    {
        $this->_eventManager->dispatch(
            $this->_eventPrefix . '_collect_totals_before',
            [$this->_eventObject => $this]
        );
        foreach ($this->getTotalCollector()->getCollectors() as $model) {
            $model->collect($this);
        }
        $this->_eventManager->dispatch(
            $this->_eventPrefix . '_collect_totals_after',
            [$this->_eventObject => $this]
        );

        return $this;
    }

    /**
     * Get address totals as array
     *
     * @return array
     */
    public function getTotals()
    {
        foreach ($this->getTotalCollector()->getRetrievers() as $model) {
            $model->fetch($this);
        }

        return $this->_totals;
    }

    /**
     * Add total data or model
     *
     * @param \Magento\Sales\Model\Quote\Total|array $total
     * @return $this
     */
    public function addTotal($total)
    {
        if (is_array($total)) {
            $totalInstance = $this->_addressTotalFactory->create(
                'Magento\Sales\Model\Quote\Address\Total'
            )->setData(
                $total
            );
        } elseif ($total instanceof \Magento\Sales\Model\Quote\Total) {
            $totalInstance = $total;
        }
        $totalInstance->setAddress($this);
        $this->_totals[$totalInstance->getCode()] = $totalInstance;

        return $this;
    }

    /**
     * Rewrite clone method
     *
     * @return \Magento\Sales\Model\Quote\Address
     */
    public function __clone()
    {
        $this->setId(null);
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
     */
    public function getAppliedTaxes()
    {
        return unserialize($this->getData('applied_taxes'));
    }

    /**
     * Set applied taxes
     *
     * @param array $data
     * @return $this
     */
    public function setAppliedTaxes($data)
    {
        return $this->setData('applied_taxes', serialize($data));
    }

    /**
     * Set shipping amount
     *
     * @param float $value
     * @param bool $alreadyExclTax
     * @return $this
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

    /**
     * Get subtotal amount with applied discount in base currency
     *
     * @return float
     */
    public function getBaseSubtotalWithDiscount()
    {
        return $this->getBaseSubtotal() + $this->getBaseDiscountAmount();
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

    /**
     * {@inheritdoc}
     */
    protected function _getValidationRulesBeforeSave()
    {
        return $this->validator;
    }
}
