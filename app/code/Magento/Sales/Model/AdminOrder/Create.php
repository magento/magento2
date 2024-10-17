<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Model\AdminOrder;

use Magento\Customer\Api\AddressMetadataInterface;
use Magento\Customer\Api\Data\AttributeMetadataInterface;
use Magento\Customer\Model\Metadata\Form as CustomerForm;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Address\CustomAttributeListInterface;
use Magento\Quote\Model\Quote\Item;
use Magento\Sales\Api\Data\OrderAddressInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
use Magento\Quote\Model\Quote;
use Magento\Framework\App\Request\Http as HttpRequest;

/**
 * Order create model
 * @api
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 * @since 100.0.2
 */
class Create extends \Magento\Framework\DataObject implements \Magento\Checkout\Model\Cart\CartInterface
{
    /**
     * Xml default email domain path
     */
    public const XML_PATH_DEFAULT_EMAIL_DOMAIN = 'customer/create_account/email_domain';

    private const XML_PATH_EMAIL_REQUIRED_CREATE_ORDER = 'customer/create_account/email_required_create_order';
    /**
     * Quote session object
     *
     * @var \Magento\Backend\Model\Session\Quote
     */
    protected $_session;

    /**
     * Quote customer wishlist model object
     *
     * @var \Magento\Wishlist\Model\Wishlist
     */
    protected $_wishlist;

    /**
     * Sales Quote instance
     *
     * @var \Magento\Quote\Model\Quote
     */
    protected $_cart;

    /**
     * Catalog Compare List instance
     *
     * @var \Magento\Catalog\Model\Product\Compare\ListCompare
     */
    protected $_compareList;

    /**
     * Re-collect quote flag
     *
     * @var boolean
     */
    protected $_needCollect;

    /**
     * Re-collect cart flag
     *
     * @var boolean
     */
    protected $_needCollectCart = false;

    /**
     * Collect (import) data and validate it flag
     *
     * @var boolean
     */
    protected $_isValidate = false;

    /**
     * Array of validate errors
     *
     * @var array
     */
    protected $_errors = [];

    /**
     * Quote associated with the model
     *
     * @var \Magento\Quote\Model\Quote
     */
    protected $_quote;

    /**
     * Core registry model to bind rules
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;

    /**
     * Core event manager proxy
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventManager = null;

    /**
     * @var \Magento\Sales\Model\Config
     */
    protected $_salesConfig;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Magento\Framework\DataObject\Copy
     */
    protected $_objectCopyService;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var Product\Quote\Initializer
     */
    protected $quoteInitializer;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var \Magento\Customer\Api\AddressRepositoryInterface
     */
    protected $addressRepository;

    /**
     * @var \Magento\Customer\Api\Data\AddressInterfaceFactory
     */
    protected $addressFactory;

    /**
     * @var \Magento\Customer\Model\Metadata\FormFactory
     */
    protected $_metadataFormFactory;

    /**
     * @var \Magento\Customer\Api\GroupRepositoryInterface
     */
    protected $groupRepository;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface
     */
    protected $stockRegistry;

    /**
     * @var \Magento\Sales\Model\AdminOrder\EmailSender
     */
    protected $emailSender;

    /**
     * @var \Magento\Quote\Model\Quote\Item\Updater
     */
    protected $quoteItemUpdater;

    /**
     * @var \Magento\Framework\DataObject\Factory
     */
    protected $objectFactory;

    /**
     * @var \Magento\Customer\Api\AccountManagementInterface
     */
    protected $accountManagement;

    /**
     * @var \Magento\Customer\Api\Data\CustomerInterfaceFactory
     */
    protected $customerFactory;

    /**
     * Constructor
     *
     * @var \Magento\Customer\Model\Customer\Mapper
     */
    protected $customerMapper;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @var \Magento\Quote\Api\CartManagementInterface
     */
    protected $quoteManagement;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var \Magento\Sales\Api\OrderManagementInterface
     */
    protected $orderManagement;

    /**
     * @var \Magento\Quote\Model\QuoteFactory
     */
    protected $quoteFactory;

    /**
     * Serializer interface instance.
     *
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    private $serializer;

    /**
     * @var ExtensibleDataObjectConverter
     */
    private $dataObjectConverter;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var CustomAttributeListInterface
     */
    private $customAttributeList;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepositoryInterface;

