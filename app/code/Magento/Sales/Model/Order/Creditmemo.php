<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Model\Order;

use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Sales\Api\Data\CreditmemoInterface;
use Magento\Sales\Model\AbstractModel;
use Magento\Sales\Model\EntityInterface;
use Magento\Sales\Model\Order\InvoiceFactory;

/**
 * Order creditmemo model
 *
 * @api
 * @method \Magento\Sales\Model\Order\Invoice setSendEmail(bool $value)
 * @method \Magento\Sales\Model\Order\Invoice setCustomerNote(string $value)
 * @method string getCustomerNote()
 * @method \Magento\Sales\Model\Order\Invoice setCustomerNoteNotify(bool $value)
 * @method bool getCustomerNoteNotify()
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 100.0.2
 */
class Creditmemo extends AbstractModel implements EntityInterface, CreditmemoInterface
{
    const STATE_OPEN = 1;

    const STATE_REFUNDED = 2;

    const STATE_CANCELED = 3;

    const REPORT_DATE_TYPE_ORDER_CREATED = 'order_created';

    const REPORT_DATE_TYPE_REFUND_CREATED = 'refund_created';

    /**
     * Identifier for order history item
     *
     * @var string
     */
    protected $entityType = 'creditmemo';

    /**
     * @var array
     */
    protected static $_states;

    /**
     * @var \Magento\Sales\Model\Order
     */
    protected $_order;

    /**
     * Calculator instances for delta rounding of prices
     *
     * @var array
     */
    protected $_calculators = [];

    /**
     * @var string
     */
    protected $_eventPrefix = 'sales_order_creditmemo';

    /**
     * @var string
     */
    protected $_eventObject = 'creditmemo';

    /**
     * @var \Magento\Sales\Model\Order\Creditmemo\Config
     */
    protected $_creditmemoConfig;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $_orderFactory;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Creditmemo\Item\CollectionFactory
     */
    protected $_cmItemCollectionFactory;

