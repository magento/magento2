<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Sales\Model\Order;

use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Sales\Api\Data\CreditmemoInterface;
use Magento\Sales\Model\AbstractModel;
use Magento\Sales\Model\EntityInterface;

/**
 * Order creditmemo model
 *
 * @api
 * @method \Magento\Sales\Model\ResourceModel\Order\Creditmemo _getResource()
 * @method \Magento\Sales\Model\ResourceModel\Order\Creditmemo getResource()
 * @method \Magento\Sales\Model\Order\Invoice setSendEmail(bool $value)
 * @method \Magento\Sales\Model\Order\Invoice setCustomerNote(string $value)
 * @method string getCustomerNote()
 * @method \Magento\Sales\Model\Order\Invoice setCustomerNoteNotify(bool $value)
 * @method bool getCustomerNoteNotify()
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 2.0.0
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
     * @since 2.0.0
     */
    protected $entityType = 'creditmemo';

    /**
     * @var array
     * @since 2.0.0
     */
    protected static $_states;

    /**
     * @var \Magento\Sales\Model\Order
     * @since 2.0.0
     */
    protected $_order;

    /**
     * Calculator instances for delta rounding of prices
     *
     * @var array
     * @since 2.0.0
     */
    protected $_calculators = [];

    /**
     * @var string
     * @since 2.0.0
     */
    protected $_eventPrefix = 'sales_order_creditmemo';

    /**
     * @var string
     * @since 2.0.0
     */
    protected $_eventObject = 'creditmemo';

    /**
     * @var \Magento\Sales\Model\Order\Creditmemo\Config
     * @since 2.0.0
     */
    protected $_creditmemoConfig;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     * @since 2.0.0
     */
    protected $_orderFactory;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Creditmemo\Item\CollectionFactory
     * @since 2.0.0
     */
    protected $_cmItemCollectionFactory;

    /**
     * @var \Magento\Framework\Math\CalculatorFactory
     * @since 2.0.0
     */
    protected $_calculatorFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     * @since 2.0.0
     */
    protected $_storeManager;

    /**
     * @var \Magento\Sales\Model\Order\Creditmemo\CommentFactory
     * @since 2.0.0
     */
    protected $_commentFactory;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Creditmemo\Comment\CollectionFactory
     * @since 2.0.0
     */
    protected $_commentCollectionFactory;

    /**
     * @var PriceCurrencyInterface
     * @since 2.0.0
     */
    protected $priceCurrency;

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
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @since 2.0.0
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
        array $data = []
    ) {
        $this->_creditmemoConfig = $creditmemoConfig;
        $this->_orderFactory = $orderFactory;
        $this->_cmItemCollectionFactory = $cmItemCollectionFactory;
        $this->_calculatorFactory = $calculatorFactory;
        $this->_storeManager = $storeManager;
        $this->_commentFactory = $commentFactory;
        $this->_commentCollectionFactory = $commentCollectionFactory;
        $this->priceCurrency = $priceCurrency;
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
     * @since 2.0.0
     */
    protected function _construct()
    {
        $this->_init(\Magento\Sales\Model\ResourceModel\Order\Creditmemo::class);
    }

    /**
     * Retrieve Creditmemo configuration model
     *
     * @return \Magento\Sales\Model\Order\Creditmemo\Config
     * @since 2.0.0
     */
    public function getConfig()
    {
        return $this->_creditmemoConfig;
    }

    /**
     * Retrieve creditmemo store instance
     *
     * @return \Magento\Store\Model\Store
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function getEntityType()
    {
        return $this->entityType;
    }

    /**
     * Retrieve billing address
     *
     * @return Address
     * @since 2.0.0
     */
    public function getBillingAddress()
    {
        return $this->getOrder()->getBillingAddress();
    }

    /**
     * Retrieve shipping address
     *
     * @return Address
     * @since 2.0.0
     */
    public function getShippingAddress()
    {
        return $this->getOrder()->getShippingAddress();
    }

    /**
     * @return mixed
     * @since 2.0.0
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
     * @since 2.0.0
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
     * Returns credit memo item by its order id
     *
     * @param mixed $orderId
     * @return \Magento\Sales\Model\Order\Creditmemo\Item|bool
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function getInvoice()
    {
        return $this->getData('invoice');
    }

    /**
     * Sets invoice
     *
     * @param Invoice $invoice
     * @return $this
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function canCancel()
    {
        return $this->getState() == self::STATE_OPEN;
    }

    /**
     * Check invoice void action availability
     *
     * @return bool
     * @since 2.0.0
     */
    public function canVoid()
    {
        return false;
        $canVoid = false;
        if ($this->getState() == self::STATE_REFUNDED) {
            $canVoid = $this->getCanVoidFlag();
            /**
             * If we not retrieve negative answer from payment yet
             */
            if (is_null($canVoid)) {
                $canVoid = $this->getOrder()->getPayment()->canVoid();
                if ($canVoid === false) {
                    $this->setCanVoidFlag(false);
                    $this->_saveBeforeDestruct = true;
                }
            } else {
                $canVoid = (bool)$canVoid;
            }
        }
        return $canVoid;
    }

    /**
     * Retrieve Creditmemo states array
     *
     * @return array
     * @since 2.0.0
     */
    public static function getStates()
    {
        if (is_null(static::$_states)) {
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
     * @since 2.0.0
     */
    public function getStateName($stateId = null)
    {
        if (is_null($stateId)) {
            $stateId = $this->getState();
        }

        if (is_null(static::$_states)) {
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
     * @since 2.0.0
     */
    public function setShippingAmount($amount)
    {
        // base shipping amount calculated in total model
        //        $amount = $this->getStore()->round($amount);
        //        $this->setData('base_shipping_amount', $amount);
        //
        //        $amount = $this->getStore()->round(
        //            $amount*$this->getOrder()->getStoreToOrderRate()
        //        );
        return $this->setData(CreditmemoInterface::SHIPPING_AMOUNT, $amount);
    }

    /**
     * @param string $amount
     * @return $this
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function getCommentsCollection($reload = false)
    {
        $collection = $this->_commentCollectionFactory->create()->setCreditmemoFilter($this->getId())
            ->setCreatedAtOrder();
//
//            $this->setComments($comments);
//            /**
//             * When credit memo created with adding comment,
//             * comments collection must be loaded before we added this comment.
//             */
//            $this->getComments()->load();

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
     * @since 2.0.0
     */
    public function getFilteredCollectionItems($filter = null)
    {
        return $this->getResourceCollection()->getFiltered($filter);
    }

    /**
     * Returns increment id
     *
     * @return string
     * @since 2.0.0
     */
    public function getIncrementId()
    {
        return $this->getData('increment_id');
    }

    /**
     * @return bool
     * @since 2.0.0
     */
    public function isValidGrandTotal()
    {
        return !($this->getGrandTotal() <= 0 && !$this->getAllowZeroGrandTotal());
    }

    /**
     * Return creditmemo items
     *
     * @return \Magento\Sales\Api\Data\CreditmemoItemInterface[]
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function getDiscountDescription()
    {
        return $this->getData(CreditmemoInterface::DISCOUNT_DESCRIPTION);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setItems($items)
    {
        return $this->setData(CreditmemoInterface::ITEMS, $items);
    }

    /**
     * Returns adjustment
     *
     * @return float
     * @since 2.0.0
     */
    public function getAdjustment()
    {
        return $this->getData(CreditmemoInterface::ADJUSTMENT);
    }

    /**
     * Returns adjustment_negative
     *
     * @return float
     * @since 2.0.0
     */
    public function getAdjustmentNegative()
    {
        return $this->getData(CreditmemoInterface::ADJUSTMENT_NEGATIVE);
    }

    /**
     * Returns adjustment_positive
     *
     * @return float
     * @since 2.0.0
     */
    public function getAdjustmentPositive()
    {
        return $this->getData(CreditmemoInterface::ADJUSTMENT_POSITIVE);
    }

    /**
     * Returns base_adjustment
     *
     * @return float
     * @since 2.0.0
     */
    public function getBaseAdjustment()
    {
        return $this->getData(CreditmemoInterface::BASE_ADJUSTMENT);
    }

    /**
     * Returns base_adjustment_negative
     *
     * @return float
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function setBaseAdjustmentNegative($baseAdjustmentNegative)
    {
        return $this->setData(CreditmemoInterface::BASE_ADJUSTMENT_NEGATIVE, $baseAdjustmentNegative);
    }

    /**
     * Returns base_adjustment_positive
     *
     * @return float
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function setBaseAdjustmentPositive($baseAdjustmentPositive)
    {
        return $this->setData(CreditmemoInterface::BASE_ADJUSTMENT_POSITIVE, $baseAdjustmentPositive);
    }

    /**
     * Returns base_currency_code
     *
     * @return string
     * @since 2.0.0
     */
    public function getBaseCurrencyCode()
    {
        return $this->getData(CreditmemoInterface::BASE_CURRENCY_CODE);
    }

    /**
     * Returns base_discount_amount
     *
     * @return float
     * @since 2.0.0
     */
    public function getBaseDiscountAmount()
    {
        return $this->getData(CreditmemoInterface::BASE_DISCOUNT_AMOUNT);
    }

    /**
     * Returns base_grand_total
     *
     * @return float
     * @since 2.0.0
     */
    public function getBaseGrandTotal()
    {
        return $this->getData(CreditmemoInterface::BASE_GRAND_TOTAL);
    }

    /**
     * Returns base_discount_tax_compensation_amount
     *
     * @return float|null
     * @since 2.0.0
     */
    public function getBaseDiscountTaxCompensationAmount()
    {
        return $this->getData(CreditmemoInterface::BASE_DISCOUNT_TAX_COMPENSATION_AMOUNT);
    }

    /**
     * Returns base_shipping_amount
     *
     * @return float
     * @since 2.0.0
     */
    public function getBaseShippingAmount()
    {
        return $this->getData(CreditmemoInterface::BASE_SHIPPING_AMOUNT);
    }

    /**
     * Returns base_shipping_discount_tax_compensation_amnt
     *
     * @return float|null
     * @since 2.0.0
     */
    public function getBaseShippingDiscountTaxCompensationAmnt()
    {
        return $this->getData(CreditmemoInterface::BASE_SHIPPING_DISCOUNT_TAX_COMPENSATION_AMNT);
    }

    /**
     * Returns base_shipping_incl_tax
     *
     * @return float
     * @since 2.0.0
     */
    public function getBaseShippingInclTax()
    {
        return $this->getData(CreditmemoInterface::BASE_SHIPPING_INCL_TAX);
    }

    /**
     * Returns base_shipping_tax_amount
     *
     * @return float
     * @since 2.0.0
     */
    public function getBaseShippingTaxAmount()
    {
        return $this->getData(CreditmemoInterface::BASE_SHIPPING_TAX_AMOUNT);
    }

    /**
     * Returns base_subtotal
     *
     * @return float
     * @since 2.0.0
     */
    public function getBaseSubtotal()
    {
        return $this->getData(CreditmemoInterface::BASE_SUBTOTAL);
    }

    /**
     * Returns base_subtotal_incl_tax
     *
     * @return float
     * @since 2.0.0
     */
    public function getBaseSubtotalInclTax()
    {
        return $this->getData(CreditmemoInterface::BASE_SUBTOTAL_INCL_TAX);
    }

    /**
     * Returns base_tax_amount
     *
     * @return float
     * @since 2.0.0
     */
    public function getBaseTaxAmount()
    {
        return $this->getData(CreditmemoInterface::BASE_TAX_AMOUNT);
    }

    /**
     * Returns base_to_global_rate
     *
     * @return float
     * @since 2.0.0
     */
    public function getBaseToGlobalRate()
    {
        return $this->getData(CreditmemoInterface::BASE_TO_GLOBAL_RATE);
    }

    /**
     * Returns base_to_order_rate
     *
     * @return float
     * @since 2.0.0
     */
    public function getBaseToOrderRate()
    {
        return $this->getData(CreditmemoInterface::BASE_TO_ORDER_RATE);
    }

    /**
     * Returns billing_address_id
     *
     * @return int
     * @since 2.0.0
     */
    public function getBillingAddressId()
    {
        return $this->getData(CreditmemoInterface::BILLING_ADDRESS_ID);
    }

    /**
     * Returns created_at
     *
     * @return string
     * @since 2.0.0
     */
    public function getCreatedAt()
    {
        return $this->getData(CreditmemoInterface::CREATED_AT);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(CreditmemoInterface::CREATED_AT, $createdAt);
    }

    /**
     * Returns creditmemo_status
     *
     * @return int
     * @since 2.0.0
     */
    public function getCreditmemoStatus()
    {
        return $this->getData(CreditmemoInterface::CREDITMEMO_STATUS);
    }

    /**
     * Returns discount_amount
     *
     * @return float
     * @since 2.0.0
     */
    public function getDiscountAmount()
    {
        return $this->getData(CreditmemoInterface::DISCOUNT_AMOUNT);
    }

    /**
     * Returns email_sent
     *
     * @return int
     * @since 2.0.0
     */
    public function getEmailSent()
    {
        return $this->getData(CreditmemoInterface::EMAIL_SENT);
    }

    /**
     * Returns global_currency_code
     *
     * @return string
     * @since 2.0.0
     */
    public function getGlobalCurrencyCode()
    {
        return $this->getData(CreditmemoInterface::GLOBAL_CURRENCY_CODE);
    }

    /**
     * Returns grand_total
     *
     * @return float
     * @since 2.0.0
     */
    public function getGrandTotal()
    {
        return $this->getData(CreditmemoInterface::GRAND_TOTAL);
    }

    /**
     * Returns discount_tax_compensation_amount
     *
     * @return float|null
     * @since 2.0.0
     */
    public function getDiscountTaxCompensationAmount()
    {
        return $this->getData(CreditmemoInterface::DISCOUNT_TAX_COMPENSATION_AMOUNT);
    }

    /**
     * Returns invoice_id
     *
     * @return int
     * @since 2.0.0
     */
    public function getInvoiceId()
    {
        return $this->getData(CreditmemoInterface::INVOICE_ID);
    }

    /**
     * Returns order_currency_code
     *
     * @return string
     * @since 2.0.0
     */
    public function getOrderCurrencyCode()
    {
        return $this->getData(CreditmemoInterface::ORDER_CURRENCY_CODE);
    }

    /**
     * Returns order_id
     *
     * @return int
     * @since 2.0.0
     */
    public function getOrderId()
    {
        return $this->getData(CreditmemoInterface::ORDER_ID);
    }

    /**
     * Returns shipping_address_id
     *
     * @return int
     * @since 2.0.0
     */
    public function getShippingAddressId()
    {
        return $this->getData(CreditmemoInterface::SHIPPING_ADDRESS_ID);
    }

    /**
     * Returns shipping_amount
     *
     * @return float
     * @since 2.0.0
     */
    public function getShippingAmount()
    {
        return $this->getData(CreditmemoInterface::SHIPPING_AMOUNT);
    }

    /**
     * Returns shipping_discount_tax_compensation_amount
     *
     * @return float|null
     * @since 2.0.0
     */
    public function getShippingDiscountTaxCompensationAmount()
    {
        return $this->getData(CreditmemoInterface::SHIPPING_DISCOUNT_TAX_COMPENSATION_AMOUNT);
    }

    /**
     * Returns shipping_incl_tax
     *
     * @return float
     * @since 2.0.0
     */
    public function getShippingInclTax()
    {
        return $this->getData(CreditmemoInterface::SHIPPING_INCL_TAX);
    }

    /**
     * Returns shipping_tax_amount
     *
     * @return float
     * @since 2.0.0
     */
    public function getShippingTaxAmount()
    {
        return $this->getData(CreditmemoInterface::SHIPPING_TAX_AMOUNT);
    }

    /**
     * Returns state
     *
     * @return int
     * @since 2.0.0
     */
    public function getState()
    {
        return $this->getData(CreditmemoInterface::STATE);
    }

    /**
     * Returns store_currency_code
     *
     * @return string
     * @since 2.0.0
     */
    public function getStoreCurrencyCode()
    {
        return $this->getData(CreditmemoInterface::STORE_CURRENCY_CODE);
    }

    /**
     * Returns store_id
     *
     * @return int
     * @since 2.0.0
     */
    public function getStoreId()
    {
        return $this->getData(CreditmemoInterface::STORE_ID);
    }

    /**
     * Returns store_to_base_rate
     *
     * @return float
     * @since 2.0.0
     */
    public function getStoreToBaseRate()
    {
        return $this->getData(CreditmemoInterface::STORE_TO_BASE_RATE);
    }

    /**
     * Returns store_to_order_rate
     *
     * @return float
     * @since 2.0.0
     */
    public function getStoreToOrderRate()
    {
        return $this->getData(CreditmemoInterface::STORE_TO_ORDER_RATE);
    }

    /**
     * Returns subtotal
     *
     * @return float
     * @since 2.0.0
     */
    public function getSubtotal()
    {
        return $this->getData(CreditmemoInterface::SUBTOTAL);
    }

    /**
     * Returns subtotal_incl_tax
     *
     * @return float
     * @since 2.0.0
     */
    public function getSubtotalInclTax()
    {
        return $this->getData(CreditmemoInterface::SUBTOTAL_INCL_TAX);
    }

    /**
     * Returns tax_amount
     *
     * @return float
     * @since 2.0.0
     */
    public function getTaxAmount()
    {
        return $this->getData(CreditmemoInterface::TAX_AMOUNT);
    }

    /**
     * Returns transaction_id
     *
     * @return string
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function setTransactionId($transactionId)
    {
        return $this->setData(CreditmemoInterface::TRANSACTION_ID, $transactionId);
    }

    /**
     * Returns updated_at
     *
     * @return string
     * @since 2.0.0
     */
    public function getUpdatedAt()
    {
        return $this->getData(CreditmemoInterface::UPDATED_AT);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setComments($comments)
    {
        return $this->setData(CreditmemoInterface::COMMENTS, $comments);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setStoreId($id)
    {
        return $this->setData(CreditmemoInterface::STORE_ID, $id);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setBaseShippingTaxAmount($amount)
    {
        return $this->setData(CreditmemoInterface::BASE_SHIPPING_TAX_AMOUNT, $amount);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setStoreToOrderRate($rate)
    {
        return $this->setData(CreditmemoInterface::STORE_TO_ORDER_RATE, $rate);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setBaseDiscountAmount($amount)
    {
        return $this->setData(CreditmemoInterface::BASE_DISCOUNT_AMOUNT, $amount);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setBaseToOrderRate($rate)
    {
        return $this->setData(CreditmemoInterface::BASE_TO_ORDER_RATE, $rate);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setGrandTotal($amount)
    {
        return $this->setData(CreditmemoInterface::GRAND_TOTAL, $amount);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setBaseSubtotalInclTax($amount)
    {
        return $this->setData(CreditmemoInterface::BASE_SUBTOTAL_INCL_TAX, $amount);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setSubtotalInclTax($amount)
    {
        return $this->setData(CreditmemoInterface::SUBTOTAL_INCL_TAX, $amount);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setBaseShippingAmount($amount)
    {
        return $this->setData(CreditmemoInterface::BASE_SHIPPING_AMOUNT, $amount);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setStoreToBaseRate($rate)
    {
        return $this->setData(CreditmemoInterface::STORE_TO_BASE_RATE, $rate);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setBaseToGlobalRate($rate)
    {
        return $this->setData(CreditmemoInterface::BASE_TO_GLOBAL_RATE, $rate);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setBaseAdjustment($baseAdjustment)
    {
        return $this->setData(CreditmemoInterface::BASE_ADJUSTMENT, $baseAdjustment);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setBaseSubtotal($amount)
    {
        return $this->setData(CreditmemoInterface::BASE_SUBTOTAL, $amount);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setDiscountAmount($amount)
    {
        return $this->setData(CreditmemoInterface::DISCOUNT_AMOUNT, $amount);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setSubtotal($amount)
    {
        return $this->setData(CreditmemoInterface::SUBTOTAL, $amount);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setAdjustment($adjustment)
    {
        return $this->setData(CreditmemoInterface::ADJUSTMENT, $adjustment);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setBaseGrandTotal($amount)
    {
        return $this->setData(CreditmemoInterface::BASE_GRAND_TOTAL, $amount);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setBaseTaxAmount($amount)
    {
        return $this->setData(CreditmemoInterface::BASE_TAX_AMOUNT, $amount);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setShippingTaxAmount($amount)
    {
        return $this->setData(CreditmemoInterface::SHIPPING_TAX_AMOUNT, $amount);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setTaxAmount($amount)
    {
        return $this->setData(CreditmemoInterface::TAX_AMOUNT, $amount);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setOrderId($id)
    {
        return $this->setData(CreditmemoInterface::ORDER_ID, $id);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setEmailSent($emailSent)
    {
        return $this->setData(CreditmemoInterface::EMAIL_SENT, $emailSent);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setCreditmemoStatus($creditmemoStatus)
    {
        return $this->setData(CreditmemoInterface::CREDITMEMO_STATUS, $creditmemoStatus);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setState($state)
    {
        return $this->setData(CreditmemoInterface::STATE, $state);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setShippingAddressId($id)
    {
        return $this->setData(CreditmemoInterface::SHIPPING_ADDRESS_ID, $id);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setBillingAddressId($id)
    {
        return $this->setData(CreditmemoInterface::BILLING_ADDRESS_ID, $id);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setInvoiceId($id)
    {
        return $this->setData(CreditmemoInterface::INVOICE_ID, $id);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setStoreCurrencyCode($code)
    {
        return $this->setData(CreditmemoInterface::STORE_CURRENCY_CODE, $code);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setOrderCurrencyCode($code)
    {
        return $this->setData(CreditmemoInterface::ORDER_CURRENCY_CODE, $code);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setBaseCurrencyCode($code)
    {
        return $this->setData(CreditmemoInterface::BASE_CURRENCY_CODE, $code);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setGlobalCurrencyCode($code)
    {
        return $this->setData(CreditmemoInterface::GLOBAL_CURRENCY_CODE, $code);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setIncrementId($id)
    {
        return $this->setData(CreditmemoInterface::INCREMENT_ID, $id);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setUpdatedAt($timestamp)
    {
        return $this->setData(CreditmemoInterface::UPDATED_AT, $timestamp);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setDiscountTaxCompensationAmount($amount)
    {
        return $this->setData(CreditmemoInterface::DISCOUNT_TAX_COMPENSATION_AMOUNT, $amount);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setBaseDiscountTaxCompensationAmount($amount)
    {
        return $this->setData(CreditmemoInterface::BASE_DISCOUNT_TAX_COMPENSATION_AMOUNT, $amount);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setShippingDiscountTaxCompensationAmount($amount)
    {
        return $this->setData(CreditmemoInterface::SHIPPING_DISCOUNT_TAX_COMPENSATION_AMOUNT, $amount);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setBaseShippingDiscountTaxCompensationAmnt($amnt)
    {
        return $this->setData(CreditmemoInterface::BASE_SHIPPING_DISCOUNT_TAX_COMPENSATION_AMNT, $amnt);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setShippingInclTax($amount)
    {
        return $this->setData(CreditmemoInterface::SHIPPING_INCL_TAX, $amount);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setBaseShippingInclTax($amount)
    {
        return $this->setData(CreditmemoInterface::BASE_SHIPPING_INCL_TAX, $amount);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setDiscountDescription($description)
    {
        return $this->setData(CreditmemoInterface::DISCOUNT_DESCRIPTION, $description);
    }

    /**
     * {@inheritdoc}
     *
     * @return \Magento\Sales\Api\Data\CreditmemoExtensionInterface|null
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function setExtensionAttributes(\Magento\Sales\Api\Data\CreditmemoExtensionInterface $extensionAttributes)
    {
        return $this->_setExtensionAttributes($extensionAttributes);
    }

    //@codeCoverageIgnoreEnd
}