    /**
     * @var HttpRequest
     */
    private $request;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Sales\Model\Config $salesConfig
     * @param \Magento\Backend\Model\Session\Quote $quoteSession
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\DataObject\Copy $objectCopyService
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param Product\Quote\Initializer $quoteInitializer
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Customer\Api\AddressRepositoryInterface $addressRepository
     * @param \Magento\Customer\Api\Data\AddressInterfaceFactory $addressFactory
     * @param \Magento\Customer\Model\Metadata\FormFactory $metadataFormFactory
     * @param \Magento\Customer\Api\GroupRepositoryInterface $groupRepository
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param EmailSender $emailSender
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     * @param Item\Updater $quoteItemUpdater
     * @param \Magento\Framework\DataObject\Factory $objectFactory
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param \Magento\Customer\Api\AccountManagementInterface $accountManagement
     * @param \Magento\Customer\Api\Data\CustomerInterfaceFactory $customerFactory
     * @param \Magento\Customer\Model\Customer\Mapper $customerMapper
     * @param \Magento\Quote\Api\CartManagementInterface $quoteManagement
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     * @param \Magento\Sales\Api\OrderManagementInterface $orderManagement
     * @param \Magento\Quote\Model\QuoteFactory $quoteFactory
     * @param array $data
     * @param \Magento\Framework\Serialize\Serializer\Json|null $serializer
     * @param ExtensibleDataObjectConverter|null $dataObjectConverter
     * @param StoreManagerInterface $storeManager
     * @param CustomAttributeListInterface|null $customAttributeList
     * @param OrderRepositoryInterface|null $orderRepositoryInterface
     * @param HttpRequest|null $request
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Sales\Model\Config $salesConfig,
        \Magento\Backend\Model\Session\Quote $quoteSession,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\DataObject\Copy $objectCopyService,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        Product\Quote\Initializer $quoteInitializer,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository,
        \Magento\Customer\Api\Data\AddressInterfaceFactory $addressFactory,
        \Magento\Customer\Model\Metadata\FormFactory $metadataFormFactory,
        \Magento\Customer\Api\GroupRepositoryInterface $groupRepository,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Sales\Model\AdminOrder\EmailSender $emailSender,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\Quote\Model\Quote\Item\Updater $quoteItemUpdater,
        \Magento\Framework\DataObject\Factory $objectFactory,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Customer\Api\AccountManagementInterface $accountManagement,
        \Magento\Customer\Api\Data\CustomerInterfaceFactory $customerFactory,
        \Magento\Customer\Model\Customer\Mapper $customerMapper,
        \Magento\Quote\Api\CartManagementInterface $quoteManagement,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        \Magento\Sales\Api\OrderManagementInterface $orderManagement,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        array $data = [],
        \Magento\Framework\Serialize\Serializer\Json $serializer = null,
        ExtensibleDataObjectConverter $dataObjectConverter = null,
        StoreManagerInterface $storeManager = null,
        CustomAttributeListInterface $customAttributeList = null,
        OrderRepositoryInterface $orderRepositoryInterface = null,
        HttpRequest $request = null
    ) {
        $this->_objectManager = $objectManager;
        $this->_eventManager = $eventManager;
        $this->_coreRegistry = $coreRegistry;
        $this->_salesConfig = $salesConfig;
        $this->_session = $quoteSession;
        $this->_logger = $logger;
        $this->_objectCopyService = $objectCopyService;
        $this->quoteInitializer = $quoteInitializer;
        $this->messageManager = $messageManager;
        $this->customerRepository = $customerRepository;
        $this->addressRepository = $addressRepository;
        $this->addressFactory = $addressFactory;
        $this->_metadataFormFactory = $metadataFormFactory;
        $this->customerFactory = $customerFactory;
        $this->groupRepository = $groupRepository;
        $this->_scopeConfig = $scopeConfig;
        $this->emailSender = $emailSender;
        $this->stockRegistry = $stockRegistry;
        $this->quoteItemUpdater = $quoteItemUpdater;
        $this->objectFactory = $objectFactory;
        $this->quoteRepository = $quoteRepository;
        $this->accountManagement = $accountManagement;
        $this->customerMapper = $customerMapper;
        $this->quoteManagement = $quoteManagement;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->orderManagement = $orderManagement;
        $this->quoteFactory = $quoteFactory;
        $this->serializer = $serializer ?: ObjectManager::getInstance()
            ->get(\Magento\Framework\Serialize\Serializer\Json::class);
        parent::__construct($data);
        $this->dataObjectConverter = $dataObjectConverter ?: ObjectManager::getInstance()
            ->get(ExtensibleDataObjectConverter::class);
        $this->storeManager = $storeManager ?: ObjectManager::getInstance()->get(StoreManagerInterface::class);
        $this->customAttributeList = $customAttributeList ?: ObjectManager::getInstance()
            ->get(CustomAttributeListInterface::class);
        $this->orderRepositoryInterface = $orderRepositoryInterface ?: ObjectManager::getInstance()
            ->get(OrderRepositoryInterface::class);
        $this->request = $request ?: ObjectManager::getInstance()
            ->get(HttpRequest::class);
    }

    /**
     * Set validate data in import data flag
     *
     * @param boolean $flag
     * @return $this
     */
    public function setIsValidate($flag)
    {
        $this->_isValidate = (bool)$flag;
        return $this;
    }

    /**
     * Return is validate data in import flag
     *
     * @return boolean
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getIsValidate()
    {
        return $this->_isValidate;
    }

    /**
     * Retrieve quote item
     *
     * @param int|\Magento\Quote\Model\Quote\Item $item
     * @return \Magento\Quote\Model\Quote\Item|false
     */
    protected function _getQuoteItem($item)
    {
        if ($item instanceof \Magento\Quote\Model\Quote\Item) {
            return $item;
        } elseif (is_numeric($item)) {
            return $this->getSession()->getQuote()->getItemById($item);
        }

        return false;
    }

    /**
     * Initialize data for price rules
     *
     * @return $this
     */
    public function initRuleData()
    {
        $this->_coreRegistry->unregister('rule_data');
        $this->_coreRegistry->register(
            'rule_data',
            new \Magento\Framework\DataObject(
                [
                    'store_id' => $this->_session->getStore()->getId(),
                    'website_id' => $this->_session->getStore()->getWebsiteId(),
                    'customer_group_id' => $this->getCustomerGroupId()
                ]
            )
        );

        return $this;
    }

    /**
     * Set collect totals flag for quote
     *
     * @param bool $flag
     *
     * @return $this
     */
    public function setRecollect($flag)
    {
        $this->_needCollect = $flag;
        return $this;
    }

    /**
     * Recollect totals for customer cart.
     *
     * Set recollect totals flag for quote.
     *
     * @return $this
     */
    public function recollectCart()
    {
        if ($this->_needCollectCart === true) {
            $this->getCustomerCart()->collectTotals();
            $this->quoteRepository->save($this->getCustomerCart());
        }
        $this->setRecollect(true);

        return $this;
    }

    /**
     * Quote saving
     *
     * @return $this
     */
    public function saveQuote()
    {
        if (!$this->getQuote()->getId()) {
            return $this;
        }

        if ($this->_needCollect) {
            $this->getQuote()->collectTotals();
        }

        $this->quoteRepository->save($this->getQuote());
        return $this;
    }

    /**
     * Retrieve session model object of quote
     *
     * @return \Magento\Backend\Model\Session\Quote
     */
    public function getSession()
    {
        return $this->_session;
    }

    /**
     * Retrieve quote object model
     *
     * @return \Magento\Quote\Model\Quote
     */
    public function getQuote()
    {
        if (!$this->_quote) {
            $this->_quote = $this->getSession()->getQuote();
        }

        return $this->_quote;
    }

    /**
     * Set quote object
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @return $this
     */
    public function setQuote(\Magento\Quote\Model\Quote $quote)
    {
        $this->_quote = $quote;
        return $this;
    }

    /**
     * Initialize creation data from existing order
     *
     * @param \Magento\Sales\Model\Order $order
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function initFromOrder(\Magento\Sales\Model\Order $order)
    {
        $session = $this->getSession();
        $session->setData($order->getReordered() ? 'reordered' : 'order_id', $order->getId());
        $session->setCurrencyId($order->getOrderCurrencyCode());
        /* Check if we edit guest order */
        $session->setCustomerId($order->getCustomerId() ?: false);
        $session->setStoreId($order->getStoreId());
        if ($session->getData('reordered')) {
            $this->getQuote()->setCustomerGroupId($order->getCustomerGroupId());
        }

        /* Initialize catalog rule data with new session values */
        $this->initRuleData();
        foreach ($order->getItemsCollection($this->_salesConfig->getAvailableProductTypes(), true) as $orderItem) {
            /* @var $orderItem \Magento\Sales\Model\Order\Item */
            if (!$orderItem->getParentItem()) {
                $qty = $orderItem->getQtyOrdered();
                if (!$order->getReordered()) {
                    $qty -= max($orderItem->getQtyShipped(), $orderItem->getQtyInvoiced());
                }

                if ($qty > 0) {
                    $item = $this->initFromOrderItem($orderItem, $qty);
                    if (is_string($item)) {
                        throw new \Magento\Framework\Exception\LocalizedException(__($item));
                    }
                }
            }
        }

        $shippingAddress = $order->getShippingAddress();
        if ($shippingAddress) {
            $shippingAddress->setSameAsBilling($this->isAddressesAreEqual($order));
        }

        $this->_initBillingAddressFromOrder($order);
        $this->_initShippingAddressFromOrder($order);

        $quote = $this->getQuote();
        if (!$quote->isVirtual() && $this->getShippingAddress()->getSameAsBilling()) {
            $quote->getShippingAddress()->setCustomerAddressId(
                $quote->getBillingAddress()->getCustomerAddressId()
            );
            $this->setShippingAsBilling(1);
        }

        $this->setShippingMethod($order->getShippingMethod());
        $quote->getShippingAddress()->setShippingDescription($order->getShippingDescription());

        $orderCouponCode = $order->getCouponCode();
        if ($orderCouponCode) {
            $quote->setCouponCode($orderCouponCode);
        }

        if ($quote->getCouponCode()) {
            $quote->collectTotals();
        }

        $this->_objectCopyService->copyFieldsetToTarget('sales_copy_order', 'to_edit', $order, $quote);

        $this->_eventManager->dispatch('sales_convert_order_to_quote', ['order' => $order, 'quote' => $quote]);

        if (!$order->getCustomerId()) {
            $quote->setCustomerIsGuest(true);
        }

        if ($session->getUseOldShippingMethod(true)) {
            /*
             * if we are making reorder or editing old order
             * we need to show old shipping as preselected
             * so for this we need to collect shipping rates
             */
            $this->collectShippingRates();
        } else {
            /*
             * if we are creating new order then we don't need to collect
             * shipping rates before customer hit appropriate button
             */
            $this->collectRates();
        }

        $quote->getShippingAddress()->unsCachedItemsAll();
        $quote->getBillingAddress()->unsCachedItemsAll();
        $quote->setTotalsCollectedFlag(false);

        $this->quoteRepository->save($quote);

        return $this;
    }

    /**
     * Copy billing address from order
     *
     * @param \Magento\Sales\Model\Order $order
     * @return void
     */
    protected function _initBillingAddressFromOrder(\Magento\Sales\Model\Order $order)
    {
        $this->getQuote()->getBillingAddress()->setCustomerAddressId('');
        $this->_objectCopyService->copyFieldsetToTarget(
            'sales_copy_order_billing_address',
            'to_order',
            $order->getBillingAddress(),
            $this->getQuote()->getBillingAddress()
        );
    }

    /**
     * Copy shipping address from order
     *
     * @param \Magento\Sales\Model\Order $order
     * @return void
     */
    protected function _initShippingAddressFromOrder(\Magento\Sales\Model\Order $order)
    {
        $orderShippingAddress = $order->getShippingAddress();
        $quoteShippingAddress = $this->getQuote()->getShippingAddress()->setCustomerAddressId(
            ''
        )->setSameAsBilling(
            $orderShippingAddress && $orderShippingAddress->getSameAsBilling()
        );
        $this->_objectCopyService->copyFieldsetToTarget(
            'sales_copy_order_shipping_address',
            'to_order',
            $orderShippingAddress,
            $quoteShippingAddress
        );
    }

    /**
     * Initialize creation data from existing order Item
     *
     * @param \Magento\Sales\Model\Order\Item $orderItem
     * @param int $qty
     * @return \Magento\Quote\Model\Quote\Item|string|$this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function initFromOrderItem(\Magento\Sales\Model\Order\Item $orderItem, $qty = null)
    {
        if (!$orderItem->getId()) {
            return $this;
        }

        $product = $this->_objectManager->create(
            \Magento\Catalog\Model\Product::class
        )->setStoreId(
            $this->getSession()->getStoreId()
        )->load(
            $orderItem->getProductId()
        );

        if ($product->getId()) {
            $product->setSkipCheckRequiredOption(true);
            $buyRequest = $orderItem->getBuyRequest();
            if (is_numeric($qty)) {
                $buyRequest->setQty($qty);
            }
            $productOptions = $orderItem->getProductOptions();

            $this->formattedOptions($product, $buyRequest, $productOptions);

            $item = $this->getQuote()->addProduct($product, $buyRequest);
            if (is_string($item)) {
                return $item;
            }

            if ($additionalOptions = $orderItem->getProductOptionByCode('additional_options')) {
                $item->addOption(
                    new \Magento\Framework\DataObject(
                        [
                            'product' => $item->getProduct(),
                            'code' => 'additional_options',
                            'value' => $this->serializer->serialize($additionalOptions)
                        ]
                    )
                );
            }

            $this->_eventManager->dispatch(
                'sales_convert_order_item_to_quote_item',
                ['order_item' => $orderItem, 'quote_item' => $item]
            );
            return $item;
        }

        return $this;
    }

    /**
     * Retrieve customer wishlist model object
     *
     * @param bool $cacheReload pass cached wishlist object and get new one
     * @return \Magento\Wishlist\Model\Wishlist|false Return false if customer ID is not specified
     */
    public function getCustomerWishlist($cacheReload = false)
    {
        if ($this->_wishlist !== null && !$cacheReload) {
            return $this->_wishlist;
        }

        $customerId = (int)$this->getSession()->getCustomerId();
        if ($customerId) {
            $this->_wishlist = $this->_objectManager->create(\Magento\Wishlist\Model\Wishlist::class);
            $this->_wishlist->loadByCustomerId($customerId, true);
            $this->_wishlist->setStore(
                $this->getSession()->getStore()
            )->setSharedStoreIds(
                $this->getSession()->getStore()->getWebsite()->getStoreIds()
            );
        } else {
            $this->_wishlist = false;
        }

        return $this->_wishlist;
    }

    /**
     * Retrieve customer cart quote object model
     *
     * @return \Magento\Quote\Model\Quote
     */
    public function getCustomerCart()
    {
        if ($this->_cart !== null) {
            return $this->_cart;
        }

        $this->_cart = $this->quoteFactory->create();

        $customerId = (int)$this->getSession()->getCustomerId();
        $storeId = (int)$this->getSession()->getStoreId();
        if ($customerId) {
            try {
                $this->_cart = $this->quoteRepository->getForCustomer($customerId, [$storeId]);
            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                if ($this->getQuote()->hasItems()) {
                    $this->_cart->setStore($this->getSession()->getStore());
                    $customerData = $this->customerRepository->getById($customerId);
                    $this->_cart->assignCustomer($customerData);
                    $this->quoteRepository->save($this->_cart);
                }
            }
        }

        return $this->_cart;
    }

    /**
     * Retrieve customer compare list model object
     *
     * @return \Magento\Catalog\Model\Product\Compare\ListCompare
     */
    public function getCustomerCompareList()
    {
        if ($this->_compareList !== null) {
            return $this->_compareList;
        }
        $customerId = (int)$this->getSession()->getCustomerId();
        if ($customerId) {
            $this->_compareList = $this->_objectManager->create(
                \Magento\Catalog\Model\Product\Compare\ListCompare::class
            );
        } else {
            $this->_compareList = false;
        }

        return $this->_compareList;
    }

    /**
     * Retrieve current customer group ID.
     *
     * @return int
     */
    public function getCustomerGroupId()
    {
        $groupId = $this->getQuote()->getCustomerGroupId();
        // @phpstan-ignore-next-line
        if (!isset($groupId)) {
            $groupId = $this->getSession()->getCustomerGroupId();
        }

        return $groupId;
    }

    /**
     * Move quote item to another items list
     *
     * @param int|\Magento\Quote\Model\Quote\Item $item
     * @param string $moveTo
     * @param int $qty
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * phpcs:disable Generic.Metrics.NestingLevel
     */
    public function moveQuoteItem($item, $moveTo, $qty)
    {
        $item = $this->_getQuoteItem($item);
        if ($item) {
            $removeItem = false;
            $moveTo = explode('_', $moveTo ?: '');
            switch ($moveTo[0]) {
                case 'order':
                    $info = $item->getBuyRequest();
                    $info->setOptions($this->_prepareOptionsForRequest($item))->setQty($qty);

                    $product = $this->_objectManager->create(
                        \Magento\Catalog\Model\Product::class
                    )->setStoreId(
                        $this->getQuote()->getStoreId()
                    )->load(
                        $item->getProduct()->getId()
                    );

                    $product->setSkipCheckRequiredOption(true);
                    $newItem = $this->getQuote()->addProduct($product, $info);

                    if (is_string($newItem)) {
                        throw new \Magento\Framework\Exception\LocalizedException(__($newItem));
                    }
                    $product->unsSkipCheckRequiredOption();
                    $newItem->checkData();
                    $this->_needCollectCart = true;
                    break;
                case 'cart':
                    $cart = $this->getCustomerCart();
                    if ($cart && $item->getOptionByCode('additional_options') === null) {
                        //options and info buy request
                        $product = $this->_objectManager->create(
                            \Magento\Catalog\Model\Product::class
                        )->setStoreId(
                            $this->getQuote()->getStoreId()
                        )->load(
                            $item->getProduct()->getId()
                        );

                        $info = $item->getOptionByCode('info_buyRequest');
                        if ($info) {
                            $info = new \Magento\Framework\DataObject(
                                $this->serializer->unserialize($info->getValue())
                            );
                            $info->setQty($qty);
                            $info->setOptions($this->_prepareOptionsForRequest($item));
                        } else {
                            $info = new \Magento\Framework\DataObject(
                                [
                                    'product_id' => $product->getId(),
                                    'qty' => $qty,
                                    'options' => $this->_prepareOptionsForRequest($item)
                                ]
                            );
                        }

                        $cartItems = $cart->getAllVisibleItems();
                        $cartItemsToRestore = [];
                        foreach ($cartItems as $cartItem) {
                            $cartItemsToRestore[$cartItem->getItemId()] = $cartItem->getItemId();
                        }
                        $canBeRestored = $this->restoreTransferredItem('cart', $cartItemsToRestore);

                        $cartItem = $cart->addProduct($product, $info);
                        if (!$canBeRestored) {
                            if (is_string($cartItem)) {
                                throw new \Magento\Framework\Exception\LocalizedException(__($cartItem));
                            }
                            $cartItem->setPrice($item->getProduct()->getPrice());
                        }
                        $this->_needCollectCart = true;
                        $removeItem = true;
                        $this->removeCartTransferredItemsAndUpdateQty($cartItem, $item->getId());
                    }
                    break;
                case 'wishlist':
                    $wishlist = null;
                    if (!isset($moveTo[1])) {
                        $wishlist = $this->_objectManager->create(
                            \Magento\Wishlist\Model\Wishlist::class
                        )->loadByCustomerId(
                            $this->getSession()->getCustomerId(),
                            true
                        );
                    } else {
                        $wishlist = $this->_objectManager->create(
                            \Magento\Wishlist\Model\Wishlist::class
                        )->load($moveTo[1]);
                        if (!$wishlist->getId() || $wishlist->getCustomerId() != $this->getSession()->getCustomerId()
                        ) {
                            $wishlist = null;
                        }
                    }
                    if (!$wishlist) {
                        throw new \Magento\Framework\Exception\LocalizedException(
                            __('We can\'t find this wish list.')
                        );
                    }
                    $wishlist->setStore(
                        $this->getSession()->getStore()
                    )->setSharedStoreIds(
                        $this->getSession()->getStore()->getWebsite()->getStoreIds()
                    );

                    if ($wishlist->getId() && $item->getProduct()->isVisibleInSiteVisibility()) {
                        $info = $item->getBuyRequest();
                        $info->setOptions(
                            $this->_prepareOptionsForRequest($item)
                        )->setQty(
                            $qty
                        )->setStoreId(
                            $this->getSession()->getStoreId()
                        );
                        $wishlistItems = $wishlist->getItemCollection()->getItems();
                        $canBeRestored = $this->restoreTransferredItem('wishlist', $wishlistItems);
                        if (!$canBeRestored) {
                            $wishlist->addNewItem($item->getProduct(), $info);
                        }
                        $removeItem = true;
                    }
                    break;
                case 'remove':
                    $removeItem = true;
                    break;
                default:
                    break;
            }
            if ($removeItem) {
                $this->getQuote()->deleteItem($item);
            }
            $this->setRecollect(true);
        }

        return $this;
    }

    /**
     * Handle data sent from sidebar
     *
     * @param array $data
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function applySidebarData($data)
    {
        if (isset($data['add_order_item'])) {
            foreach ($data['add_order_item'] as $orderItemId => $value) {
                /* @var $orderItem \Magento\Sales\Model\Order\Item */
                $orderItem = $this->_objectManager->create(\Magento\Sales\Model\Order\Item::class)->load($orderItemId);
                $item = $this->initFromOrderItem($orderItem);
                if (is_string($item)) {
                    throw new \Magento\Framework\Exception\LocalizedException(__($item));
                }
            }
        }
        if (isset($data['add_cart_item'])) {
            foreach ($data['add_cart_item'] as $itemId => $qty) {
                $item = $this->getCustomerCart()->getItemById($itemId);
                if ($item) {
                    $this->moveQuoteItem($item, 'order', $qty);
                    $transferredItems = $this->_session->getTransferredItems() ?? [];
                    $transferredItems['cart'][$itemId] = $itemId;
                    $this->_session->setTransferredItems($transferredItems);
                }
            }
        }
        if (isset($data['add_wishlist_item'])) {
            foreach ($data['add_wishlist_item'] as $itemId => $qty) {
                $item = $this->_objectManager->create(
                    \Magento\Wishlist\Model\Item::class
                )->loadWithOptions(
                    $itemId,
                    'info_buyRequest'
                );
                if ($item->getId()) {
                    $this->addProduct($item->getProduct(), $item->getBuyRequest()->toArray());
                    $transferredItems = $this->_session->getTransferredItems() ?? [];
                    $transferredItems['wishlist'][$itemId] = $itemId;
                    $this->_session->setTransferredItems($transferredItems);
                }
            }
        }
        if (isset($data['add'])) {
            foreach ($data['add'] as $productId => $qty) {
                $this->addProduct($productId, ['qty' => $qty]);
            }
        }
        if (isset($data['remove'])) {
            foreach ($data['remove'] as $itemId => $from) {
                $this->removeItem($itemId, $from);
            }
        }
        if (isset($data['empty_customer_cart']) && (int)$data['empty_customer_cart'] == 1) {
            $this->getCustomerCart()->removeAllItems()->collectTotals();
            $this->quoteRepository->save($this->getCustomerCart());
        }

        $this->recollectCart();

        return $this;
    }

    /**
     * Remove item from some of customer items storage (shopping cart, wishlist etc.)
     *
     * @param int $itemId
     * @param string $from
     * @return $this
     */
    public function removeItem($itemId, $from)
    {
        switch ($from) {
            case 'quote':
                $this->removeQuoteItem($itemId);
                break;
            case 'cart':
                $cart = $this->getCustomerCart();
                if ($cart) {
                    $cart->removeItem($itemId);
                    $this->_needCollectCart = true;
                }
                break;
            case 'wishlist':
                $wishlist = $this->getCustomerWishlist();
                if ($wishlist) {
                    $item = $this->_objectManager->create(\Magento\Wishlist\Model\Item::class)->load($itemId);
                    $item->delete();
                }
                break;
            case 'compared':
                $this->_objectManager->create(
                    \Magento\Catalog\Model\Product\Compare\Item::class
                )->load($itemId)->delete();
                break;
        }

        return $this;
    }

    /**
     * Remove quote item
     *
     * @param int $item
     * @return $this
     */
    public function removeQuoteItem($item)
    {
        $this->getQuote()->removeItem($item);
        $this->setRecollect(true);

        return $this;
    }

    /**
     * Add product to current order quote
     * $product can be either product id or product model
     * $config can be either buyRequest config, or just qty
     *
     * @param int|\Magento\Catalog\Model\Product $product
     * @param array|float|int|\Magento\Framework\DataObject $config
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function addProduct($product, $config = 1)
    {
        if (!is_array($config) && !$config instanceof \Magento\Framework\DataObject) {
            $config = ['qty' => $config];
        }
        $config = new \Magento\Framework\DataObject($config);

        if (!$product instanceof \Magento\Catalog\Model\Product) {
            $productId = $product;
            $product = $this->_objectManager->create(
                \Magento\Catalog\Model\Product::class
            )->setStore(
                $this->getSession()->getStore()
            )->setStoreId(
                $this->getSession()->getStoreId()
            )->load(
                $product
            );
            if (!$product->getId()) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('We could not add a product to cart by the ID "%1".', $productId)
                );
            }
        }

        $item = $this->quoteInitializer->init($this->getQuote(), $product, $config);

        if (is_string($item)) {
            throw new \Magento\Framework\Exception\LocalizedException(__($item));
        }
        $item->checkData();
        $this->setRecollect(true);

        return $this;
    }

    /**
     * Add multiple products to current order quote
     *
     * @param array $products
     * @return $this
     */
    public function addProducts(array $products)
    {
        foreach ($products as $productId => $config) {
            $config['qty'] = isset($config['qty']) ? (double)$config['qty'] : 1;
            try {
                $this->addProduct($productId, $config);
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                return $e;
            }
        }

        return $this;
    }

    /**
     * Update quantity of order quote items
     *
     * @param array $items
     * @return $this
     * @throws \Exception|\Magento\Framework\Exception\LocalizedException
     */
    public function updateQuoteItems($items)
    {
        if (!is_array($items)) {
            return $this;
        }

        try {
            foreach ($items as $itemId => $info) {
                if (!empty($info['configured'])) {
                    $item = $this->getQuote()->updateItem($itemId, $this->objectFactory->create($info));
                    $info['qty'] = (double)$item->getQty();
                } else {
                    $item = $this->getQuote()->getItemById($itemId);
                    if (!$item) {
                        continue;
                    }
                    $info['qty'] = (double)$info['qty'];
                }
                $this->quoteItemUpdater->update($item, $info);
                if ($item && !empty($info['action'])) {
                    $this->moveQuoteItem($item, $info['action'], $item->getQty());
                }
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->recollectCart();
            throw $e;
        } catch (\Exception $e) {
            $this->_logger->critical($e);
        }
        $this->recollectCart();

        return $this;
    }

    /**
     * Parse additional options and sync them with product options
     *
     * @param \Magento\Quote\Model\Quote\Item $item
     * @param string $additionalOptions
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     *
     * @deprecated 101.0.0
     * @see void
     */
    protected function _parseOptions(\Magento\Quote\Model\Quote\Item $item, $additionalOptions)
    {
        $productOptions = $this->_objectManager->get(
            \Magento\Catalog\Model\Product\Option\Type\DefaultType::class
        )->setProduct(
            $item->getProduct()
        )->getProductOptions();

        $newOptions = [];
        $newAdditionalOptions = [];

        foreach (explode("\n", $additionalOptions) as $_additionalOption) {
            if (strlen(trim($_additionalOption))) {
                try {
                    if (strpos($_additionalOption, ':') === false) {
                        throw new \Magento\Framework\Exception\LocalizedException(
                            __('There is an error in one of the option rows.')
                        );
                    }
                    list($label, $value) = explode(':', $_additionalOption, 2);
                } catch (\Exception $e) {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __('There is an error in one of the option rows.')
                    );
                }
                $label = trim($label);
                $value = trim($value);
                if (empty($value)) {
                    continue;
                }

                if (array_key_exists($label, $productOptions)) {
                    $optionId = $productOptions[$label]['option_id'];
                    $option = $item->getProduct()->getOptionById($optionId);

                    $group = $this->_objectManager->get(
                        \Magento\Catalog\Model\Product\Option::class
                    )->groupFactory(
                        $option->getType()
                    )->setOption(
                        $option
                    )->setProduct(
                        $item->getProduct()
                    );

                    $parsedValue = $group->parseOptionValue($value, $productOptions[$label]['values']);

                    if ($parsedValue !== null) {
                        $newOptions[$optionId] = $parsedValue;
                    } else {
                        $newAdditionalOptions[] = ['label' => $label, 'value' => $value];
                    }
                } else {
                    $newAdditionalOptions[] = ['label' => $label, 'value' => $value];
                }
            }
        }

        return ['options' => $newOptions, 'additional_options' => $newAdditionalOptions];
    }

    /**
     * Assign options to item
     *
     * @param \Magento\Quote\Model\Quote\Item $item
     * @param array $options
     * @return $this
     *
     * @deprecated 101.0.0
     * @see void
     */
    protected function _assignOptionsToItem(\Magento\Quote\Model\Quote\Item $item, $options)
    {
        $optionIds = $item->getOptionByCode('option_ids');
        if ($optionIds) {
            foreach (explode(',', $optionIds->getValue()) as $optionId) {
                $item->removeOption('option_' . $optionId);
            }
            $item->removeOption('option_ids');
        }
        if ($item->getOptionByCode('additional_options')) {
            $item->removeOption('additional_options');
        }
        $item->save();
        if (!empty($options['options'])) {
            $item->addOption(
                new \Magento\Framework\DataObject(
                    [
                        'product' => $item->getProduct(),
                        'code' => 'option_ids',
                        'value' => implode(',', array_keys($options['options']))
                    ]
                )
            );

            foreach ($options['options'] as $optionId => $optionValue) {
                $item->addOption(
                    new \Magento\Framework\DataObject(
                        [
                            'product' => $item->getProduct(),
                            'code' => 'option_' . $optionId,
                            'value' => $optionValue
                        ]
                    )
                );
            }
        }
        if (!empty($options['additional_options'])) {
            $item->addOption(
                new \Magento\Framework\DataObject(
                    [
                        'product' => $item->getProduct(),
                        'code' => 'additional_options',
                        'value' => $this->serializer->serialize($options['additional_options'])
                    ]
                )
            );
        }

        return $this;
    }

    /**
     * Prepare options array for info buy request
     *
     * @param \Magento\Quote\Model\Quote\Item $item
     * @return array
     */
    protected function _prepareOptionsForRequest($item)
    {
        $newInfoOptions = [];
        $optionIds = $item->getOptionByCode('option_ids');
        if ($optionIds) {
            foreach (explode(',', $optionIds->getValue() ?? '') as $optionId) {
                $option = $item->getProduct()->getOptionById($optionId);
                $optionValue = $item->getOptionByCode('option_' . $optionId)->getValue();

                $group = $this->_objectManager->get(
                    \Magento\Catalog\Model\Product\Option::class
                )->groupFactory(
                    $option->getType()
                )->setOption(
                    $option
                )->setQuoteItem(
                    $item
                );

                $newInfoOptions[$optionId] = $group->prepareOptionValueForRequest($optionValue);
            }
        }

        return $newInfoOptions;
    }

    /**
     * Return valid price
     *
     * @param float|int $price
     * @return float|int
     */
    protected function _parseCustomPrice($price)
    {
        $price = $this->_objectManager->get(\Magento\Framework\Locale\FormatInterface::class)->getNumber($price);
        $price = $price > 0 ? $price : 0;

        return $price;
    }

    /**
     * Retrieve order quote shipping address
     *
     * @return \Magento\Quote\Model\Quote\Address
     */
    public function getShippingAddress()
    {
        return $this->getQuote()->getShippingAddress();
    }

    /**
     * Return Customer (Checkout) Form instance
     *
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     * @return CustomerForm
     */
    protected function _createCustomerForm(\Magento\Customer\Api\Data\CustomerInterface $customer)
    {
        $customerForm = $this->_metadataFormFactory->create(
            \Magento\Customer\Api\CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER,
            'adminhtml_checkout',
            $this->customerMapper->toFlatArray($customer),
            false,
            CustomerForm::DONT_IGNORE_INVISIBLE
        );

        return $customerForm;
    }

    /**
     * Set and validate Quote address
     *
     * All errors added to _errors
     *
     * @param \Magento\Quote\Model\Quote\Address $address
     * @param array $data
     * @return $this
     */
    protected function _setQuoteAddress(\Magento\Quote\Model\Quote\Address $address, array $data)
    {
        $isAjax = !$this->getIsValidate();

        // Region is a Data Object, so it is represented by an array. validateData() doesn't understand arrays, so we
        // need to merge region data with address data. This is going to be removed when we switch to use address Data
        // Object instead of the address model.
        // Note: if we use getRegion() here it will pull region from db using the region_id
        $data = isset($data['region']) && is_array($data['region']) ? array_merge($data, $data['region']) : $data;

        $addressForm = $this->_metadataFormFactory->create(
            AddressMetadataInterface::ENTITY_TYPE_ADDRESS,
            'adminhtml_customer_address',
            $data,
            $isAjax,
            CustomerForm::DONT_IGNORE_INVISIBLE,
            []
        );

        // prepare request
        // save original request structure for files
        if ($address->getAddressType() == \Magento\Quote\Model\Quote\Address::TYPE_SHIPPING) {
            $requestData = ['order' => ['shipping_address' => $data]];
            $requestScope = 'order/shipping_address';
        } else {
            $requestData = ['order' => ['billing_address' => $data]];
            $requestScope = 'order/billing_address';
        }
        $request = $addressForm->prepareRequest($requestData);
        $addressData = $addressForm->extractData($request, $requestScope);
        if ($this->getIsValidate()) {
            $errors = $addressForm->validateData($addressData);
            if ($errors !== true) {
                if ($address->getAddressType() == \Magento\Quote\Model\Quote\Address::TYPE_SHIPPING) {
                    $typeName = __('Shipping Address: ');
                } else {
                    $typeName = __('Billing Address: ');
                }
                foreach ($errors as $error) {
                    $this->_errors[] = $typeName . $error;
                }
                $address->setData($addressForm->restoreData($addressData));
            } else {
                $address->setData($addressForm->compactData($addressData));
            }
        } else {
            $address->addData($addressForm->restoreData($addressData));
        }

        return $this;
    }

    /**
     * Set shipping address into quote
     *
     * @param \Magento\Quote\Model\Quote\Address|array $address
     * @return $this
     */
    public function setShippingAddress($address)
    {
        if (is_array($address)) {
            $shippingAddress = $this->_objectManager->create(
                \Magento\Quote\Model\Quote\Address::class
            )->setData(
                $address
            )->setAddressType(
                \Magento\Quote\Model\Quote\Address::TYPE_SHIPPING
            );
            if (!$this->getQuote()->isVirtual()) {
                $this->_setQuoteAddress($shippingAddress, $address);
            }
            /**
             * save_in_address_book is not a valid attribute and is filtered out by _setQuoteAddress,
             * that is why it should be added after _setQuoteAddress call
             */
            $saveInAddressBook = (int)(!empty($address['save_in_address_book']));
            $shippingAddress->setData('save_in_address_book', $saveInAddressBook);
        } elseif ($address instanceof \Magento\Quote\Model\Quote\Address) {
            $shippingAddress = $address;
        } else {
            $shippingAddress = null;
        }

        $this->setRecollect(true);
        $this->getQuote()->setShippingAddress($shippingAddress);

        return $this;
    }

    /**
     * Set shipping address to be same as billing
     *
     * @param bool $flag If true - don't save in address book and actually copy data across billing and shipping
     *                   addresses
     * @return $this
     */
    public function setShippingAsBilling($flag)
    {
        if ($flag) {
            $tmpAddress = clone $this->getBillingAddress();
            $tmpAddress->unsAddressId()->unsAddressType();
            $data = $tmpAddress->getData();
            $data['save_in_address_book'] = 0;
            $shippingAddressTmp = $this->getShippingAddress()->getData();
            // Do not duplicate address (billing address will do saving too)
            $this->getShippingAddress()->addData($data);
            if (array_key_exists('weight', $shippingAddressTmp) && !empty($shippingAddressTmp['weight'])) {
                $this->getShippingAddress()->setWeight($shippingAddressTmp['weight']);
            }
        }
        $this->getShippingAddress()->setSameAsBilling($flag);
        $this->setRecollect(true);
        return $this;
    }

    /**
     * Retrieve quote billing address
     *
     * @return \Magento\Quote\Model\Quote\Address
     */
    public function getBillingAddress()
    {
        return $this->getQuote()->getBillingAddress();
    }

    /**
     * Set billing address into quote
     *
     * @param array $address
     * @return $this
     */
    public function setBillingAddress($address)
    {
        if (!is_array($address)) {
            return $this;
        }

        $billingAddress = $this->_objectManager->create(Address::class)
            ->setData($address)
            ->setAddressType(Address::TYPE_BILLING);

        $this->_setQuoteAddress($billingAddress, $address);

        /**
         * save_in_address_book is not a valid attribute and is filtered out by _setQuoteAddress,
         * that is why it should be added after _setQuoteAddress call
         */
        $saveInAddressBook = (int)(!empty($address['save_in_address_book']));
        $billingAddress->setData('save_in_address_book', $saveInAddressBook);

        $quote = $this->getQuote();
        $shippingAddress = $this->getShippingAddress();
        if (!$quote->isVirtual() && $shippingAddress->getSameAsBilling()) {
            $address['save_in_address_book'] = 0;
            $this->setShippingAddress($address);
        }

        // not assigned billing address should be saved as new
        // but if quote already has the billing address it won't be overridden
        if (empty($billingAddress->getCustomerAddressId())) {
            $billingAddress->setCustomerAddressId(null);
            $quote->getBillingAddress()->setCustomerAddressId(null);
        }
        $quote->setBillingAddress($billingAddress);

        if ($shippingAddress->getSameAsBilling()) {
            $this->synchronizeAddressesFileAttributes();
        }

        return $this;
    }

    /**
     * Synchronizes addresses file attributes.
     *
     * @return void
     */
    private function synchronizeAddressesFileAttributes(): void
    {
        $billingAddress = $this->getBillingAddress();
        $shippingAddress = $this->getShippingAddress();

        /** @var AttributeMetadataInterface[] $customAttributes */
        $customAttributes = $this->customAttributeList->getAttributes();
        foreach ($customAttributes as $attribute) {
            $attributeCode = $attribute->getAttributeCode();
            if ($attribute->getFrontendInput() === 'file'
                && !empty($billingAddress->getData($attributeCode))
                && empty($shippingAddress->getData($attributeCode))
            ) {
                $shippingAddress->setData($attributeCode, $billingAddress->getData($attributeCode));
            }
        }
    }

    /**
     * Set shipping method
     *
     * @param string $method
     * @return $this
     */
    public function setShippingMethod($method)
    {
        $this->getShippingAddress()->setShippingMethod($method);
        $this->setRecollect(true);

        return $this;
    }

    /**
     * Empty shipping method and clear shipping rates
     *
     * @return $this
     */
    public function resetShippingMethod()
    {
        $this->getShippingAddress()->setShippingMethod(false);
        $this->getShippingAddress()->removeAllShippingRates();

        return $this;
    }

    /**
     * Collect shipping data for quote shipping address
     *
     * @return $this
     */
    public function collectShippingRates()
    {
        $store = $this->getQuote()->getStore();
        $this->storeManager->setCurrentStore($store);
        $this->getQuote()->getShippingAddress()->setCollectShippingRates(true);
        $this->collectRates();

        return $this;
    }

    /**
     * Calculate totals
     *
     * @return void
     */
    public function collectRates()
    {
        $this->getQuote()->collectTotals();
    }

    /**
     * Set payment method into quote
     *
     * @param string $method
     * @return $this
     */
    public function setPaymentMethod($method)
    {
        $this->getQuote()->getPayment()->setMethod($method);
        return $this;
    }

    /**
     * Set payment data into quote
     *
     * @param array $data
     * @return $this
     */
    public function setPaymentData($data)
    {
        if (!isset($data['method'])) {
            $data['method'] = $this->getQuote()->getPayment()->getMethod();
        }
        $this->getQuote()->getPayment()->importData($data);

        return $this;
    }

    /**
     * Add coupon code to the quote
     *
     * @param string $code
     * @return $this
     */
    public function applyCoupon($code)
    {
        $code = trim((string)$code);
        $this->getQuote()->getShippingAddress()->setCollectShippingRates(true);

        if (empty($code)) {
            $this->getQuote()->getShippingAddress()->setFreeShipping(0);
        }
        $this->getQuote()->setCouponCode($code);
        $this->setRecollect(true);

        return $this;
    }

    /**
     * Add account data to quote
     *
     * @param array $accountData
     * @return $this
     */
    public function setAccountData($accountData)
    {
        $customer = $this->getQuote()->getCustomer();
        if (empty($accountData['email'])) {
            $accountData['email'] = $customer->getEmail();
        }
        $form = $this->_createCustomerForm($customer);

        // emulate request
        $request = $form->prepareRequest($accountData);
        $requestScope = $request->getPostValue() ? 'order/account' : null;
        $data = $form->extractData($request, $requestScope);
        $data = $form->restoreData($data);
        $customer = $this->customerFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $customer,
            $data,
            \Magento\Customer\Api\Data\CustomerInterface::class
        );
        $customer->setStoreId($this->getQuote()->getStoreId());
        $this->getQuote()->updateCustomerData($customer);
        $data = [];

        $customerData = $this->customerMapper->toFlatArray($customer);
        foreach ($form->getAttributes() as $attribute) {
            $code = sprintf('customer_%s', $attribute->getAttributeCode());
            $data[$code] = isset($customerData[$attribute->getAttributeCode()])
                ? $customerData[$attribute->getAttributeCode()]
                : null;
        }

        if (isset($data['customer_group_id'])) {
            $customerGroup = $this->groupRepository->getById($data['customer_group_id']);
            $data['customer_tax_class_id'] = $customerGroup->getTaxClassId();
            $this->setRecollect(true);
        }

        $this->getQuote()->addData($data);

        return $this;
    }

    /**
     * Parse data retrieved from request
     *
     * @param   array $data
     * @return  $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function importPostData($data)
    {
        if (is_array($data)) {
            $this->addData($data);
        } else {
            return $this;
        }

        if (isset($data['account'])) {
            $this->setAccountData($data['account']);
        }

        if (isset($data['comment'])) {
            $this->getQuote()->addData($data['comment']);
            if ($this->getIsValidate()) {
                $notify = !empty($data['comment']['customer_note_notify']);
                $this->getQuote()->setCustomerNoteNotify($notify);
            }
        }

        if (isset($data['billing_address'])) {
            $this->setBillingAddress($data['billing_address']);
        }

        if (isset($data['shipping_address'])) {
            $this->setShippingAddress($data['shipping_address']);
        }

        if (isset($data['shipping_method'])) {
            $this->setShippingMethod($data['shipping_method']);
        }

        if (isset($data['payment_method'])) {
            $this->setPaymentMethod($data['payment_method']);
        }

        if (isset($data['coupon']['code'])) {
            $this->applyCoupon($data['coupon']['code']);
        }

        return $this;
    }

    /**
     * Check whether we need to create new customer (for another website) during order creation
     *
     * @param \Magento\Store\Model\Store $store
     * @return bool
     */
    protected function _customerIsInStore($store)
    {
        $customer = $this->getQuote()->getCustomer();

        return $customer->getWebsiteId() == $store->getWebsiteId()
            || $this->accountManagement->isCustomerInStore($customer->getWebsiteId(), $store->getId());
    }

    /**
     * Set and validate Customer data. Return the updated Data Object merged with the account data
     *
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     * @return \Magento\Customer\Api\Data\CustomerInterface
     */
    protected function _validateCustomerData(\Magento\Customer\Api\Data\CustomerInterface $customer)
    {
        $form = $this->_createCustomerForm($customer);
        // emulate request
        $request = $form->prepareRequest(['order' => $this->getData()]);
        $data = $form->extractData($request, 'order/account');
        $validationResults = $this->accountManagement->validate($customer);
        if (!$validationResults->isValid()) {
            $errors = $validationResults->getMessages();
            if (is_array($errors)) {
                foreach ($errors as $error) {
                    $this->_errors[] = $error;
                }
            }
        }
        $data = $form->restoreData($data);
        foreach ($data as $key => $value) {
            if ($value !== null) {
                unset($data[$key]);
            }
        }

        $this->dataObjectHelper->populateWithArray(
            $customer,
            $data,
            \Magento\Customer\Api\Data\CustomerInterface::class
        );
        return $customer;
    }

    /**
     * Prepare customer data for order creation.
     *
     * Create customer if not created using data from customer form.
     * Create customer billing/shipping address if necessary using data from customer address forms.
     * Set customer data to quote.
     *
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function _prepareCustomer()
    {
        /** @var $store \Magento\Store\Model\Store */
        $store = $this->getSession()->getStore();
        $customer = $this->getQuote()->getCustomer();

        if ($customer->getId() && !$this->_customerIsInStore($store)) {
            /** Create a new customer record if it is not available in the specified store */
            /** Unset customer ID to ensure that new customer will be created */
            $customer->setId(null)
                ->setStoreId($store->getId())
                ->setWebsiteId($store->getWebsiteId())
                ->setCreatedAt(null);
            $customer = $this->_validateCustomerData($customer);
        } elseif (!$customer->getId()) {
            /** Create new customer */
            $customerBillingAddressDataObject = $this->getBillingAddress()->exportCustomerAddress();
            $customer->setSuffix($customerBillingAddressDataObject->getSuffix())
                ->setFirstname($customerBillingAddressDataObject->getFirstname())
                ->setLastname($customerBillingAddressDataObject->getLastname())
                ->setMiddlename($customerBillingAddressDataObject->getMiddlename())
                ->setPrefix($customerBillingAddressDataObject->getPrefix())
                ->setStoreId($store->getId())
                ->setWebsiteId($store->getWebsiteId())
                ->setEmail($this->_getNewCustomerEmail());
            $customer = $this->_validateCustomerData($customer);
        }
        $this->getQuote()->setCustomer($customer);

        if ($this->getBillingAddress()->getSaveInAddressBook()) {
            $this->_prepareCustomerAddress($this->getQuote()->getCustomer(), $this->getBillingAddress());
            $address = $this->getBillingAddress()->setCustomerId($this->getQuote()->getCustomer()->getId());
            $this->setBillingAddress($address);
        }
        if (!$this->getQuote()->isVirtual() && $this->getShippingAddress()->getSaveInAddressBook()) {
            $this->_prepareCustomerAddress($this->getQuote()->getCustomer(), $this->getShippingAddress());
            $address = $this->getShippingAddress()->setCustomerId($this->getQuote()->getCustomer()->getId());
            $this->setShippingAddress($address);
        }
        $this->getBillingAddress()->setCustomerId($customer->getId());
        $this->getQuote()->updateCustomerData($this->getQuote()->getCustomer());

        $customer = $this->getQuote()->getCustomer();
        $origAddresses = $customer->getAddresses(); // save original addresses
        $customer->setAddresses([]);
        $customerData = $this->customerMapper->toFlatArray($customer);
        $customer->setAddresses($origAddresses); // restore original addresses
        foreach ($this->_createCustomerForm($customer)->getUserAttributes() as $attribute) {
            if (isset($customerData[$attribute->getAttributeCode()])) {
                $quoteCode = sprintf('customer_%s', $attribute->getAttributeCode());
                $this->getQuote()->setData($quoteCode, $customerData[$attribute->getAttributeCode()]);
            }
        }

        return $this;
    }

    /**
     * Create customer address and save it in the quote so that it can be used to persist later.
     *
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     * @param \Magento\Quote\Model\Quote\Address $quoteCustomerAddress
     * @return void
     * @throws \InvalidArgumentException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function _prepareCustomerAddress($customer, $quoteCustomerAddress)
    {
        // Possible that customerId is null for new customers
        $quoteCustomerAddress->setCustomerId($customer->getId());
        $customerAddress = $quoteCustomerAddress->exportCustomerAddress();
        $quoteAddressId = $quoteCustomerAddress->getCustomerAddressId();
        $addressType = $quoteCustomerAddress->getAddressType();
        if ($quoteAddressId) {
            /** Update existing address */
            $existingAddressDataObject = $this->addressRepository->getById($quoteAddressId);
            /** Update customer address data */
            $this->dataObjectHelper->mergeDataObjects(
                get_class($existingAddressDataObject),
                $existingAddressDataObject,
                $customerAddress
            );
            $customerAddress = $existingAddressDataObject;
        } elseif ($addressType == \Magento\Quote\Model\Quote\Address::ADDRESS_TYPE_SHIPPING) {
            try {
                $billingAddressDataObject = $this->accountManagement->getDefaultBillingAddress($customer->getId());
                // phpcs:ignore Magento2.CodeAnalysis.EmptyBlock
            } catch (\Exception $e) {
                /** Billing address does not exist. */
            }
            $isShippingAsBilling = $quoteCustomerAddress->getSameAsBilling();
            if (isset($billingAddressDataObject) && $isShippingAsBilling) {
                /** Set existing billing address as default shipping */
                $customerAddress = $billingAddressDataObject;
                $customerAddress->setIsDefaultShipping(true);
            }
        }

        switch ($addressType) {
            case \Magento\Quote\Model\Quote\Address::ADDRESS_TYPE_BILLING:
                if ($customer->getDefaultBilling() === null) {
                    $customerAddress->setIsDefaultBilling(true);
                }
                break;
            case \Magento\Quote\Model\Quote\Address::ADDRESS_TYPE_SHIPPING:
                if ($customer->getDefaultShipping() === null) {
                    $customerAddress->setIsDefaultShipping(true);
                }
                break;
            default:
                throw new \InvalidArgumentException('Customer address type is invalid.');
        }
        $this->getQuote()->setCustomer($customer);
        $this->getQuote()->addCustomerAddress($customerAddress);
    }

    /**
     * Prepare item options
     *
     * @return $this
     */
    protected function _prepareQuoteItems()
    {
        $store = $this->getQuote()->getStore();
        $this->storeManager->setCurrentStore($store);
        foreach ($this->getQuote()->getAllItems() as $item) {
            $options = [];
            $productOptions = $item->getProduct()->getTypeInstance()->getOrderOptions($item->getProduct());
            if ($productOptions) {
                $productOptions['info_buyRequest']['options'] = $this->_prepareOptionsForRequest($item);
                $options = $productOptions;
            }
            $addOptions = $item->getOptionByCode('additional_options');
            if ($addOptions) {
                $options['additional_options'] = $this->serializer->unserialize($addOptions->getValue());
            }
            $item->setProductOrderOptions($options);
        }
        return $this;
    }

    /**
     * Create new order
     *
     * @return Order
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function createOrder()
    {
        $this->_prepareCustomer();
        $this->_validate();
        $quote = $this->getQuote();

        $this->_prepareQuoteItems();

        $orderData = $this->beforeSubmit($quote);
        $order = $this->quoteManagement->submit($quote, $orderData);
        $this->afterSubmit($order);

        if ($this->getSendConfirmation() && !$order->getEmailSent()) {
            $this->emailSender->send($order);
        }

        $this->_eventManager->dispatch('checkout_submit_all_after', ['order' => $order, 'quote' => $quote]);

        $this->removeTransferredItems();

        return $order;
    }

    /**
     * Prepare and retrieve order data before submitting a quote for order creation.
     *
     * @param Quote $quote
     * @return array
     */
    private function beforeSubmit(Quote $quote)
    {
        $orderData = [];
        if ($this->getSession()->getReordered() || $this->getSession()->getOrder()->getId()) {
            $oldOrder = $this->getSession()->getOrder();
            $oldOrder = $oldOrder->getId() ?
                $oldOrder : $this->orderRepositoryInterface->get($this->getSession()->getReordered());
            $originalId = $oldOrder->getOriginalIncrementId();
            if (!$originalId) {
                $originalId = $oldOrder->getIncrementId();
            }
            $orderData = [
                'original_increment_id' => $originalId,
                'relation_parent_id' => $oldOrder->getId(),
                'relation_parent_real_id' => $oldOrder->getIncrementId(),
                'edit_increment' => $oldOrder->getEditIncrement() + 1,
                'increment_id' => $originalId . '-' . ($oldOrder->getEditIncrement() + 1)
            ];
            $quote->setReservedOrderId($orderData['increment_id']);
        }

        return $orderData;
    }

    /**
     * Process old order after submission.
     *
     * @param Order $order
     * @return void
     * @throws \Exception
     */
    private function afterSubmit(Order $order)
    {
        if ($this->getSession()->getReordered() || $this->getSession()->getOrder()->getId()) {
            $oldOrder = $this->getSession()->getOrder();
            $oldOrder = $oldOrder->getId() ?
                $oldOrder : $this->orderRepositoryInterface->get($this->getSession()->getReordered());
            $oldOrder->setRelationChildId($order->getId());
            $oldOrder->setRelationChildRealId($order->getIncrementId());
            $oldOrder->save();
            if ($this->getSession()->getOrder()->getId()) {
                $this->orderManagement->cancel($oldOrder->getEntityId());
            }
            $order->save();
        }
    }

    /**
     * Remove items that were transferred into shopping cart from their original sources (cart, wishlist, ...)
     *
     * @return void
     */
    private function removeTransferredItems(): void
    {
        try {
            if (is_array($this->getSession()->getTransferredItems())) {
                foreach ($this->getSession()->getTransferredItems() as $from => $itemIds) {
                    foreach ($itemIds as $itemId) {
                        $this->removeItem($itemId, $from);
                    }
                }
                $this->recollectCart();
            }
        } catch (\Throwable $exception) {
            $this->_logger->error($exception);
        }
    }

    /**
     * Restore items that were transferred from ordered items to their original sources (cart, wishlist, ...)
     *
     * @param string $area
     * @param \Magento\Quote\Model\Quote\Item[]|\Magento\Wishlist\Model\Item[] $items
     * @return bool
     */
    private function restoreTransferredItem(string $area, array $items): bool
    {
        $transferredItems = $this->_session->getTransferredItems() ?? [];
        if (!isset($transferredItems[$area])) {
            return false;
        }
        $itemToRestore = array_intersect_key($items, $transferredItems[$area]);
        $itemToRestoreId = array_key_first($itemToRestore);

        if ($itemToRestoreId) {
            unset($transferredItems[$area][$itemToRestoreId]);
            $this->_session->setTransferredItems($transferredItems);
            return true;
        }
        return false;
    }

    /**
     * Validate quote data before order creation
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _validate()
    {
        if (!$this->getSession()->getStore()->getId()) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Please select a store'));
        }
        $items = $this->getQuote()->getAllItems();

        if (count($items) === 0) {
            $this->_errors[] = __('Please specify order items.');
        }

        $errors = [];
        foreach ($items as $item) {
            /** @var \Magento\Quote\Model\Quote\Item $item */
            $messages = $item->getMessage(false);
            if ($item->getHasError() && is_array($messages) && !empty($messages)) {
                $errors[] = $messages;
            }
        }

        $this->_errors = array_merge([], $this->_errors, ...$errors);

        if (!$this->getQuote()->isVirtual()) {
            if (!$this->getQuote()->getShippingAddress()->getShippingMethod()) {
                $this->_errors[] = __('The shipping method is missing. Select the shipping method and try again.');
            }
        }

        if (!$this->getQuote()->getPayment()->getMethod()) {
            $this->_errors[] = __("The payment method isn't selected. Enter the payment method and try again.");
        } else {
            $method = $this->getQuote()->getPayment()->getMethodInstance();
            if (!$method->isAvailable($this->getQuote())) {
                $this->_errors[] = __('This payment method is not available.');
            } else {
                try {
                    $method->validate();
                } catch (\Magento\Framework\Exception\LocalizedException $e) {
                    $this->_errors[] = $e->getMessage();
                }
            }
        }
        if (!empty($this->_errors)) {
            /** @var LoggerInterface $logger */
            $logger = ObjectManager::getInstance()->get(LoggerInterface::class);
            foreach ($this->_errors as $error) {
                $logger->error($error);
                $this->messageManager->addErrorMessage($error);
            }

            throw new \Magento\Framework\Exception\LocalizedException(__('Validation is failed.'));
        }

        return $this;
    }

    /**
     * Retrieve new customer email.
     *
     * @return string
     */
    protected function _getNewCustomerEmail()
    {
        $email = $this->getData('account/email');

        if ($email || $this->isEmailRequired()) {
            return $email;
        }

        return $this->generateEmail();
    }

    /**
     * Check email is require
     *
     * @return bool
     */
    private function isEmailRequired(): bool
    {
        return (bool)$this->_scopeConfig->getValue(
            self::XML_PATH_EMAIL_REQUIRED_CREATE_ORDER,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->_session->getStore()->getId()
        );
    }

    /**
     * Generate Email
     *
     * @return string
     */
    private function generateEmail(): string
    {
        $host = $this->_scopeConfig->getValue(
            self::XML_PATH_DEFAULT_EMAIL_DOMAIN,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $account = time();
        $email = $account . '@' . $host;
        $account = $this->getData('account');
        $account['email'] = $email;
        $this->setData('account', $account);

        return $email;
    }

    /**
     * Checks id shipping and billing addresses are equal.
     *
     * @param Order $order
     * @return bool
     */
    private function isAddressesAreEqual(Order $order)
    {
        $shippingAddress = $order->getShippingAddress();
        $billingAddress = $order->getBillingAddress();
        $shippingData = $this->dataObjectConverter->toFlatArray($shippingAddress, [], OrderAddressInterface::class);
        $billingData = $this->dataObjectConverter->toFlatArray($billingAddress, [], OrderAddressInterface::class);
        unset(
            $shippingData['address_type'],
            $shippingData['entity_id'],
            $billingData['address_type'],
            $billingData['entity_id']
        );
        if (isset($shippingData['customer_address_id']) && !isset($billingData['customer_address_id'])) {
            unset($shippingData['customer_address_id']);
        }

        return $shippingData == $billingData;
    }

    /**
     * Set $buyRequest with formatted product options.
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param object $buyRequest
     * @param array $productOptions
     * @return $this
     */
    private function formattedOptions(\Magento\Catalog\Model\Product $product, $buyRequest, $productOptions)
    {
        if ($productOptions !== null && !empty($productOptions['options'])) {
            $formattedOptions = [];
            foreach ($productOptions['options'] as $option) {
                if (in_array($option['option_type'], ['date', 'date_time', 'time', 'file'])) {
                    $product->setSkipCheckRequiredOption(false);
                    if ($option['option_type'] === 'file') {
                        try {
                            $formattedOptions[$option['option_id']] =
                                $this->serializer->unserialize($option['option_value']);
                            continue;
                        } catch (\InvalidArgumentException $exception) {
                            //log the exception as warning
                            $this->_logger->warning($exception);
                        }
                    }
                    $formattedOptions[$option['option_id']] =
                        $buyRequest->getDataByKey('options')[$option['option_id']];
                    continue;
                }

                $formattedOptions[$option['option_id']] = $option['option_value'];
            }
            if (!empty($formattedOptions)) {
                $buyRequest->setData('options', $formattedOptions);
            }
        }
        return $this;
    }

    /**
     * Remove cart from transferred items and update the qty.
     *
     * @param int|null|Item $cartItem
     * @param int $itemId
     * @return void
     */
    private function removeCartTransferredItemsAndUpdateQty(int|null|Item $cartItem, int $itemId)
    {
        $removeCartTransferredItems = $this->getSession()->getTransferredItems() ?? [];
        if (isset($removeCartTransferredItems['cart'])) {
            $removeTransferredItemKey = array_search($cartItem->getId(), $removeCartTransferredItems['cart']);
            if ($removeCartTransferredItems['cart'][$removeTransferredItemKey]) {
                $cartItem->clearMessage();
                $cartItem->setHasError(false);
                if (isset($this->request->get('item')[$itemId]['qty'])) {
                    $qty = $this->request->get('item')[$itemId]['qty'];
                    $cartItem->setQty($qty);
                }

                if ($cartItem->getHasError()) {
                    throw new LocalizedException(__($cartItem->getMessage()));
                }
                unset($removeCartTransferredItems['cart'][$removeTransferredItemKey]);
            }
            $this->getSession()->setTransferredItems($removeCartTransferredItems);
        }
    }
}