    /**
     * @var \Magento\Framework\Math\CalculatorFactory
     */
    protected $_calculatorFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Sales\Model\Order\Creditmemo\CommentFactory
     */
    protected $_commentFactory;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Creditmemo\Comment\CollectionFactory
     */
    protected $_commentCollectionFactory;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @var InvoiceFactory
     */
    private $invoiceFactory;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param Creditmemo\Config $creditmemoConfig
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Sales\Model\ResourceModel\Order\Creditmemo\Item\CollectionFactory $cmItemCollectionFactory
     * @param \Magento\Framework\Math\CalculatorFactory $calculatorFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param Creditmemo\CommentFactory $commentFactory
     * @param \Magento\Sales\Model\ResourceModel\Order\Creditmemo\Comment\CollectionFactory $commentCollectionFactory
     * @param PriceCurrencyInterface $priceCurrency
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     * @param InvoiceFactory $invoiceFactory
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        \Magento\Sales\Model\Order\Creditmemo\Config $creditmemoConfig,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Sales\Model\ResourceModel\Order\Creditmemo\Item\CollectionFactory $cmItemCollectionFactory,
        \Magento\Framework\Math\CalculatorFactory $calculatorFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Sales\Model\Order\Creditmemo\CommentFactory $commentFactory,
        \Magento\Sales\Model\ResourceModel\Order\Creditmemo\Comment\CollectionFactory $commentCollectionFactory,
        PriceCurrencyInterface $priceCurrency,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = [],
        InvoiceFactory $invoiceFactory = null
    ) {
        $this->_creditmemoConfig = $creditmemoConfig;
        $this->_orderFactory = $orderFactory;
        $this->_cmItemCollectionFactory = $cmItemCollectionFactory;
        $this->_calculatorFactory = $calculatorFactory;
        $this->_storeManager = $storeManager;
        $this->_commentFactory = $commentFactory;
        $this->_commentCollectionFactory = $commentCollectionFactory;
        $this->priceCurrency = $priceCurrency;
        $this->invoiceFactory = $invoiceFactory ?: ObjectManager::getInstance()->get(InvoiceFactory::class);
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
     * Initialize creditmemo resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Magento\Sales\Model\ResourceModel\Order\Creditmemo::class);
    }

    /**
     * Retrieve Creditmemo configuration model
     *
     * @return \Magento\Sales\Model\Order\Creditmemo\Config
     */
    public function getConfig()
    {
        return $this->_creditmemoConfig;
    }

    /**
     * Retrieve creditmemo store instance
     *
     * @return \Magento\Store\Model\Store
     */
    public function getStore()
    {
        return $this->getOrder()->getStore();
    }

    /**
     * Declare order for creditmemo
     *
     * @param \Magento\Sales\Model\Order $order
     * @return $this
     */
    public function setOrder(\Magento\Sales\Model\Order $order)
    {
        $this->_order = $order;
        $this->setOrderId($order->getId())->setStoreId($order->getStoreId());
        return $this;
    }

    /**
     * Retrieve the order the creditmemo for created for
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        if (!$this->_order instanceof \Magento\Sales\Model\Order) {
            $this->_order = $this->_orderFactory->create()->load($this->getOrderId());
        }
        return $this->_order->setHistoryEntityName($this->entityType);
    }

    /**
     * Return order entity type
     *
     * @return string
     */
    public function getEntityType()
    {
        return $this->entityType;
    }

    /**
     * Retrieve billing address
     *
     * @return Address
     */
    public function getBillingAddress()
    {
        return $this->getOrder()->getBillingAddress();
    }

    /**
     * Retrieve shipping address
     *
     * @return Address
     */
    public function getShippingAddress()
    {
        return $this->getOrder()->getShippingAddress();
    }

    /**
     * @return mixed
     */
    public function getItemsCollection()
    {
        $collection = $this->_cmItemCollectionFactory->create()->setCreditmemoFilter($this->getId());

        if ($this->getId()) {
            foreach ($collection as $item) {
                $item->setCreditmemo($this);
            }
        }
        return $collection;
    }

    /**
     * @return \Magento\Sales\Model\Order\Creditmemo\Item[]
     */
    public function getAllItems()
    {
        $items = [];
        foreach ($this->getItems() as $item) {
            if (!$item->isDeleted()) {
                $items[] = $item;
            }
        }
        return $items;
    }

    /**
     * @param mixed $itemId
     * @return mixed
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
     * Returns credit memo item by its order id
     *
     * @param mixed $orderId
     * @return \Magento\Sales\Model\Order\Creditmemo\Item|bool
     */
    public function getItemByOrderId($orderId)
    {
        foreach ($this->getItemsCollection() as $item) {
            if ($item->getOrderItemId() == $orderId) {
                return $item;
            }
        }
        return false;
    }

    /**
     * @param \Magento\Sales\Model\Order\Creditmemo\Item $item
     * @return $this
     */
    public function addItem(\Magento\Sales\Model\Order\Creditmemo\Item $item)
    {
        $item->setCreditmemo($this)->setParentId($this->getId())->setStoreId($this->getStoreId());
        if (!$item->getId()) {
            $this->setItems(array_merge($this->getItems(), [$item]));
        }
        return $this;
    }

    /**
     * Creditmemo totals collecting
     *
     * @return $this
     */
    public function collectTotals()
    {
        foreach ($this->getConfig()->getTotalModels() as $model) {
            $model->collect($this);
        }
        return $this;
    }

    /**
     * Round price considering delta
     *
     * @param float $price
     * @param string $type
     * @param bool $negative Indicates if we perform addition (true) or subtraction (false) of rounded value
     * @return float
     */
    public function roundPrice($price, $type = 'regular', $negative = false)
    {
        if ($price) {
            if (!isset($this->_calculators[$type])) {
                $this->_calculators[$type] = $this->_calculatorFactory->create(['scope' => $this->getStore()]);
            }
            $price = $this->_calculators[$type]->deltaRound($price, $negative);
        }
        return $price;
    }

    /**
     * @return bool
     */
    public function canRefund()
    {
        if ($this->getState() != self::STATE_CANCELED &&
            $this->getState() != self::STATE_REFUNDED &&
            $this->getOrder()->getPayment()->canRefund()
        ) {
            return true;
        }
        return false;
    }

    /**
     * Returns assigned invoice
     *
     * @return Invoice|null
     */
    public function getInvoice()
    {
        if (!$this->getData('invoice') instanceof \Magento\Sales\Api\Data\InvoiceInterface && $this->getInvoiceId()) {
            $this->setInvoice($this->invoiceFactory->create()->load($this->getInvoiceId()));
        }
        return $this->getData('invoice');
    }

    /**
     * Sets invoice
     *
     * @param Invoice $invoice
     * @return $this
     */
    public function setInvoice(Invoice $invoice)
    {
        $this->setData('invoice', $invoice);
        return $this;
    }

    /**
     * Check creditmemo cancel action availability
     *
     * @return bool
     */
    public function canCancel()
    {
        return $this->getState() == self::STATE_OPEN;
    }

    /**
     * Check invoice void action availability
     *
     * @return bool
     */
    public function canVoid()
    {
        return false;
    }

    /**
     * Retrieve Creditmemo states array
     *
     * @return array
     */
    public static function getStates()
    {
        if (static::$_states === null) {
            static::$_states = [
                self::STATE_OPEN => __('Pending'),
                self::STATE_REFUNDED => __('Refunded'),
                self::STATE_CANCELED => __('Canceled'),
            ];
        }
        return static::$_states;
    }

    /**
     * Retrieve Creditmemo state name by state identifier
     *
     * @param   int $stateId
     * @return \Magento\Framework\Phrase
     */
    public function getStateName($stateId = null)
    {
        if ($stateId === null) {
            $stateId = $this->getState();
        }

        if (static::$_states === null) {
            static::getStates();
        }
        if (isset(static::$_states[$stateId])) {
            return static::$_states[$stateId];
        }
        return __('Unknown State');
    }

    /**
     * @param float $amount
     * @return $this
     */
    public function setShippingAmount($amount)
    {
        return $this->setData(CreditmemoInterface::SHIPPING_AMOUNT, $amount);
    }

    /**
     * @param string $amount
     * @return $this
     */
    public function setAdjustmentPositive($amount)
    {
        $amount = trim($amount);
        if (substr($amount, -1) == '%') {
            $amount = (double)substr($amount, 0, -1);
            $amount = $this->getOrder()->getGrandTotal() * $amount / 100;
        }

        $amount = $this->priceCurrency->round($amount);
        $this->setData('base_adjustment_positive', $amount);

        $amount = $this->priceCurrency->round($amount * $this->getOrder()->getBaseToOrderRate());
        $this->setData('adjustment_positive', $amount);
        return $this;
    }

    /**
     * @param string $amount
     * @return $this
     */
    public function setAdjustmentNegative($amount)
    {
        $amount = trim($amount);
        if (substr($amount, -1) == '%') {
            $amount = (double)substr($amount, 0, -1);
            $amount = $this->getOrder()->getGrandTotal() * $amount / 100;
        }

        $amount = $this->priceCurrency->round($amount);
        $this->setData('base_adjustment_negative', $amount);

        $amount = $this->priceCurrency->round($amount * $this->getOrder()->getBaseToOrderRate());
        $this->setData('adjustment_negative', $amount);
        return $this;
    }

    /**
     * Checking if the creditmemo is last
     *
     * @return bool
     */
    public function isLast()
    {
        $items = $this->getAllItems();
        foreach ($items as $item) {
            if (!$item->isLast()) {
                return false;
            }
        }

        if (empty($items)) {
            $order = $this->getOrder();
            if ($order) {
                foreach ($order->getItems() as $orderItem) {
                    if ($orderItem->canRefund()) {
                        return false;
                    }
                }
            }
        }

        return true;
    }

    /**
     * Adds comment to credit memo with additional possibility to send it to customer via email
     * and show it in customer account
     *
     * @param \Magento\Sales\Model\Order\Creditmemo\Comment|string $comment
     * @param bool $notify
     * @param bool $visibleOnFront
     *
     * @return \Magento\Sales\Model\Order\Creditmemo\Comment
     */
    public function addComment($comment, $notify = false, $visibleOnFront = false)
    {
        if (!$comment instanceof \Magento\Sales\Model\Order\Creditmemo\Comment) {
            $comment = $this->_commentFactory->create()->setComment(
                $comment
            )->setIsCustomerNotified(
                $notify
            )->setIsVisibleOnFront(
                $visibleOnFront
            );
        }
        $comment->setCreditmemo($this)->setParentId($this->getId())->setStoreId($this->getStoreId());
        $this->setComments(array_merge($this->getComments(), [$comment]));
        return $comment;
    }

    /**
     * @param bool $reload
     * @return \Magento\Sales\Model\ResourceModel\Order\Creditmemo\Comment\Collection
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getCommentsCollection($reload = false)
    {
        $collection = $this->_commentCollectionFactory->create()->setCreditmemoFilter($this->getId())
            ->setCreatedAtOrder();

        if ($this->getId()) {
            foreach ($collection as $comment) {
                $comment->setCreditmemo($this);
            }
        }
        return $collection;
    }

    /**
     * Get creditmemos collection filtered by $filter
     *
     * @param array|null $filter
     * @return \Magento\Sales\Model\ResourceModel\Order\Creditmemo\Collection
     */
    public function getFilteredCollectionItems($filter = null)
    {
        return $this->getResourceCollection()->getFiltered($filter);
    }

    /**
     * Returns increment id
     *
     * @return string
     */
    public function getIncrementId()
    {
        return $this->getData('increment_id');
    }

    /**
     * @return bool
     */
    public function isValidGrandTotal()
    {
        return !($this->getGrandTotal() <= 0 && !$this->getAllowZeroGrandTotal());
    }

    /**
     * Return creditmemo items
     *
     * @return \Magento\Sales\Api\Data\CreditmemoItemInterface[]
     */
    public function getItems()
    {
        if ($this->getData(CreditmemoInterface::ITEMS) == null) {
            $this->setData(
                CreditmemoInterface::ITEMS,
                $this->getItemsCollection()->getItems()
            );
        }
        return $this->getData(CreditmemoInterface::ITEMS);
    }

    /**
     * Return creditmemo comments
     *
     * @return \Magento\Sales\Api\Data\CreditmemoCommentInterface[]|null
     */
    public function getComments()
    {
        if ($this->getData(CreditmemoInterface::COMMENTS) == null) {
            $this->setData(
                CreditmemoInterface::COMMENTS,
                $this->getCommentsCollection()->getItems()
            );
        }
        return $this->getData(CreditmemoInterface::COMMENTS);
    }

    //@codeCoverageIgnoreStart

    /**
     * Returns discount_description
     *
     * @return string
     */
    public function getDiscountDescription()
    {
        return $this->getData(CreditmemoInterface::DISCOUNT_DESCRIPTION);
    }

    /**
     * {@inheritdoc}
     */
    public function setItems($items)
    {
        return $this->setData(CreditmemoInterface::ITEMS, $items);
    }

    /**
     * Returns adjustment
     *
     * @return float
     */
    public function getAdjustment()
    {
        return $this->getData(CreditmemoInterface::ADJUSTMENT);
    }

    /**
     * Returns adjustment_negative
     *
     * @return float
     */
    public function getAdjustmentNegative()
    {
        return $this->getData(CreditmemoInterface::ADJUSTMENT_NEGATIVE);
    }

    /**
     * Returns adjustment_positive
     *
     * @return float
     */
    public function getAdjustmentPositive()
    {
        return $this->getData(CreditmemoInterface::ADJUSTMENT_POSITIVE);
    }

    /**
     * Returns base_adjustment
     *
     * @return float
     */
    public function getBaseAdjustment()
    {
        return $this->getData(CreditmemoInterface::BASE_ADJUSTMENT);
    }

    /**
     * Returns base_adjustment_negative
     *
     * @return float
     */
    public function getBaseAdjustmentNegative()
    {
        return $this->getData(CreditmemoInterface::BASE_ADJUSTMENT_NEGATIVE);
    }

    /**
     * Set base_adjustment_negative
     *
     * @param float $baseAdjustmentNegative
     * @return $this
     */
    public function setBaseAdjustmentNegative($baseAdjustmentNegative)
    {
        return $this->setData(CreditmemoInterface::BASE_ADJUSTMENT_NEGATIVE, $baseAdjustmentNegative);
    }

    /**
     * Returns base_adjustment_positive
     *
     * @return float
     */
    public function getBaseAdjustmentPositive()
    {
        return $this->getData(CreditmemoInterface::BASE_ADJUSTMENT_POSITIVE);
    }

    /**
     * Set base_adjustment_positive
     *
     * @param float $baseAdjustmentPositive
     * @return $this
     */
    public function setBaseAdjustmentPositive($baseAdjustmentPositive)
    {
        return $this->setData(CreditmemoInterface::BASE_ADJUSTMENT_POSITIVE, $baseAdjustmentPositive);
    }

    /**
     * Returns base_currency_code
     *
     * @return string
     */
    public function getBaseCurrencyCode()
    {
        return $this->getData(CreditmemoInterface::BASE_CURRENCY_CODE);
    }

    /**
     * Returns base_discount_amount
     *
     * @return float
     */
    public function getBaseDiscountAmount()
    {
        return $this->getData(CreditmemoInterface::BASE_DISCOUNT_AMOUNT);
    }

    /**
     * Returns base_grand_total
     *
     * @return float
     */
    public function getBaseGrandTotal()
    {
        return $this->getData(CreditmemoInterface::BASE_GRAND_TOTAL);
    }

    /**
     * Returns base_discount_tax_compensation_amount
     *
     * @return float|null
     */
    public function getBaseDiscountTaxCompensationAmount()
    {
        return $this->getData(CreditmemoInterface::BASE_DISCOUNT_TAX_COMPENSATION_AMOUNT);
    }

    /**
     * Returns base_shipping_amount
     *
     * @return float
     */
    public function getBaseShippingAmount()
    {
        return $this->getData(CreditmemoInterface::BASE_SHIPPING_AMOUNT);
    }

    /**
     * Returns base_shipping_discount_tax_compensation_amnt
     *
     * @return float|null
     */
    public function getBaseShippingDiscountTaxCompensationAmnt()
    {
        return $this->getData(CreditmemoInterface::BASE_SHIPPING_DISCOUNT_TAX_COMPENSATION_AMNT);
    }

    /**
     * Returns base_shipping_incl_tax
     *
     * @return float
     */
    public function getBaseShippingInclTax()
    {
        return $this->getData(CreditmemoInterface::BASE_SHIPPING_INCL_TAX);
    }

    /**
     * Returns base_shipping_tax_amount
     *
     * @return float
     */
    public function getBaseShippingTaxAmount()
    {
        return $this->getData(CreditmemoInterface::BASE_SHIPPING_TAX_AMOUNT);
    }

    /**
     * Returns base_subtotal
     *
     * @return float
     */
    public function getBaseSubtotal()
    {
        return $this->getData(CreditmemoInterface::BASE_SUBTOTAL);
    }

    /**
     * Returns base_subtotal_incl_tax
     *
     * @return float
     */
    public function getBaseSubtotalInclTax()
    {
        return $this->getData(CreditmemoInterface::BASE_SUBTOTAL_INCL_TAX);
    }

    /**
     * Returns base_tax_amount
     *
     * @return float
     */
    public function getBaseTaxAmount()
    {
        return $this->getData(CreditmemoInterface::BASE_TAX_AMOUNT);
    }

    /**
     * Returns base_to_global_rate
     *
     * @return float
     */
    public function getBaseToGlobalRate()
    {
        return $this->getData(CreditmemoInterface::BASE_TO_GLOBAL_RATE);
    }

    /**
     * Returns base_to_order_rate
     *
     * @return float
     */
    public function getBaseToOrderRate()
    {
        return $this->getData(CreditmemoInterface::BASE_TO_ORDER_RATE);
    }

    /**
     * Returns billing_address_id
     *
     * @return int
     */
    public function getBillingAddressId()
    {
        return $this->getData(CreditmemoInterface::BILLING_ADDRESS_ID);
    }

    /**
     * Returns created_at
     *
     * @return string
     */
    public function getCreatedAt()
    {
        return $this->getData(CreditmemoInterface::CREATED_AT);
    }

    /**
     * {@inheritdoc}
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(CreditmemoInterface::CREATED_AT, $createdAt);
    }

    /**
     * Returns creditmemo_status
     *
     * @return int
     */
    public function getCreditmemoStatus()
    {
        return $this->getData(CreditmemoInterface::CREDITMEMO_STATUS);
    }

    /**
     * Returns discount_amount
     *
     * @return float
     */
    public function getDiscountAmount()
    {
        return $this->getData(CreditmemoInterface::DISCOUNT_AMOUNT);
    }

    /**
     * Returns email_sent
     *
     * @return int
     */
    public function getEmailSent()
    {
        return $this->getData(CreditmemoInterface::EMAIL_SENT);
    }

    /**
     * Returns global_currency_code
     *
     * @return string
     */
    public function getGlobalCurrencyCode()
    {
        return $this->getData(CreditmemoInterface::GLOBAL_CURRENCY_CODE);
    }

    /**
     * Returns grand_total
     *
     * @return float
     */
    public function getGrandTotal()
    {
        return $this->getData(CreditmemoInterface::GRAND_TOTAL);
    }

    /**
     * Returns discount_tax_compensation_amount
     *
     * @return float|null
     */
    public function getDiscountTaxCompensationAmount()
    {
        return $this->getData(CreditmemoInterface::DISCOUNT_TAX_COMPENSATION_AMOUNT);
    }

    /**
     * Returns invoice_id
     *
     * @return int
     */
    public function getInvoiceId()
    {
        return $this->getData(CreditmemoInterface::INVOICE_ID);
    }

    /**
     * Returns order_currency_code
     *
     * @return string
     */
    public function getOrderCurrencyCode()
    {
        return $this->getData(CreditmemoInterface::ORDER_CURRENCY_CODE);
    }

    /**
     * Returns order_id
     *
     * @return int
     */
    public function getOrderId()
    {
        return $this->getData(CreditmemoInterface::ORDER_ID);
    }

    /**
     * Returns shipping_address_id
     *
     * @return int
     */
    public function getShippingAddressId()
    {
        return $this->getData(CreditmemoInterface::SHIPPING_ADDRESS_ID);
    }

    /**
     * Returns shipping_amount
     *
     * @return float
     */
    public function getShippingAmount()
    {
        return $this->getData(CreditmemoInterface::SHIPPING_AMOUNT);
    }

    /**
     * Returns shipping_discount_tax_compensation_amount
     *
     * @return float|null
     */
    public function getShippingDiscountTaxCompensationAmount()
    {
        return $this->getData(CreditmemoInterface::SHIPPING_DISCOUNT_TAX_COMPENSATION_AMOUNT);
    }

    /**
     * Returns shipping_incl_tax
     *
     * @return float
     */
    public function getShippingInclTax()
    {
        return $this->getData(CreditmemoInterface::SHIPPING_INCL_TAX);
    }

    /**
     * Returns shipping_tax_amount
     *
     * @return float
     */
    public function getShippingTaxAmount()
    {
        return $this->getData(CreditmemoInterface::SHIPPING_TAX_AMOUNT);
    }

    /**
     * Returns state
     *
     * @return int
     */
    public function getState()
    {
        return $this->getData(CreditmemoInterface::STATE);
    }

    /**
     * Returns store_currency_code
     *
     * @return string
     */
    public function getStoreCurrencyCode()
    {
        return $this->getData(CreditmemoInterface::STORE_CURRENCY_CODE);
    }

    /**
     * Returns store_id
     *
     * @return int
     */
    public function getStoreId()
    {
        return $this->getData(CreditmemoInterface::STORE_ID);
    }

    /**
     * Returns store_to_base_rate
     *
     * @return float
     */
    public function getStoreToBaseRate()
    {
        return $this->getData(CreditmemoInterface::STORE_TO_BASE_RATE);
    }

    /**
     * Returns store_to_order_rate
     *
     * @return float
     */
    public function getStoreToOrderRate()
    {
        return $this->getData(CreditmemoInterface::STORE_TO_ORDER_RATE);
    }

    /**
     * Returns subtotal
     *
     * @return float
     */
    public function getSubtotal()
    {
        return $this->getData(CreditmemoInterface::SUBTOTAL);
    }

    /**
     * Returns subtotal_incl_tax
     *
     * @return float
     */
    public function getSubtotalInclTax()
    {
        return $this->getData(CreditmemoInterface::SUBTOTAL_INCL_TAX);
    }

    /**
     * Returns tax_amount
     *
     * @return float
     */
    public function getTaxAmount()
    {
        return $this->getData(CreditmemoInterface::TAX_AMOUNT);
    }

    /**
     * Returns transaction_id
     *
     * @return string
     */
    public function getTransactionId()
    {
        return $this->getData(CreditmemoInterface::TRANSACTION_ID);
    }

    /**
     * Sets the credit memo transaction ID.
     *
     * @param string $transactionId
     * @return $this
     */
    public function setTransactionId($transactionId)
    {
        return $this->setData(CreditmemoInterface::TRANSACTION_ID, $transactionId);
    }

    /**
     * Returns updated_at
     *
     * @return string
     */
    public function getUpdatedAt()
    {
        return $this->getData(CreditmemoInterface::UPDATED_AT);
    }

    /**
     * {@inheritdoc}
     */
    public function setComments($comments)
    {
        return $this->setData(CreditmemoInterface::COMMENTS, $comments);
    }

    /**
     * {@inheritdoc}
     */
    public function setStoreId($id)
    {
        return $this->setData(CreditmemoInterface::STORE_ID, $id);
    }

    /**
     * {@inheritdoc}
     */
    public function setBaseShippingTaxAmount($amount)
    {
        return $this->setData(CreditmemoInterface::BASE_SHIPPING_TAX_AMOUNT, $amount);
    }

    /**
     * {@inheritdoc}
     */
    public function setStoreToOrderRate($rate)
    {
        return $this->setData(CreditmemoInterface::STORE_TO_ORDER_RATE, $rate);
    }

    /**
     * {@inheritdoc}
     */
    public function setBaseDiscountAmount($amount)
    {
        return $this->setData(CreditmemoInterface::BASE_DISCOUNT_AMOUNT, $amount);
    }

    /**
     * {@inheritdoc}
     */
    public function setBaseToOrderRate($rate)
    {
        return $this->setData(CreditmemoInterface::BASE_TO_ORDER_RATE, $rate);
    }

    /**
     * {@inheritdoc}
     */
    public function setGrandTotal($amount)
    {
        return $this->setData(CreditmemoInterface::GRAND_TOTAL, $amount);
    }

    /**
     * {@inheritdoc}
     */
    public function setBaseSubtotalInclTax($amount)
    {
        return $this->setData(CreditmemoInterface::BASE_SUBTOTAL_INCL_TAX, $amount);
    }

    /**
     * {@inheritdoc}
     */
    public function setSubtotalInclTax($amount)
    {
        return $this->setData(CreditmemoInterface::SUBTOTAL_INCL_TAX, $amount);
    }

    /**
     * {@inheritdoc}
     */
    public function setBaseShippingAmount($amount)
    {
        return $this->setData(CreditmemoInterface::BASE_SHIPPING_AMOUNT, $amount);
    }

    /**
     * {@inheritdoc}
     */
    public function setStoreToBaseRate($rate)
    {
        return $this->setData(CreditmemoInterface::STORE_TO_BASE_RATE, $rate);
    }

    /**
     * {@inheritdoc}
     */
    public function setBaseToGlobalRate($rate)
    {
        return $this->setData(CreditmemoInterface::BASE_TO_GLOBAL_RATE, $rate);
    }

    /**
     * {@inheritdoc}
     */
    public function setBaseAdjustment($baseAdjustment)
    {
        return $this->setData(CreditmemoInterface::BASE_ADJUSTMENT, $baseAdjustment);
    }

    /**
     * {@inheritdoc}
     */
    public function setBaseSubtotal($amount)
    {
        return $this->setData(CreditmemoInterface::BASE_SUBTOTAL, $amount);
    }

    /**
     * {@inheritdoc}
     */
    public function setDiscountAmount($amount)
    {
        return $this->setData(CreditmemoInterface::DISCOUNT_AMOUNT, $amount);
    }

    /**
     * {@inheritdoc}
     */
    public function setSubtotal($amount)
    {
        return $this->setData(CreditmemoInterface::SUBTOTAL, $amount);
    }

    /**
     * {@inheritdoc}
     */
    public function setAdjustment($adjustment)
    {
        return $this->setData(CreditmemoInterface::ADJUSTMENT, $adjustment);
    }

    /**
     * {@inheritdoc}
     */
    public function setBaseGrandTotal($amount)
    {
        return $this->setData(CreditmemoInterface::BASE_GRAND_TOTAL, $amount);
    }

    /**
     * {@inheritdoc}
     */
    public function setBaseTaxAmount($amount)
    {
        return $this->setData(CreditmemoInterface::BASE_TAX_AMOUNT, $amount);
    }

    /**
     * {@inheritdoc}
     */
    public function setShippingTaxAmount($amount)
    {
        return $this->setData(CreditmemoInterface::SHIPPING_TAX_AMOUNT, $amount);
    }

    /**
     * {@inheritdoc}
     */
    public function setTaxAmount($amount)
    {
        return $this->setData(CreditmemoInterface::TAX_AMOUNT, $amount);
    }

    /**
     * {@inheritdoc}
     */
    public function setOrderId($id)
    {
        return $this->setData(CreditmemoInterface::ORDER_ID, $id);
    }

    /**
     * {@inheritdoc}
     */
    public function setEmailSent($emailSent)
    {
        return $this->setData(CreditmemoInterface::EMAIL_SENT, $emailSent);
    }

    /**
     * {@inheritdoc}
     */
    public function setCreditmemoStatus($creditmemoStatus)
    {
        return $this->setData(CreditmemoInterface::CREDITMEMO_STATUS, $creditmemoStatus);
    }

    /**
     * {@inheritdoc}
     */
    public function setState($state)
    {
        return $this->setData(CreditmemoInterface::STATE, $state);
    }

    /**
     * {@inheritdoc}
     */
    public function setShippingAddressId($id)
    {
        return $this->setData(CreditmemoInterface::SHIPPING_ADDRESS_ID, $id);
    }

    /**
     * {@inheritdoc}
     */
    public function setBillingAddressId($id)
    {
        return $this->setData(CreditmemoInterface::BILLING_ADDRESS_ID, $id);
    }

    /**
     * {@inheritdoc}
     */
    public function setInvoiceId($id)
    {
        return $this->setData(CreditmemoInterface::INVOICE_ID, $id);
    }

    /**
     * {@inheritdoc}
     */
    public function setStoreCurrencyCode($code)
    {
        return $this->setData(CreditmemoInterface::STORE_CURRENCY_CODE, $code);
    }

    /**
     * {@inheritdoc}
     */
    public function setOrderCurrencyCode($code)
    {
        return $this->setData(CreditmemoInterface::ORDER_CURRENCY_CODE, $code);
    }

    /**
     * {@inheritdoc}
     */
    public function setBaseCurrencyCode($code)
    {
        return $this->setData(CreditmemoInterface::BASE_CURRENCY_CODE, $code);
    }

    /**
     * {@inheritdoc}
     */
    public function setGlobalCurrencyCode($code)
    {
        return $this->setData(CreditmemoInterface::GLOBAL_CURRENCY_CODE, $code);
    }

    /**
     * {@inheritdoc}
     */
    public function setIncrementId($id)
    {
        return $this->setData(CreditmemoInterface::INCREMENT_ID, $id);
    }

    /**
     * {@inheritdoc}
     */
    public function setUpdatedAt($timestamp)
    {
        return $this->setData(CreditmemoInterface::UPDATED_AT, $timestamp);
    }

    /**
     * {@inheritdoc}
     */
    public function setDiscountTaxCompensationAmount($amount)
    {
        return $this->setData(CreditmemoInterface::DISCOUNT_TAX_COMPENSATION_AMOUNT, $amount);
    }

    /**
     * {@inheritdoc}
     */
    public function setBaseDiscountTaxCompensationAmount($amount)
    {
        return $this->setData(CreditmemoInterface::BASE_DISCOUNT_TAX_COMPENSATION_AMOUNT, $amount);
    }

    /**
     * {@inheritdoc}
     */
    public function setShippingDiscountTaxCompensationAmount($amount)
    {
        return $this->setData(CreditmemoInterface::SHIPPING_DISCOUNT_TAX_COMPENSATION_AMOUNT, $amount);
    }

    /**
     * {@inheritdoc}
     */
    public function setBaseShippingDiscountTaxCompensationAmnt($amnt)
    {
        return $this->setData(CreditmemoInterface::BASE_SHIPPING_DISCOUNT_TAX_COMPENSATION_AMNT, $amnt);
    }

    /**
     * {@inheritdoc}
     */
    public function setShippingInclTax($amount)
    {
        return $this->setData(CreditmemoInterface::SHIPPING_INCL_TAX, $amount);
    }

    /**
     * {@inheritdoc}
     */
    public function setBaseShippingInclTax($amount)
    {
        return $this->setData(CreditmemoInterface::BASE_SHIPPING_INCL_TAX, $amount);
    }

    /**
     * {@inheritdoc}
     */
    public function setDiscountDescription($description)
    {
        return $this->setData(CreditmemoInterface::DISCOUNT_DESCRIPTION, $description);
    }

    /**
     * {@inheritdoc}
     *
     * @return \Magento\Sales\Api\Data\CreditmemoExtensionInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * {@inheritdoc}
     *
     * @param \Magento\Sales\Api\Data\CreditmemoExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(\Magento\Sales\Api\Data\CreditmemoExtensionInterface $extensionAttributes)
    {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
    //@codeCoverageIgnoreEnd
}
