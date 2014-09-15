<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Sales\Model\Order;

use Magento\Sales\Model\EntityInterface;

/**
 * @method \Magento\Sales\Model\Resource\Order\Invoice _getResource()
 * @method \Magento\Sales\Model\Resource\Order\Invoice getResource()
 * @method int getStoreId()
 * @method \Magento\Sales\Model\Order\Invoice setStoreId(int $value)
 * @method float getBaseGrandTotal()
 * @method \Magento\Sales\Model\Order\Invoice setBaseGrandTotal(float $value)
 * @method float getShippingTaxAmount()
 * @method \Magento\Sales\Model\Order\Invoice setShippingTaxAmount(float $value)
 * @method float getTaxAmount()
 * @method \Magento\Sales\Model\Order\Invoice setTaxAmount(float $value)
 * @method float getBaseTaxAmount()
 * @method \Magento\Sales\Model\Order\Invoice setBaseTaxAmount(float $value)
 * @method float getStoreToOrderRate()
 * @method \Magento\Sales\Model\Order\Invoice setStoreToOrderRate(float $value)
 * @method float getBaseShippingTaxAmount()
 * @method \Magento\Sales\Model\Order\Invoice setBaseShippingTaxAmount(float $value)
 * @method float getBaseDiscountAmount()
 * @method \Magento\Sales\Model\Order\Invoice setBaseDiscountAmount(float $value)
 * @method float getBaseToOrderRate()
 * @method \Magento\Sales\Model\Order\Invoice setBaseToOrderRate(float $value)
 * @method float getGrandTotal()
 * @method \Magento\Sales\Model\Order\Invoice setGrandTotal(float $value)
 * @method float getShippingAmount()
 * @method \Magento\Sales\Model\Order\Invoice setShippingAmount(float $value)
 * @method float getSubtotalInclTax()
 * @method \Magento\Sales\Model\Order\Invoice setSubtotalInclTax(float $value)
 * @method float getBaseSubtotalInclTax()
 * @method \Magento\Sales\Model\Order\Invoice setBaseSubtotalInclTax(float $value)
 * @method float getStoreToBaseRate()
 * @method \Magento\Sales\Model\Order\Invoice setStoreToBaseRate(float $value)
 * @method float getBaseShippingAmount()
 * @method \Magento\Sales\Model\Order\Invoice setBaseShippingAmount(float $value)
 * @method float getTotalQty()
 * @method \Magento\Sales\Model\Order\Invoice setTotalQty(float $value)
 * @method float getBaseToGlobalRate()
 * @method \Magento\Sales\Model\Order\Invoice setBaseToGlobalRate(float $value)
 * @method float getSubtotal()
 * @method \Magento\Sales\Model\Order\Invoice setSubtotal(float $value)
 * @method float getBaseSubtotal()
 * @method \Magento\Sales\Model\Order\Invoice setBaseSubtotal(float $value)
 * @method float getDiscountAmount()
 * @method \Magento\Sales\Model\Order\Invoice setDiscountAmount(float $value)
 * @method int getBillingAddressId()
 * @method \Magento\Sales\Model\Order\Invoice setBillingAddressId(int $value)
 * @method int getIsUsedForRefund()
 * @method \Magento\Sales\Model\Order\Invoice setIsUsedForRefund(int $value)
 * @method int getOrderId()
 * @method \Magento\Sales\Model\Order\Invoice setOrderId(int $value)
 * @method int getEmailSent()
 * @method \Magento\Sales\Model\Order\Invoice setEmailSent(int $value)
 * @method int getCanVoidFlag()
 * @method \Magento\Sales\Model\Order\Invoice setCanVoidFlag(int $value)
 * @method int getState()
 * @method \Magento\Sales\Model\Order\Invoice setState(int $value)
 * @method int getShippingAddressId()
 * @method \Magento\Sales\Model\Order\Invoice setShippingAddressId(int $value)
 * @method string getStoreCurrencyCode()
 * @method \Magento\Sales\Model\Order\Invoice setStoreCurrencyCode(string $value)
 * @method string getTransactionId()
 * @method \Magento\Sales\Model\Order\Invoice setTransactionId(string $value)
 * @method string getOrderCurrencyCode()
 * @method \Magento\Sales\Model\Order\Invoice setOrderCurrencyCode(string $value)
 * @method string getBaseCurrencyCode()
 * @method \Magento\Sales\Model\Order\Invoice setBaseCurrencyCode(string $value)
 * @method string getGlobalCurrencyCode()
 * @method \Magento\Sales\Model\Order\Invoice setGlobalCurrencyCode(string $value)
 * @method \Magento\Sales\Model\Order\Invoice setIncrementId(string $value)
 * @method string getCreatedAt()
 * @method \Magento\Sales\Model\Order\Invoice setCreatedAt(string $value)
 * @method string getUpdatedAt()
 * @method \Magento\Sales\Model\Order\Invoice setUpdatedAt(string $value)
 * @method float getHiddenTaxAmount()
 * @method \Magento\Sales\Model\Order\Invoice setHiddenTaxAmount(float $value)
 * @method float getBaseHiddenTaxAmount()
 * @method \Magento\Sales\Model\Order\Invoice setBaseHiddenTaxAmount(float $value)
 * @method float getShippingHiddenTaxAmount()
 * @method \Magento\Sales\Model\Order\Invoice setShippingHiddenTaxAmount(float $value)
 * @method float getBaseShippingHiddenTaxAmnt()
 * @method \Magento\Sales\Model\Order\Invoice setBaseShippingHiddenTaxAmnt(float $value)
 * @method float getShippingInclTax()
 * @method \Magento\Sales\Model\Order\Invoice setShippingInclTax(float $value)
 * @method float getBaseShippingInclTax()
 * @method \Magento\Sales\Model\Order\Invoice setBaseShippingInclTax(float $value)
 */
class Invoice extends \Magento\Sales\Model\AbstractModel implements EntityInterface
{
    /**
     * Invoice states
     */
    const STATE_OPEN = 1;

    const STATE_PAID = 2;

    const STATE_CANCELED = 3;

    const CAPTURE_ONLINE = 'online';

    const CAPTURE_OFFLINE = 'offline';

    const NOT_CAPTURE = 'not_capture';

    const REPORT_DATE_TYPE_ORDER_CREATED = 'order_created';

    const REPORT_DATE_TYPE_INVOICE_CREATED = 'invoice_created';

    /**
     * Identifier for history item
     *
     * @var string
     */
    protected $entityType = 'invoice';

    /**
     * @var array
     */
    protected static $_states;

    /**
     * @var \Magento\Sales\Model\Resource\Order\Invoice\Item\Collection
     */
    protected $_items;

    /**
     * @var \Magento\Sales\Model\Resource\Order\Invoice\Comment\Collection
     */
    protected $_comments;

    /**
     * @var \Magento\Sales\Model\Order
     */
    protected $_order;

    /**
     * Calculator instances for delta rounding of prices
     *
     * @var array
     */
    protected $_rounders = array();

    /**
     * @var bool
     */
    protected $_saveBeforeDestruct = false;

    /**
     * @var string
     */
    protected $_eventPrefix = 'sales_order_invoice';

    /**
     * @var string
     */
    protected $_eventObject = 'invoice';

    /**
     * Whether the pay() was called
     *
     * @var bool
     */
    protected $_wasPayCalled = false;

    /**
     * @var \Magento\Sales\Model\Order\Invoice\Config
     */
    protected $_invoiceConfig;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $_orderFactory;

    /**
     * @var \Magento\Framework\Math\CalculatorFactory
     */
    protected $_calculatorFactory;

    /**
     * @var \Magento\Sales\Model\Resource\Order\Invoice\Item\CollectionFactory
     */
    protected $_invoiceItemCollectionFactory;

    /**
     * @var \Magento\Sales\Model\Order\Invoice\CommentFactory
     */
    protected $_invoiceCommentFactory;

    /**
     * @var \Magento\Sales\Model\Resource\Order\Invoice\Comment\CollectionFactory
     */
    protected $_commentCollectionFactory;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param Invoice\Config $invoiceConfig
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Framework\Math\CalculatorFactory $calculatorFactory
     * @param \Magento\Sales\Model\Resource\Order\Invoice\Item\CollectionFactory $invoiceItemCollectionFactory
     * @param Invoice\CommentFactory $invoiceCommentFactory
     * @param \Magento\Sales\Model\Resource\Order\Invoice\Comment\CollectionFactory $commentCollectionFactory
     * @param \Magento\Framework\Model\Resource\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        \Magento\Sales\Model\Order\Invoice\Config $invoiceConfig,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Framework\Math\CalculatorFactory $calculatorFactory,
        \Magento\Sales\Model\Resource\Order\Invoice\Item\CollectionFactory $invoiceItemCollectionFactory,
        \Magento\Sales\Model\Order\Invoice\CommentFactory $invoiceCommentFactory,
        \Magento\Sales\Model\Resource\Order\Invoice\Comment\CollectionFactory $commentCollectionFactory,
        \Magento\Framework\Model\Resource\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\Db $resourceCollection = null,
        array $data = array()
    ) {
        $this->_invoiceConfig = $invoiceConfig;
        $this->_orderFactory = $orderFactory;
        $this->_calculatorFactory = $calculatorFactory;
        $this->_invoiceItemCollectionFactory = $invoiceItemCollectionFactory;
        $this->_invoiceCommentFactory = $invoiceCommentFactory;
        $this->_commentCollectionFactory = $commentCollectionFactory;
        parent::__construct($context, $registry, $localeDate, $dateTime, $resource, $resourceCollection, $data);
    }

    /**
     * Initialize invoice resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\Sales\Model\Resource\Order\Invoice');
    }

    /**
     * Load invoice by increment id
     *
     * @param string $incrementId
     * @return $this
     */
    public function loadByIncrementId($incrementId)
    {
        $ids = $this->getCollection()->addAttributeToFilter('increment_id', $incrementId)->getAllIds();

        if (!empty($ids)) {
            reset($ids);
            $this->load(current($ids));
        }
        return $this;
    }

    /**
     * Retrieve invoice configuration model
     *
     * @return \Magento\Sales\Model\Order\Invoice\Config
     */
    public function getConfig()
    {
        return $this->_invoiceConfig;
    }

    /**
     * Retrieve store model instance
     *
     * @return \Magento\Store\Model\Store
     */
    public function getStore()
    {
        return $this->getOrder()->getStore();
    }

    /**
     * Declare order for invoice
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
     * Retrieve the order the invoice for created for
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
     * Return order history item identifier
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
     * Check invoice cancel state
     *
     * @return bool
     */
    public function isCanceled()
    {
        return $this->getState() == self::STATE_CANCELED;
    }

    /**
     * Check invoice capture action availability
     *
     * @return bool
     */
    public function canCapture()
    {
        return $this->getState() != self::STATE_CANCELED &&
            $this->getState() != self::STATE_PAID &&
            $this->getOrder()->getPayment()->canCapture();
    }

    /**
     * Check invoice void action availability
     *
     * @return bool
     */
    public function canVoid()
    {
        if ($this->getState() == self::STATE_PAID) {
            if (is_null($this->getCanVoidFlag())) {
                return (bool)$this->getOrder()->getPayment()->canVoid($this);
            }
        }
        return (bool)$this->getCanVoidFlag();
    }

    /**
     * Check invoice cancel action availability
     *
     * @return bool
     */
    public function canCancel()
    {
        return $this->getState() == self::STATE_OPEN;
    }

    /**
     * Check invoice refund action availability
     *
     * @return bool
     */
    public function canRefund()
    {
        if ($this->getState() != self::STATE_PAID) {
            return false;
        }
        if (abs($this->getBaseGrandTotal() - $this->getBaseTotalRefunded()) < .0001) {
            return false;
        }
        return true;
    }

    /**
     * Capture invoice
     *
     * @return $this
     */
    public function capture()
    {
        $this->getOrder()->getPayment()->capture($this);
        if ($this->getIsPaid()) {
            $this->pay();
        }
        return $this;
    }

    /**
     * Pay invoice
     *
     * @return $this
     */
    public function pay()
    {
        if ($this->_wasPayCalled) {
            return $this;
        }
        $this->_wasPayCalled = true;

        $invoiceState = self::STATE_PAID;
        if ($this->getOrder()->getPayment()->hasForcedState()) {
            $invoiceState = $this->getOrder()->getPayment()->getForcedState();
        }

        $this->setState($invoiceState);

        $this->getOrder()->getPayment()->pay($this);
        $this->getOrder()->setTotalPaid($this->getOrder()->getTotalPaid() + $this->getGrandTotal());
        $this->getOrder()->setBaseTotalPaid($this->getOrder()->getBaseTotalPaid() + $this->getBaseGrandTotal());
        $this->_eventManager->dispatch('sales_order_invoice_pay', array($this->_eventObject => $this));
        return $this;
    }

    /**
     * Whether pay() method was called (whether order and payment totals were updated)
     *
     * @return bool
     */
    public function wasPayCalled()
    {
        return $this->_wasPayCalled;
    }

    /**
     * Void invoice
     *
     * @return $this
     */
    public function void()
    {
        $this->getOrder()->getPayment()->void($this);
        $this->cancel();
        return $this;
    }

    /**
     * Cancel invoice action
     *
     * @return $this
     */
    public function cancel()
    {
        $order = $this->getOrder();
        $order->getPayment()->cancelInvoice($this);
        foreach ($this->getAllItems() as $item) {
            $item->cancel();
        }

        /**
         * Unregister order totals only for invoices in state PAID
         */
        $order->setTotalInvoiced($order->getTotalInvoiced() - $this->getGrandTotal());
        $order->setBaseTotalInvoiced($order->getBaseTotalInvoiced() - $this->getBaseGrandTotal());

        $order->setSubtotalInvoiced($order->getSubtotalInvoiced() - $this->getSubtotal());
        $order->setBaseSubtotalInvoiced($order->getBaseSubtotalInvoiced() - $this->getBaseSubtotal());

        $order->setTaxInvoiced($order->getTaxInvoiced() - $this->getTaxAmount());
        $order->setBaseTaxInvoiced($order->getBaseTaxInvoiced() - $this->getBaseTaxAmount());

        $order->setHiddenTaxInvoiced($order->getHiddenTaxInvoiced() - $this->getHiddenTaxAmount());
        $order->setBaseHiddenTaxInvoiced($order->getBaseHiddenTaxInvoiced() - $this->getBaseHiddenTaxAmount());

        $order->setShippingTaxInvoiced($order->getShippingTaxInvoiced() - $this->getShippingTaxAmount());
        $order->setBaseShippingTaxInvoiced($order->getBaseShippingTaxInvoiced() - $this->getBaseShippingTaxAmount());

        $order->setShippingInvoiced($order->getShippingInvoiced() - $this->getShippingAmount());
        $order->setBaseShippingInvoiced($order->getBaseShippingInvoiced() - $this->getBaseShippingAmount());

        $order->setDiscountInvoiced($order->getDiscountInvoiced() - $this->getDiscountAmount());
        $order->setBaseDiscountInvoiced($order->getBaseDiscountInvoiced() - $this->getBaseDiscountAmount());
        $order->setBaseTotalInvoicedCost($order->getBaseTotalInvoicedCost() - $this->getBaseCost());


        if ($this->getState() == self::STATE_PAID) {
            $this->getOrder()->setTotalPaid($this->getOrder()->getTotalPaid() - $this->getGrandTotal());
            $this->getOrder()->setBaseTotalPaid($this->getOrder()->getBaseTotalPaid() - $this->getBaseGrandTotal());
        }
        $this->setState(self::STATE_CANCELED);
        $this->getOrder()->setState(\Magento\Sales\Model\Order::STATE_PROCESSING, true);
        $this->_eventManager->dispatch('sales_order_invoice_cancel', array($this->_eventObject => $this));
        return $this;
    }

    /**
     * Invoice totals collecting
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
            if (!isset($this->_rounders[$type])) {
                $this->_rounders[$type] = $this->_calculatorFactory->create(array('scope' => $this->getStore()));
            }
            $price = $this->_rounders[$type]->deltaRound($price, $negative);
        }
        return $price;
    }

    /**
     * Get invoice items collection
     *
     * @return \Magento\Sales\Model\Resource\Order\Invoice\Item\Collection
     */
    public function getItemsCollection()
    {
        if (empty($this->_items)) {
            $this->_items = $this->_invoiceItemCollectionFactory->create()->setInvoiceFilter($this->getId());

            if ($this->getId()) {
                foreach ($this->_items as $item) {
                    $item->setInvoice($this);
                }
            }
        }
        return $this->_items;
    }

    /**
     * @return array
     */
    public function getAllItems()
    {
        $items = array();
        foreach ($this->getItemsCollection() as $item) {
            if (!$item->isDeleted()) {
                $items[] = $item;
            }
        }
        return $items;
    }

    /**
     * @param int|string $itemId
     * @return bool|\Magento\Sales\Model\Order\Invoice\Item
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
     * @param \Magento\Sales\Model\Order\Invoice\Item $item
     * @return $this
     */
    public function addItem(\Magento\Sales\Model\Order\Invoice\Item $item)
    {
        $item->setInvoice($this)->setParentId($this->getId())->setStoreId($this->getStoreId());

        if (!$item->getId()) {
            $this->getItemsCollection()->addItem($item);
        }
        return $this;
    }

    /**
     * Retrieve invoice states array
     *
     * @return array
     */
    public static function getStates()
    {
        if (null === self::$_states) {
            self::$_states = array(
                self::STATE_OPEN => __('Pending'),
                self::STATE_PAID => __('Paid'),
                self::STATE_CANCELED => __('Canceled')
            );
        }
        return self::$_states;
    }

    /**
     * Retrieve invoice state name by state identifier
     *
     * @param   int|null $stateId
     * @return  string
     */
    public function getStateName($stateId = null)
    {
        if (is_null($stateId)) {
            $stateId = $this->getState();
        }

        if (null === self::$_states) {
            self::getStates();
        }
        if (isset(self::$_states[$stateId])) {
            return self::$_states[$stateId];
        }
        return __('Unknown State');
    }

    /**
     * Register invoice
     *
     * Apply to order, order items etc.
     *
     * @return $this
     * @throws \Magento\Framework\Model\Exception
     */
    public function register()
    {
        if ($this->getId()) {
            throw new \Magento\Framework\Model\Exception(__('We cannot register an existing invoice'));
        }

        foreach ($this->getAllItems() as $item) {
            if ($item->getQty() > 0) {
                $item->register();
            } else {
                $item->isDeleted(true);
            }
        }

        $order = $this->getOrder();
        $captureCase = $this->getRequestedCaptureCase();
        if ($this->canCapture()) {
            if ($captureCase) {
                if ($captureCase == self::CAPTURE_ONLINE) {
                    $this->capture();
                } elseif ($captureCase == self::CAPTURE_OFFLINE) {
                    $this->setCanVoidFlag(false);
                    $this->pay();
                }
            }
        } elseif (!$order->getPayment()->getMethodInstance()->isGateway() || $captureCase == self::CAPTURE_OFFLINE) {
            if (!$order->getPayment()->getIsTransactionPending()) {
                $this->setCanVoidFlag(false);
                $this->pay();
            }
        }

        $order->setTotalInvoiced($order->getTotalInvoiced() + $this->getGrandTotal());
        $order->setBaseTotalInvoiced($order->getBaseTotalInvoiced() + $this->getBaseGrandTotal());

        $order->setSubtotalInvoiced($order->getSubtotalInvoiced() + $this->getSubtotal());
        $order->setBaseSubtotalInvoiced($order->getBaseSubtotalInvoiced() + $this->getBaseSubtotal());

        $order->setTaxInvoiced($order->getTaxInvoiced() + $this->getTaxAmount());
        $order->setBaseTaxInvoiced($order->getBaseTaxInvoiced() + $this->getBaseTaxAmount());

        $order->setHiddenTaxInvoiced($order->getHiddenTaxInvoiced() + $this->getHiddenTaxAmount());
        $order->setBaseHiddenTaxInvoiced($order->getBaseHiddenTaxInvoiced() + $this->getBaseHiddenTaxAmount());

        $order->setShippingTaxInvoiced($order->getShippingTaxInvoiced() + $this->getShippingTaxAmount());
        $order->setBaseShippingTaxInvoiced($order->getBaseShippingTaxInvoiced() + $this->getBaseShippingTaxAmount());


        $order->setShippingInvoiced($order->getShippingInvoiced() + $this->getShippingAmount());
        $order->setBaseShippingInvoiced($order->getBaseShippingInvoiced() + $this->getBaseShippingAmount());

        $order->setDiscountInvoiced($order->getDiscountInvoiced() + $this->getDiscountAmount());
        $order->setBaseDiscountInvoiced($order->getBaseDiscountInvoiced() + $this->getBaseDiscountAmount());
        $order->setBaseTotalInvoicedCost($order->getBaseTotalInvoicedCost() + $this->getBaseCost());

        $state = $this->getState();
        if (null === $state) {
            $this->setState(self::STATE_OPEN);
        }

        $this->_eventManager->dispatch(
            'sales_order_invoice_register',
            array($this->_eventObject => $this, 'order' => $order)
        );
        return $this;
    }

    /**
     * Checking if the invoice is last
     *
     * @return bool
     */
    public function isLast()
    {
        foreach ($this->getAllItems() as $item) {
            if (!$item->isLast()) {
                return false;
            }
        }
        return true;
    }

    /**
     * Adds comment to invoice with additional possibility to send it to customer via email
     * and show it in customer account
     *
     * @param string $comment
     * @param bool $notify
     * @param bool $visibleOnFront
     * @return $this
     */
    public function addComment($comment, $notify = false, $visibleOnFront = false)
    {
        if (!$comment instanceof \Magento\Sales\Model\Order\Invoice\Comment) {
            $comment = $this->_invoiceCommentFactory->create()->setComment(
                $comment
            )->setIsCustomerNotified(
                $notify
            )->setIsVisibleOnFront(
                $visibleOnFront
            );
        }
        $comment->setInvoice($this)->setStoreId($this->getStoreId())->setParentId($this->getId());
        if (!$comment->getId()) {
            $this->getCommentsCollection()->addItem($comment);
        }
        $this->_hasDataChanges = true;
        return $this;
    }

    /**
     * @param bool $reload
     * @return \Magento\Sales\Model\Resource\Order\Invoice\Comment\Collection
     */
    public function getCommentsCollection($reload = false)
    {
        if (is_null($this->_comments) || $reload) {
            $this->_comments = $this->_commentCollectionFactory->create()->setInvoiceFilter(
                $this->getId()
            )->setCreatedAtOrder();
            /**
             * When invoice created with adding comment, comments collection
             * must be loaded before we added this comment.
             */
            $this->_comments->load();

            if ($this->getId()) {
                foreach ($this->_comments as $comment) {
                    $comment->setInvoice($this);
                }
            }
        }
        return $this->_comments;
    }

    /**
     * Reset invoice object
     *
     * @return $this
     */
    public function reset()
    {
        $this->unsetData();
        $this->_origData = null;
        $this->_items = null;
        $this->_comments = null;
        $this->_order = null;
        $this->_saveBeforeDestruct = false;
        $this->_wasPayCalled = false;
        return $this;
    }

    /**
     * Before object save manipulations
     *
     * @return $this
     */
    protected function _beforeSave()
    {
        parent::_beforeSave();

        if (!$this->getOrderId() && $this->getOrder()) {
            $this->setOrderId($this->getOrder()->getId());
            $this->setBillingAddressId($this->getOrder()->getBillingAddress()->getId());
        }

        return $this;
    }

    /**
     * After object save manipulation
     *
     * @return $this
     */
    protected function _afterSave()
    {

        if (null !== $this->_items) {
            /**
             * Save invoice items
             */
            foreach ($this->_items as $item) {
                $item->setOrderItem($item->getOrderItem());
                $item->save();
            }
        }

        if (null !== $this->_comments) {
            foreach ($this->_comments as $comment) {
                $comment->save();
            }
        }

        return parent::_afterSave();
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
}
