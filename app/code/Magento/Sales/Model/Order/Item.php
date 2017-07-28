<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order;

use Magento\Framework\Api\AttributeValueFactory;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Model\AbstractModel;

/**
 * Order Item Model
 *
 * @api
 * @method \Magento\Sales\Model\ResourceModel\Order\Item _getResource()
 * @method \Magento\Sales\Model\ResourceModel\Order\Item getResource()
 * @method int getGiftMessageId()
 * @method \Magento\Sales\Model\Order\Item setGiftMessageId(int $value)
 * @method int getGiftMessageAvailable()
 * @method \Magento\Sales\Model\Order\Item setGiftMessageAvailable(int $value)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 2.0.0
 */
class Item extends AbstractModel implements OrderItemInterface
{
    const STATUS_PENDING = 1;

    // No items shipped, invoiced, canceled, refunded nor backordered
    const STATUS_SHIPPED = 2;

    // When qty ordered - [qty canceled + qty returned] = qty shipped
    const STATUS_INVOICED = 9;

    // When qty ordered - [qty canceled + qty returned] = qty invoiced
    const STATUS_BACKORDERED = 3;

    // When qty ordered - [qty canceled + qty returned] = qty backordered
    const STATUS_CANCELED = 5;

    // When qty ordered = qty canceled
    const STATUS_PARTIAL = 6;

    // If [qty shipped or(max of two) qty invoiced + qty canceled + qty returned]
    // < qty ordered
    const STATUS_MIXED = 7;

    // All other combinations
    const STATUS_REFUNDED = 8;

    // When qty ordered = qty refunded
    const STATUS_RETURNED = 4;

    // When qty ordered = qty returned // not used at the moment

    /**
     * @var string
     * @since 2.0.0
     */
    protected $_eventPrefix = 'sales_order_item';

    /**
     * @var string
     * @since 2.0.0
     */
    protected $_eventObject = 'item';

    /**
     * @var array
     * @since 2.0.0
     */
    protected static $_statuses = null;

    /**
     * Order instance
     *
     * @var \Magento\Sales\Model\Order
     * @since 2.0.0
     */
    protected $_order = null;

    /**
     * @var array
     * @since 2.0.0
     */
    protected $_children = [];

    /**
     * @var \Magento\Sales\Model\OrderFactory
     * @since 2.0.0
     */
    protected $_orderFactory;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     * @since 2.0.0
     */
    protected $productRepository;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     * @since 2.0.0
     */
    protected $_storeManager;

    /**
     * Serializer interface instance.
     *
     * @var \Magento\Framework\Serialize\Serializer\Json
     * @since 2.2.0
     */
    private $serializer;

    /**
     * Initialize dependencies.
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     * @param \Magento\Framework\Serialize\Serializer\Json|null $serializer
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = [],
        \Magento\Framework\Serialize\Serializer\Json $serializer = null
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $resource,
            $resourceCollection,
            $data
        );
        $this->serializer = $serializer ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Framework\Serialize\Serializer\Json::class);
        $this->_orderFactory = $orderFactory;
        $this->_storeManager = $storeManager;
        $this->productRepository = $productRepository;
    }

    /**
     * Init resource model
     *
     * @return void
     * @since 2.0.0
     */
    protected function _construct()
    {
        $this->_init(\Magento\Sales\Model\ResourceModel\Order\Item::class);
    }

    /**
     * Set parent item
     *
     * @param Item $item
     * @return $this
     * @since 2.0.0
     */
    public function setParentItem($item)
    {
        if ($item) {
            $this->setData(OrderItemInterface::PARENT_ITEM, $item);
            $item->setHasChildren(true);
            $item->addChildItem($this);
        }
        return $this;
    }

    /**
     * Get parent item
     *
     * @return OrderItemInterface|null
     * @since 2.0.0
     */
    public function getParentItem()
    {
        return $this->getData(OrderItemInterface::PARENT_ITEM);
    }

    /**
     * Check item invoice availability
     *
     * @return bool
     * @since 2.0.0
     */
    public function canInvoice()
    {
        return $this->getQtyToInvoice() > 0;
    }

    /**
     * Check item ship availability
     *
     * @return bool
     * @since 2.0.0
     */
    public function canShip()
    {
        return $this->getQtyToShip() > 0;
    }

    /**
     * Check item refund availability
     *
     * @return bool
     * @since 2.0.0
     */
    public function canRefund()
    {
        return $this->getQtyToRefund() > 0;
    }

    /**
     * Retrieve item qty available for ship
     *
     * @return float|integer
     * @since 2.0.0
     */
    public function getQtyToShip()
    {
        if ($this->isDummy(true)) {
            return 0;
        }

        return $this->getSimpleQtyToShip();
    }

    /**
     * Retrieve item qty available for ship
     *
     * @return float|integer
     * @since 2.0.0
     */
    public function getSimpleQtyToShip()
    {
        $qty = $this->getQtyOrdered() - $this->getQtyShipped() - $this->getQtyRefunded() - $this->getQtyCanceled();
        return max($qty, 0);
    }

    /**
     * Retrieve item qty available for invoice
     *
     * @return float|integer
     * @since 2.0.0
     */
    public function getQtyToInvoice()
    {
        if ($this->isDummy()) {
            return 0;
        }

        $qty = $this->getQtyOrdered() - $this->getQtyInvoiced() - $this->getQtyCanceled();
        return max($qty, 0);
    }

    /**
     * Retrieve item qty available for refund
     *
     * @return float|integer
     * @since 2.0.0
     */
    public function getQtyToRefund()
    {
        if ($this->isDummy()) {
            return 0;
        }

        return max($this->getQtyInvoiced() - $this->getQtyRefunded(), 0);
    }

    /**
     * Retrieve item qty available for cancel
     *
     * @return float|integer
     * @since 2.0.0
     */
    public function getQtyToCancel()
    {
        $qtyToCancel = min($this->getQtyToInvoice(), $this->getQtyToShip());
        return max($qtyToCancel, 0);
    }

    /**
     * Declare order
     *
     * @param \Magento\Sales\Model\Order $order
     * @return $this
     * @since 2.0.0
     */
    public function setOrder(\Magento\Sales\Model\Order $order)
    {
        $this->_order = $order;
        $this->setOrderId($order->getId());
        return $this;
    }

    /**
     * Retrieve order model object
     *
     * @return \Magento\Sales\Model\Order
     * @since 2.0.0
     */
    public function getOrder()
    {
        if ($this->_order === null && ($orderId = $this->getOrderId())) {
            $order = $this->_orderFactory->create();
            $order->load($orderId);
            $this->setOrder($order);
        }
        return $this->_order;
    }

    /**
     * Retrieve item status identifier
     *
     * @return int
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @since 2.0.0
     */
    public function getStatusId()
    {
        $backordered = (double)$this->getQtyBackordered();
        if (!$backordered && $this->getHasChildren()) {
            $backordered = (double)$this->_getQtyChildrenBackordered();
        }
        $canceled = (double)$this->getQtyCanceled();
        $invoiced = (double)$this->getQtyInvoiced();
        $ordered = (double)$this->getQtyOrdered();
        $refunded = (double)$this->getQtyRefunded();
        $shipped = (double)$this->getQtyShipped();

        $actuallyOrdered = $ordered - $canceled - $refunded;

        if (!$invoiced && !$shipped && !$refunded && !$canceled && !$backordered) {
            return self::STATUS_PENDING;
        }
        if ($shipped && $invoiced && $actuallyOrdered == $shipped) {
            return self::STATUS_SHIPPED;
        }

        if ($invoiced && !$shipped && $actuallyOrdered == $invoiced) {
            return self::STATUS_INVOICED;
        }

        if ($backordered && $actuallyOrdered == $backordered) {
            return self::STATUS_BACKORDERED;
        }

        if ($refunded && $ordered == $refunded) {
            return self::STATUS_REFUNDED;
        }

        if ($canceled && $ordered == $canceled) {
            return self::STATUS_CANCELED;
        }

        if (max($shipped, $invoiced) < $actuallyOrdered) {
            return self::STATUS_PARTIAL;
        }

        return self::STATUS_MIXED;
    }

    /**
     * Retrieve backordered qty of children items
     *
     * @return float|null
     * @since 2.0.0
     */
    protected function _getQtyChildrenBackordered()
    {
        $backordered = null;
        foreach ($this->_children as $childItem) {
            $backordered += (double)$childItem->getQtyBackordered();
        }

        return $backordered;
    }

    /**
     * Retrieve status
     *
     * @return string
     * @since 2.0.0
     */
    public function getStatus()
    {
        return $this->getStatusName($this->getStatusId());
    }

    /**
     * Retrieve status name
     *
     * @param string $statusId
     * @return \Magento\Framework\Phrase
     * @since 2.0.0
     */
    public static function getStatusName($statusId)
    {
        if (self::$_statuses === null) {
            self::getStatuses();
        }
        if (isset(self::$_statuses[$statusId])) {
            return self::$_statuses[$statusId];
        }
        return __('Unknown Status');
    }

    /**
     * Cancel order item
     *
     * @return $this
     * @since 2.0.0
     */
    public function cancel()
    {
        if ($this->getStatusId() !== self::STATUS_CANCELED) {
            $this->_eventManager->dispatch('sales_order_item_cancel', ['item' => $this]);
            $this->setQtyCanceled($this->getQtyToCancel());
            $this->setTaxCanceled(
                $this->getTaxCanceled() + $this->getBaseTaxAmount() * $this->getQtyCanceled() / $this->getQtyOrdered()
            );
            $this->setDiscountTaxCompensationCanceled(
                $this->getDiscountTaxCompensationCanceled() +
                $this->getDiscountTaxCompensationAmount() * $this->getQtyCanceled() / $this->getQtyOrdered()
            );
        }
        return $this;
    }

    /**
     * Retrieve order item statuses array
     *
     * @return array
     * @since 2.0.0
     */
    public static function getStatuses()
    {
        if (self::$_statuses === null) {
            self::$_statuses = [
                self::STATUS_PENDING => __('Ordered'),
                self::STATUS_SHIPPED => __('Shipped'),
                self::STATUS_INVOICED => __('Invoiced'),
                self::STATUS_BACKORDERED => __('Backordered'),
                self::STATUS_RETURNED => __('Returned'),
                self::STATUS_REFUNDED => __('Refunded'),
                self::STATUS_CANCELED => __('Canceled'),
                self::STATUS_PARTIAL => __('Partial'),
                self::STATUS_MIXED => __('Mixed'),
            ];
        }
        return self::$_statuses;
    }

    /**
     * Redeclare getter for back compatibility
     *
     * @return float|null
     * @since 2.0.0
     */
    public function getOriginalPrice()
    {
        $price = $this->getData(OrderItemInterface::ORIGINAL_PRICE);
        if ($price === null) {
            return $this->getPrice();
        }
        return $price;
    }

    /**
     * Set product options
     *
     * @param array $options
     * @return $this
     * @since 2.0.0
     */
    public function setProductOptions(array $options = null)
    {
        $this->setData('product_options', $options);
        return $this;
    }

    /**
     * Get product options array
     *
     * @return array
     * @since 2.0.0
     */
    public function getProductOptions()
    {
        $data = $this->_getData('product_options');
        if (is_string($data)) {
            $data = $this->serializer->unserialize($data);
        }
        return $data;
    }

    /**
     * Get product options array by code.
     * If code is null return all options
     *
     * @param string $code
     * @return array
     * @since 2.0.0
     */
    public function getProductOptionByCode($code = null)
    {
        $options = $this->getProductOptions();
        if ($code === null) {
            return $options;
        }
        if (isset($options[$code])) {
            return $options[$code];
        }
        return null;
    }

    /**
     * Return real product type of item or NULL if item is not composite
     *
     * @return string | null
     * @since 2.0.0
     */
    public function getRealProductType()
    {
        $productType = $this->getProductOptionByCode('real_product_type');
        if ($productType) {
            return $productType;
        }
        return null;
    }

    /**
     * Adds child item to this item
     *
     * @param Item $item
     * @return void
     * @since 2.0.0
     */
    public function addChildItem($item)
    {
        if ($item instanceof \Magento\Sales\Model\Order\Item) {
            $this->_children[] = $item;
        } elseif (is_array($item)) {
            $this->_children = array_merge($this->_children, $item);
        }
    }

    /**
     * Return children items of this item
     *
     * @return array
     * @since 2.0.0
     */
    public function getChildrenItems()
    {
        return $this->_children;
    }

    /**
     * Return checking of what calculation
     * type was for this product
     *
     * @return bool
     * @since 2.0.0
     */
    public function isChildrenCalculated()
    {
        $parentItem = $this->getParentItem();
        if ($parentItem) {
            $options = $parentItem->getProductOptions();
        } else {
            $options = $this->getProductOptions();
        }

        if (isset(
            $options['product_calculations']
        ) && $options['product_calculations'] == \Magento\Catalog\Model\Product\Type\AbstractType::CALCULATE_CHILD
        ) {
            return true;
        }
        return false;
    }

    /**
     * Check if discount has to be applied to parent item
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     * @since 2.0.0
     */
    public function getForceApplyDiscountToParentItem()
    {
        if ($this->getParentItem()) {
            $product = $this->getParentItem()->getProduct();
        } else {
            $product = $this->getProduct();
        }

        return $product->getTypeInstance()->getForceApplyDiscountToParentItem();
    }

    /**
     * Return checking of what shipment
     * type was for this product
     *
     * @return bool
     * @since 2.0.0
     */
    public function isShipSeparately()
    {
        $parentItem = $this->getParentItem();
        if ($parentItem) {
            $options = $parentItem->getProductOptions();
        } else {
            $options = $this->getProductOptions();
        }

        if (isset(
            $options['shipment_type']
        ) && $options['shipment_type'] == \Magento\Catalog\Model\Product\Type\AbstractType::SHIPMENT_SEPARATELY
        ) {
            return true;
        }
        return false;
    }

    /**
     * This is Dummy item or not
     * if $shipment is true then we checking this for shipping situation if not
     * then we checking this for calculation
     *
     * @param bool $shipment
     * @return bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @since 2.0.0
     */
    public function isDummy($shipment = false)
    {
        if ($shipment) {
            if ($this->getHasChildren() && $this->isShipSeparately()) {
                return true;
            }

            if ($this->getHasChildren() && !$this->isShipSeparately()) {
                return false;
            }

            if ($this->getParentItem() && $this->isShipSeparately()) {
                return false;
            }

            if ($this->getParentItem() && !$this->isShipSeparately()) {
                return true;
            }
        } else {
            if ($this->getHasChildren() && $this->isChildrenCalculated()) {
                return true;
            }

            if ($this->getHasChildren() && !$this->isChildrenCalculated()) {
                return false;
            }

            if ($this->getParentItem() && $this->isChildrenCalculated()) {
                return false;
            }

            if ($this->getParentItem() && !$this->isChildrenCalculated()) {
                return true;
            }
        }
        return false;
    }

    /**
     * Returns formatted buy request - object, holding request received from
     * product view page with keys and options for configured product
     *
     * @return \Magento\Framework\DataObject
     * @since 2.0.0
     */
    public function getBuyRequest()
    {
        $option = $this->getProductOptionByCode('info_buyRequest');
        if (!$option) {
            $option = [];
        }
        $buyRequest = new \Magento\Framework\DataObject($option);
        $buyRequest->setQty($this->getQtyOrdered() * 1);
        return $buyRequest;
    }

    /**
     * Retrieve product
     *
     * @return \Magento\Catalog\Model\Product|null
     * @since 2.0.0
     */
    public function getProduct()
    {
        if (!$this->hasData('product')) {
            try {
                $product = $this->productRepository->getById($this->getProductId());
            } catch (\Magento\Framework\Exception\NoSuchEntityException $noEntityException) {
                $product = null;
            }
            $this->setProduct($product);
        }
        return $this->getData('product');
    }

    /**
     * Retrieve store model instance
     *
     * @return \Magento\Store\Model\Store
     * @since 2.0.0
     */
    public function getStore()
    {
        $storeId = $this->getStoreId();
        if ($storeId) {
            return $this->_storeManager->getStore($storeId);
        }
        return $this->_storeManager->getStore();
    }

    //@codeCoverageIgnoreStart

    /**
     * Returns additional_data
     *
     * @return string|null
     * @since 2.0.0
     */
    public function getAdditionalData()
    {
        return $this->getData(OrderItemInterface::ADDITIONAL_DATA);
    }

    /**
     * Returns amount_refunded
     *
     * @return float|null
     * @since 2.0.0
     */
    public function getAmountRefunded()
    {
        return $this->getData(OrderItemInterface::AMOUNT_REFUNDED);
    }

    /**
     * Returns applied_rule_ids
     *
     * @return string|null
     * @since 2.0.0
     */
    public function getAppliedRuleIds()
    {
        return $this->getData(OrderItemInterface::APPLIED_RULE_IDS);
    }

    /**
     * Returns base_amount_refunded
     *
     * @return float|null
     * @since 2.0.0
     */
    public function getBaseAmountRefunded()
    {
        return $this->getData(OrderItemInterface::BASE_AMOUNT_REFUNDED);
    }

    /**
     * Returns base_cost
     *
     * @return float|null
     * @since 2.0.0
     */
    public function getBaseCost()
    {
        return $this->getData(OrderItemInterface::BASE_COST);
    }

    /**
     * Returns base_discount_amount
     *
     * @return float|null
     * @since 2.0.0
     */
    public function getBaseDiscountAmount()
    {
        return $this->getData(OrderItemInterface::BASE_DISCOUNT_AMOUNT);
    }

    /**
     * Returns base_discount_invoiced
     *
     * @return float|null
     * @since 2.0.0
     */
    public function getBaseDiscountInvoiced()
    {
        return $this->getData(OrderItemInterface::BASE_DISCOUNT_INVOICED);
    }

    /**
     * Returns base_discount_refunded
     *
     * @return float|null
     * @since 2.0.0
     */
    public function getBaseDiscountRefunded()
    {
        return $this->getData(OrderItemInterface::BASE_DISCOUNT_REFUNDED);
    }

    /**
     * Returns base_discount_tax_compensation_amount
     *
     * @return float|null
     * @since 2.0.0
     */
    public function getBaseDiscountTaxCompensationAmount()
    {
        return $this->getData(OrderItemInterface::BASE_DISCOUNT_TAX_COMPENSATION_AMOUNT);
    }

    /**
     * Returns base_discount_tax_compensation_invoiced
     *
     * @return float|null
     * @since 2.0.0
     */
    public function getBaseDiscountTaxCompensationInvoiced()
    {
        return $this->getData(OrderItemInterface::BASE_DISCOUNT_TAX_COMPENSATION_INVOICED);
    }

    /**
     * Returns base_discount_tax_compensation_refunded
     *
     * @return float|null
     * @since 2.0.0
     */
    public function getBaseDiscountTaxCompensationRefunded()
    {
        return $this->getData(OrderItemInterface::BASE_DISCOUNT_TAX_COMPENSATION_REFUNDED);
    }

    /**
     * Returns base_original_price
     *
     * @return float|null
     * @since 2.0.0
     */
    public function getBaseOriginalPrice()
    {
        return $this->getData(OrderItemInterface::BASE_ORIGINAL_PRICE);
    }

    /**
     * Returns base_price
     *
     * @return float|null
     * @since 2.0.0
     */
    public function getBasePrice()
    {
        return $this->getData(OrderItemInterface::BASE_PRICE);
    }

    /**
     * Returns base_price_incl_tax
     *
     * @return float|null
     * @since 2.0.0
     */
    public function getBasePriceInclTax()
    {
        return $this->getData(OrderItemInterface::BASE_PRICE_INCL_TAX);
    }

    /**
     * Returns base_row_invoiced
     *
     * @return float|null
     * @since 2.0.0
     */
    public function getBaseRowInvoiced()
    {
        return $this->getData(OrderItemInterface::BASE_ROW_INVOICED);
    }

    /**
     * Returns base_row_total
     *
     * @return float|null
     * @since 2.0.0
     */
    public function getBaseRowTotal()
    {
        return $this->getData(OrderItemInterface::BASE_ROW_TOTAL);
    }

    /**
     * Returns base_row_total_incl_tax
     *
     * @return float|null
     * @since 2.0.0
     */
    public function getBaseRowTotalInclTax()
    {
        return $this->getData(OrderItemInterface::BASE_ROW_TOTAL_INCL_TAX);
    }

    /**
     * Returns base_tax_amount
     *
     * @return float|null
     * @since 2.0.0
     */
    public function getBaseTaxAmount()
    {
        return $this->getData(OrderItemInterface::BASE_TAX_AMOUNT);
    }

    /**
     * Returns base_tax_before_discount
     *
     * @return float|null
     * @since 2.0.0
     */
    public function getBaseTaxBeforeDiscount()
    {
        return $this->getData(OrderItemInterface::BASE_TAX_BEFORE_DISCOUNT);
    }

    /**
     * Returns base_tax_invoiced
     *
     * @return float|null
     * @since 2.0.0
     */
    public function getBaseTaxInvoiced()
    {
        return $this->getData(OrderItemInterface::BASE_TAX_INVOICED);
    }

    /**
     * Returns base_tax_refunded
     *
     * @return float|null
     * @since 2.0.0
     */
    public function getBaseTaxRefunded()
    {
        return $this->getData(OrderItemInterface::BASE_TAX_REFUNDED);
    }

    /**
     * Returns base_weee_tax_applied_amount
     *
     * @return float|null
     * @since 2.0.0
     */
    public function getBaseWeeeTaxAppliedAmount()
    {
        return $this->getData(OrderItemInterface::BASE_WEEE_TAX_APPLIED_AMOUNT);
    }

    /**
     * Returns base_weee_tax_applied_row_amnt
     *
     * @return float|null
     * @since 2.0.0
     */
    public function getBaseWeeeTaxAppliedRowAmnt()
    {
        return $this->getData(OrderItemInterface::BASE_WEEE_TAX_APPLIED_ROW_AMNT);
    }

    /**
     * Returns base_weee_tax_disposition
     *
     * @return float|null
     * @since 2.0.0
     */
    public function getBaseWeeeTaxDisposition()
    {
        return $this->getData(OrderItemInterface::BASE_WEEE_TAX_DISPOSITION);
    }

    /**
     * Returns base_weee_tax_row_disposition
     *
     * @return float|null
     * @since 2.0.0
     */
    public function getBaseWeeeTaxRowDisposition()
    {
        return $this->getData(OrderItemInterface::BASE_WEEE_TAX_ROW_DISPOSITION);
    }

    /**
     * Returns created_at
     *
     * @return string|null
     * @since 2.0.0
     */
    public function getCreatedAt()
    {
        return $this->getData(OrderItemInterface::CREATED_AT);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(OrderItemInterface::CREATED_AT, $createdAt);
    }

    /**
     * Returns description
     *
     * @return string|null
     * @since 2.0.0
     */
    public function getDescription()
    {
        return $this->getData(OrderItemInterface::DESCRIPTION);
    }

    /**
     * Returns discount_amount
     *
     * @return float|null
     * @since 2.0.0
     */
    public function getDiscountAmount()
    {
        return $this->getData(OrderItemInterface::DISCOUNT_AMOUNT);
    }

    /**
     * Returns discount_invoiced
     *
     * @return float|null
     * @since 2.0.0
     */
    public function getDiscountInvoiced()
    {
        return $this->getData(OrderItemInterface::DISCOUNT_INVOICED);
    }

    /**
     * Returns discount_percent
     *
     * @return float|null
     * @since 2.0.0
     */
    public function getDiscountPercent()
    {
        return $this->getData(OrderItemInterface::DISCOUNT_PERCENT);
    }

    /**
     * Returns discount_refunded
     *
     * @return float|null
     * @since 2.0.0
     */
    public function getDiscountRefunded()
    {
        return $this->getData(OrderItemInterface::DISCOUNT_REFUNDED);
    }

    /**
     * Returns event_id
     *
     * @return int|null
     * @since 2.0.0
     */
    public function getEventId()
    {
        return $this->getData(OrderItemInterface::EVENT_ID);
    }

    /**
     * Returns ext_order_item_id
     *
     * @return string|null
     * @since 2.0.0
     */
    public function getExtOrderItemId()
    {
        return $this->getData(OrderItemInterface::EXT_ORDER_ITEM_ID);
    }

    /**
     * Returns free_shipping
     *
     * @return int|null
     * @since 2.0.0
     */
    public function getFreeShipping()
    {
        return $this->getData(OrderItemInterface::FREE_SHIPPING);
    }

    /**
     * Returns gw_base_price
     *
     * @return float|null
     * @since 2.0.0
     */
    public function getGwBasePrice()
    {
        return $this->getData(OrderItemInterface::GW_BASE_PRICE);
    }

    /**
     * Returns gw_base_price_invoiced
     *
     * @return float|null
     * @since 2.0.0
     */
    public function getGwBasePriceInvoiced()
    {
        return $this->getData(OrderItemInterface::GW_BASE_PRICE_INVOICED);
    }

    /**
     * Returns gw_base_price_refunded
     *
     * @return float|null
     * @since 2.0.0
     */
    public function getGwBasePriceRefunded()
    {
        return $this->getData(OrderItemInterface::GW_BASE_PRICE_REFUNDED);
    }

    /**
     * Returns gw_base_tax_amount
     *
     * @return float|null
     * @since 2.0.0
     */
    public function getGwBaseTaxAmount()
    {
        return $this->getData(OrderItemInterface::GW_BASE_TAX_AMOUNT);
    }

    /**
     * Returns gw_base_tax_amount_invoiced
     *
     * @return float|null
     * @since 2.0.0
     */
    public function getGwBaseTaxAmountInvoiced()
    {
        return $this->getData(OrderItemInterface::GW_BASE_TAX_AMOUNT_INVOICED);
    }

    /**
     * Returns gw_base_tax_amount_refunded
     *
     * @return float|null
     * @since 2.0.0
     */
    public function getGwBaseTaxAmountRefunded()
    {
        return $this->getData(OrderItemInterface::GW_BASE_TAX_AMOUNT_REFUNDED);
    }

    /**
     * Returns gw_id
     *
     * @return int|null
     * @since 2.0.0
     */
    public function getGwId()
    {
        return $this->getData(OrderItemInterface::GW_ID);
    }

    /**
     * Returns gw_price
     *
     * @return float|null
     * @since 2.0.0
     */
    public function getGwPrice()
    {
        return $this->getData(OrderItemInterface::GW_PRICE);
    }

    /**
     * Returns gw_price_invoiced
     *
     * @return float|null
     * @since 2.0.0
     */
    public function getGwPriceInvoiced()
    {
        return $this->getData(OrderItemInterface::GW_PRICE_INVOICED);
    }

    /**
     * Returns gw_price_refunded
     *
     * @return float|null
     * @since 2.0.0
     */
    public function getGwPriceRefunded()
    {
        return $this->getData(OrderItemInterface::GW_PRICE_REFUNDED);
    }

    /**
     * Returns gw_tax_amount
     *
     * @return float|null
     * @since 2.0.0
     */
    public function getGwTaxAmount()
    {
        return $this->getData(OrderItemInterface::GW_TAX_AMOUNT);
    }

    /**
     * Returns gw_tax_amount_invoiced
     *
     * @return float|null
     * @since 2.0.0
     */
    public function getGwTaxAmountInvoiced()
    {
        return $this->getData(OrderItemInterface::GW_TAX_AMOUNT_INVOICED);
    }

    /**
     * Returns gw_tax_amount_refunded
     *
     * @return float|null
     * @since 2.0.0
     */
    public function getGwTaxAmountRefunded()
    {
        return $this->getData(OrderItemInterface::GW_TAX_AMOUNT_REFUNDED);
    }

    /**
     * Returns discount_tax_compensation_amount
     *
     * @return float|null
     * @since 2.0.0
     */
    public function getDiscountTaxCompensationAmount()
    {
        return $this->getData(OrderItemInterface::DISCOUNT_TAX_COMPENSATION_AMOUNT);
    }

    /**
     * Returns discount_tax_compensation_canceled
     *
     * @return float|null
     * @since 2.0.0
     */
    public function getDiscountTaxCompensationCanceled()
    {
        return $this->getData(OrderItemInterface::DISCOUNT_TAX_COMPENSATION_CANCELED);
    }

    /**
     * Returns discount_tax_compensation_invoiced
     *
     * @return float|null
     * @since 2.0.0
     */
    public function getDiscountTaxCompensationInvoiced()
    {
        return $this->getData(OrderItemInterface::DISCOUNT_TAX_COMPENSATION_INVOICED);
    }

    /**
     * Returns discount_tax_compensation_refunded
     *
     * @return float|null
     * @since 2.0.0
     */
    public function getDiscountTaxCompensationRefunded()
    {
        return $this->getData(OrderItemInterface::DISCOUNT_TAX_COMPENSATION_REFUNDED);
    }

    /**
     * Returns is_qty_decimal
     *
     * @return int|null
     * @since 2.0.0
     */
    public function getIsQtyDecimal()
    {
        return $this->getData(OrderItemInterface::IS_QTY_DECIMAL);
    }

    /**
     * Returns is_virtual
     *
     * @return int|null
     * @since 2.0.0
     */
    public function getIsVirtual()
    {
        return $this->getData(OrderItemInterface::IS_VIRTUAL);
    }

    /**
     * Returns item_id
     *
     * @return int|null
     * @since 2.0.0
     */
    public function getItemId()
    {
        return $this->getData(OrderItemInterface::ITEM_ID);
    }

    /**
     * Returns locked_do_invoice
     *
     * @return int|null
     * @since 2.0.0
     */
    public function getLockedDoInvoice()
    {
        return $this->getData(OrderItemInterface::LOCKED_DO_INVOICE);
    }

    /**
     * Returns locked_do_ship
     *
     * @return int|null
     * @since 2.0.0
     */
    public function getLockedDoShip()
    {
        return $this->getData(OrderItemInterface::LOCKED_DO_SHIP);
    }

    /**
     * Returns name
     *
     * @return string|null
     * @since 2.0.0
     */
    public function getName()
    {
        return $this->getData(OrderItemInterface::NAME);
    }

    /**
     * Returns no_discount
     *
     * @return int|null
     * @since 2.0.0
     */
    public function getNoDiscount()
    {
        return $this->getData(OrderItemInterface::NO_DISCOUNT);
    }

    /**
     * Returns order_id
     *
     * @return int|null
     * @since 2.0.0
     */
    public function getOrderId()
    {
        return $this->getData(OrderItemInterface::ORDER_ID);
    }

    /**
     * Returns parent_item_id
     *
     * @return int|null
     * @since 2.0.0
     */
    public function getParentItemId()
    {
        return $this->getData(OrderItemInterface::PARENT_ITEM_ID);
    }

    /**
     * Returns price
     *
     * @return float|null
     * @since 2.0.0
     */
    public function getPrice()
    {
        return $this->getData(OrderItemInterface::PRICE);
    }

    /**
     * Returns price_incl_tax
     *
     * @return float|null
     * @since 2.0.0
     */
    public function getPriceInclTax()
    {
        return $this->getData(OrderItemInterface::PRICE_INCL_TAX);
    }

    /**
     * Returns product_id
     *
     * @return int|null
     * @since 2.0.0
     */
    public function getProductId()
    {
        return $this->getData(OrderItemInterface::PRODUCT_ID);
    }

    /**
     * Returns product_type
     *
     * @return string|null
     * @since 2.0.0
     */
    public function getProductType()
    {
        return $this->getData(OrderItemInterface::PRODUCT_TYPE);
    }

    /**
     * Returns qty_backordered
     *
     * @return float|null
     * @since 2.0.0
     */
    public function getQtyBackordered()
    {
        return $this->getData(OrderItemInterface::QTY_BACKORDERED);
    }

    /**
     * Returns qty_canceled
     *
     * @return float|null
     * @since 2.0.0
     */
    public function getQtyCanceled()
    {
        return $this->getData(OrderItemInterface::QTY_CANCELED);
    }

    /**
     * Returns qty_invoiced
     *
     * @return float|null
     * @since 2.0.0
     */
    public function getQtyInvoiced()
    {
        return $this->getData(OrderItemInterface::QTY_INVOICED);
    }

    /**
     * Returns qty_ordered
     *
     * @return float|null
     * @since 2.0.0
     */
    public function getQtyOrdered()
    {
        return $this->getData(OrderItemInterface::QTY_ORDERED);
    }

    /**
     * Returns qty_refunded
     *
     * @return float|null
     * @since 2.0.0
     */
    public function getQtyRefunded()
    {
        return $this->getData(OrderItemInterface::QTY_REFUNDED);
    }

    /**
     * Returns qty_returned
     *
     * @return float|null
     * @since 2.0.0
     */
    public function getQtyReturned()
    {
        return $this->getData(OrderItemInterface::QTY_RETURNED);
    }

    /**
     * Returns qty_shipped
     *
     * @return float|null
     * @since 2.0.0
     */
    public function getQtyShipped()
    {
        return $this->getData(OrderItemInterface::QTY_SHIPPED);
    }

    /**
     * Returns quote_item_id
     *
     * @return int|null
     * @since 2.0.0
     */
    public function getQuoteItemId()
    {
        return $this->getData(OrderItemInterface::QUOTE_ITEM_ID);
    }

    /**
     * Returns row_invoiced
     *
     * @return float|null
     * @since 2.0.0
     */
    public function getRowInvoiced()
    {
        return $this->getData(OrderItemInterface::ROW_INVOICED);
    }

    /**
     * Returns row_total
     *
     * @return float|null
     * @since 2.0.0
     */
    public function getRowTotal()
    {
        return $this->getData(OrderItemInterface::ROW_TOTAL);
    }

    /**
     * Returns row_total_incl_tax
     *
     * @return float|null
     * @since 2.0.0
     */
    public function getRowTotalInclTax()
    {
        return $this->getData(OrderItemInterface::ROW_TOTAL_INCL_TAX);
    }

    /**
     * Returns row_weight
     *
     * @return float|null
     * @since 2.0.0
     */
    public function getRowWeight()
    {
        return $this->getData(OrderItemInterface::ROW_WEIGHT);
    }

    /**
     * Returns sku
     *
     * @return string
     * @since 2.0.0
     */
    public function getSku()
    {
        return $this->getData(OrderItemInterface::SKU);
    }

    /**
     * Returns store_id
     *
     * @return int|null
     * @since 2.0.0
     */
    public function getStoreId()
    {
        return $this->getData(OrderItemInterface::STORE_ID);
    }

    /**
     * Returns tax_amount
     *
     * @return float|null
     * @since 2.0.0
     */
    public function getTaxAmount()
    {
        return $this->getData(OrderItemInterface::TAX_AMOUNT);
    }

    /**
     * Returns tax_before_discount
     *
     * @return float|null
     * @since 2.0.0
     */
    public function getTaxBeforeDiscount()
    {
        return $this->getData(OrderItemInterface::TAX_BEFORE_DISCOUNT);
    }

    /**
     * Returns tax_canceled
     *
     * @return float|null
     * @since 2.0.0
     */
    public function getTaxCanceled()
    {
        return $this->getData(OrderItemInterface::TAX_CANCELED);
    }

    /**
     * Returns tax_invoiced
     *
     * @return float|null
     * @since 2.0.0
     */
    public function getTaxInvoiced()
    {
        return $this->getData(OrderItemInterface::TAX_INVOICED);
    }

    /**
     * Returns tax_percent
     *
     * @return float|null
     * @since 2.0.0
     */
    public function getTaxPercent()
    {
        return $this->getData(OrderItemInterface::TAX_PERCENT);
    }

    /**
     * Returns tax_refunded
     *
     * @return float|null
     * @since 2.0.0
     */
    public function getTaxRefunded()
    {
        return $this->getData(OrderItemInterface::TAX_REFUNDED);
    }

    /**
     * Returns updated_at
     *
     * @return string|null
     * @since 2.0.0
     */
    public function getUpdatedAt()
    {
        return $this->getData(OrderItemInterface::UPDATED_AT);
    }

    /**
     * Returns weee_tax_applied
     *
     * @return string|null
     * @since 2.0.0
     */
    public function getWeeeTaxApplied()
    {
        return $this->getData(OrderItemInterface::WEEE_TAX_APPLIED);
    }

    /**
     * Returns weee_tax_applied_amount
     *
     * @return float|null
     * @since 2.0.0
     */
    public function getWeeeTaxAppliedAmount()
    {
        return $this->getData(OrderItemInterface::WEEE_TAX_APPLIED_AMOUNT);
    }

    /**
     * Returns weee_tax_applied_row_amount
     *
     * @return float|null
     * @since 2.0.0
     */
    public function getWeeeTaxAppliedRowAmount()
    {
        return $this->getData(OrderItemInterface::WEEE_TAX_APPLIED_ROW_AMOUNT);
    }

    /**
     * Returns weee_tax_disposition
     *
     * @return float|null
     * @since 2.0.0
     */
    public function getWeeeTaxDisposition()
    {
        return $this->getData(OrderItemInterface::WEEE_TAX_DISPOSITION);
    }

    /**
     * Returns weee_tax_row_disposition
     *
     * @return float|null
     * @since 2.0.0
     */
    public function getWeeeTaxRowDisposition()
    {
        return $this->getData(OrderItemInterface::WEEE_TAX_ROW_DISPOSITION);
    }

    /**
     * Returns weight
     *
     * @return float|null
     * @since 2.0.0
     */
    public function getWeight()
    {
        return $this->getData(OrderItemInterface::WEIGHT);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setUpdatedAt($timestamp)
    {
        return $this->setData(OrderItemInterface::UPDATED_AT, $timestamp);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setItemId($id)
    {
        return $this->setData(OrderItemInterface::ITEM_ID, $id);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setOrderId($id)
    {
        return $this->setData(OrderItemInterface::ORDER_ID, $id);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setParentItemId($id)
    {
        return $this->setData(OrderItemInterface::PARENT_ITEM_ID, $id);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setQuoteItemId($id)
    {
        return $this->setData(OrderItemInterface::QUOTE_ITEM_ID, $id);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setStoreId($id)
    {
        return $this->setData(OrderItemInterface::STORE_ID, $id);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setProductId($id)
    {
        return $this->setData(OrderItemInterface::PRODUCT_ID, $id);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setProductType($productType)
    {
        return $this->setData(OrderItemInterface::PRODUCT_TYPE, $productType);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setWeight($weight)
    {
        return $this->setData(OrderItemInterface::WEIGHT, $weight);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setIsVirtual($isVirtual)
    {
        return $this->setData(OrderItemInterface::IS_VIRTUAL, $isVirtual);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setSku($sku)
    {
        return $this->setData(OrderItemInterface::SKU, $sku);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setName($name)
    {
        return $this->setData(OrderItemInterface::NAME, $name);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setDescription($description)
    {
        return $this->setData(OrderItemInterface::DESCRIPTION, $description);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setAppliedRuleIds($appliedRuleIds)
    {
        return $this->setData(OrderItemInterface::APPLIED_RULE_IDS, $appliedRuleIds);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setAdditionalData($additionalData)
    {
        return $this->setData(OrderItemInterface::ADDITIONAL_DATA, $additionalData);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setIsQtyDecimal($isQtyDecimal)
    {
        return $this->setData(OrderItemInterface::IS_QTY_DECIMAL, $isQtyDecimal);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setNoDiscount($noDiscount)
    {
        return $this->setData(OrderItemInterface::NO_DISCOUNT, $noDiscount);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setQtyBackordered($qtyBackordered)
    {
        return $this->setData(OrderItemInterface::QTY_BACKORDERED, $qtyBackordered);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setQtyCanceled($qtyCanceled)
    {
        return $this->setData(OrderItemInterface::QTY_CANCELED, $qtyCanceled);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setQtyInvoiced($qtyInvoiced)
    {
        return $this->setData(OrderItemInterface::QTY_INVOICED, $qtyInvoiced);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setQtyOrdered($qtyOrdered)
    {
        return $this->setData(OrderItemInterface::QTY_ORDERED, $qtyOrdered);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setQtyRefunded($qtyRefunded)
    {
        return $this->setData(OrderItemInterface::QTY_REFUNDED, $qtyRefunded);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setQtyShipped($qtyShipped)
    {
        return $this->setData(OrderItemInterface::QTY_SHIPPED, $qtyShipped);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setBaseCost($baseCost)
    {
        return $this->setData(OrderItemInterface::BASE_COST, $baseCost);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setPrice($price)
    {
        return $this->setData(OrderItemInterface::PRICE, $price);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setBasePrice($price)
    {
        return $this->setData(OrderItemInterface::BASE_PRICE, $price);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setOriginalPrice($price)
    {
        return $this->setData(OrderItemInterface::ORIGINAL_PRICE, $price);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setBaseOriginalPrice($price)
    {
        return $this->setData(OrderItemInterface::BASE_ORIGINAL_PRICE, $price);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setTaxPercent($taxPercent)
    {
        return $this->setData(OrderItemInterface::TAX_PERCENT, $taxPercent);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setTaxAmount($amount)
    {
        return $this->setData(OrderItemInterface::TAX_AMOUNT, $amount);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setBaseTaxAmount($amount)
    {
        return $this->setData(OrderItemInterface::BASE_TAX_AMOUNT, $amount);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setTaxInvoiced($taxInvoiced)
    {
        return $this->setData(OrderItemInterface::TAX_INVOICED, $taxInvoiced);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setBaseTaxInvoiced($baseTaxInvoiced)
    {
        return $this->setData(OrderItemInterface::BASE_TAX_INVOICED, $baseTaxInvoiced);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setDiscountPercent($discountPercent)
    {
        return $this->setData(OrderItemInterface::DISCOUNT_PERCENT, $discountPercent);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setDiscountAmount($amount)
    {
        return $this->setData(OrderItemInterface::DISCOUNT_AMOUNT, $amount);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setBaseDiscountAmount($amount)
    {
        return $this->setData(OrderItemInterface::BASE_DISCOUNT_AMOUNT, $amount);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setDiscountInvoiced($discountInvoiced)
    {
        return $this->setData(OrderItemInterface::DISCOUNT_INVOICED, $discountInvoiced);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setBaseDiscountInvoiced($baseDiscountInvoiced)
    {
        return $this->setData(OrderItemInterface::BASE_DISCOUNT_INVOICED, $baseDiscountInvoiced);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setAmountRefunded($amountRefunded)
    {
        return $this->setData(OrderItemInterface::AMOUNT_REFUNDED, $amountRefunded);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setBaseAmountRefunded($baseAmountRefunded)
    {
        return $this->setData(OrderItemInterface::BASE_AMOUNT_REFUNDED, $baseAmountRefunded);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setRowTotal($amount)
    {
        return $this->setData(OrderItemInterface::ROW_TOTAL, $amount);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setBaseRowTotal($amount)
    {
        return $this->setData(OrderItemInterface::BASE_ROW_TOTAL, $amount);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setRowInvoiced($rowInvoiced)
    {
        return $this->setData(OrderItemInterface::ROW_INVOICED, $rowInvoiced);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setBaseRowInvoiced($baseRowInvoiced)
    {
        return $this->setData(OrderItemInterface::BASE_ROW_INVOICED, $baseRowInvoiced);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setRowWeight($rowWeight)
    {
        return $this->setData(OrderItemInterface::ROW_WEIGHT, $rowWeight);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setBaseTaxBeforeDiscount($baseTaxBeforeDiscount)
    {
        return $this->setData(OrderItemInterface::BASE_TAX_BEFORE_DISCOUNT, $baseTaxBeforeDiscount);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setTaxBeforeDiscount($taxBeforeDiscount)
    {
        return $this->setData(OrderItemInterface::TAX_BEFORE_DISCOUNT, $taxBeforeDiscount);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setExtOrderItemId($id)
    {
        return $this->setData(OrderItemInterface::EXT_ORDER_ITEM_ID, $id);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setLockedDoInvoice($flag)
    {
        return $this->setData(OrderItemInterface::LOCKED_DO_INVOICE, $flag);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setLockedDoShip($flag)
    {
        return $this->setData(OrderItemInterface::LOCKED_DO_SHIP, $flag);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setPriceInclTax($amount)
    {
        return $this->setData(OrderItemInterface::PRICE_INCL_TAX, $amount);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setBasePriceInclTax($amount)
    {
        return $this->setData(OrderItemInterface::BASE_PRICE_INCL_TAX, $amount);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setRowTotalInclTax($amount)
    {
        return $this->setData(OrderItemInterface::ROW_TOTAL_INCL_TAX, $amount);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setBaseRowTotalInclTax($amount)
    {
        return $this->setData(OrderItemInterface::BASE_ROW_TOTAL_INCL_TAX, $amount);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setDiscountTaxCompensationAmount($amount)
    {
        return $this->setData(OrderItemInterface::DISCOUNT_TAX_COMPENSATION_AMOUNT, $amount);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setBaseDiscountTaxCompensationAmount($amount)
    {
        return $this->setData(OrderItemInterface::BASE_DISCOUNT_TAX_COMPENSATION_AMOUNT, $amount);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setDiscountTaxCompensationInvoiced($discountTaxCompensationInvoiced)
    {
        return $this->setData(OrderItemInterface::DISCOUNT_TAX_COMPENSATION_INVOICED, $discountTaxCompensationInvoiced);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setBaseDiscountTaxCompensationInvoiced($baseDiscountTaxCompensationInvoiced)
    {
        return $this->setData(
            OrderItemInterface::BASE_DISCOUNT_TAX_COMPENSATION_INVOICED,
            $baseDiscountTaxCompensationInvoiced
        );
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setDiscountTaxCompensationRefunded($discountTaxCompensationRefunded)
    {
        return $this->setData(OrderItemInterface::DISCOUNT_TAX_COMPENSATION_REFUNDED, $discountTaxCompensationRefunded);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setBaseDiscountTaxCompensationRefunded($baseDiscountTaxCompensationRefunded)
    {
        return $this->setData(
            OrderItemInterface::BASE_DISCOUNT_TAX_COMPENSATION_REFUNDED,
            $baseDiscountTaxCompensationRefunded
        );
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setTaxCanceled($taxCanceled)
    {
        return $this->setData(OrderItemInterface::TAX_CANCELED, $taxCanceled);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setDiscountTaxCompensationCanceled($discountTaxCompensationCanceled)
    {
        return $this->setData(OrderItemInterface::DISCOUNT_TAX_COMPENSATION_CANCELED, $discountTaxCompensationCanceled);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setTaxRefunded($taxRefunded)
    {
        return $this->setData(OrderItemInterface::TAX_REFUNDED, $taxRefunded);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setBaseTaxRefunded($baseTaxRefunded)
    {
        return $this->setData(OrderItemInterface::BASE_TAX_REFUNDED, $baseTaxRefunded);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setDiscountRefunded($discountRefunded)
    {
        return $this->setData(OrderItemInterface::DISCOUNT_REFUNDED, $discountRefunded);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setBaseDiscountRefunded($baseDiscountRefunded)
    {
        return $this->setData(OrderItemInterface::BASE_DISCOUNT_REFUNDED, $baseDiscountRefunded);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setGwId($id)
    {
        return $this->setData(OrderItemInterface::GW_ID, $id);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setGwBasePrice($price)
    {
        return $this->setData(OrderItemInterface::GW_BASE_PRICE, $price);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setGwPrice($price)
    {
        return $this->setData(OrderItemInterface::GW_PRICE, $price);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setGwBaseTaxAmount($amount)
    {
        return $this->setData(OrderItemInterface::GW_BASE_TAX_AMOUNT, $amount);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setGwTaxAmount($amount)
    {
        return $this->setData(OrderItemInterface::GW_TAX_AMOUNT, $amount);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setGwBasePriceInvoiced($gwBasePriceInvoiced)
    {
        return $this->setData(OrderItemInterface::GW_BASE_PRICE_INVOICED, $gwBasePriceInvoiced);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setGwPriceInvoiced($gwPriceInvoiced)
    {
        return $this->setData(OrderItemInterface::GW_PRICE_INVOICED, $gwPriceInvoiced);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setGwBaseTaxAmountInvoiced($gwBaseTaxAmountInvoiced)
    {
        return $this->setData(OrderItemInterface::GW_BASE_TAX_AMOUNT_INVOICED, $gwBaseTaxAmountInvoiced);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setGwTaxAmountInvoiced($gwTaxAmountInvoiced)
    {
        return $this->setData(OrderItemInterface::GW_TAX_AMOUNT_INVOICED, $gwTaxAmountInvoiced);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setGwBasePriceRefunded($gwBasePriceRefunded)
    {
        return $this->setData(OrderItemInterface::GW_BASE_PRICE_REFUNDED, $gwBasePriceRefunded);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setGwPriceRefunded($gwPriceRefunded)
    {
        return $this->setData(OrderItemInterface::GW_PRICE_REFUNDED, $gwPriceRefunded);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setGwBaseTaxAmountRefunded($gwBaseTaxAmountRefunded)
    {
        return $this->setData(OrderItemInterface::GW_BASE_TAX_AMOUNT_REFUNDED, $gwBaseTaxAmountRefunded);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setGwTaxAmountRefunded($gwTaxAmountRefunded)
    {
        return $this->setData(OrderItemInterface::GW_TAX_AMOUNT_REFUNDED, $gwTaxAmountRefunded);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setFreeShipping($freeShipping)
    {
        return $this->setData(OrderItemInterface::FREE_SHIPPING, $freeShipping);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setQtyReturned($qtyReturned)
    {
        return $this->setData(OrderItemInterface::QTY_RETURNED, $qtyReturned);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setEventId($id)
    {
        return $this->setData(OrderItemInterface::EVENT_ID, $id);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setBaseWeeeTaxAppliedAmount($amount)
    {
        return $this->setData(OrderItemInterface::BASE_WEEE_TAX_APPLIED_AMOUNT, $amount);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setBaseWeeeTaxAppliedRowAmnt($amnt)
    {
        return $this->setData(OrderItemInterface::BASE_WEEE_TAX_APPLIED_ROW_AMNT, $amnt);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setWeeeTaxAppliedAmount($amount)
    {
        return $this->setData(OrderItemInterface::WEEE_TAX_APPLIED_AMOUNT, $amount);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setWeeeTaxAppliedRowAmount($amount)
    {
        return $this->setData(OrderItemInterface::WEEE_TAX_APPLIED_ROW_AMOUNT, $amount);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setWeeeTaxApplied($weeeTaxApplied)
    {
        return $this->setData(OrderItemInterface::WEEE_TAX_APPLIED, $weeeTaxApplied);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setWeeeTaxDisposition($weeeTaxDisposition)
    {
        return $this->setData(OrderItemInterface::WEEE_TAX_DISPOSITION, $weeeTaxDisposition);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setWeeeTaxRowDisposition($weeeTaxRowDisposition)
    {
        return $this->setData(OrderItemInterface::WEEE_TAX_ROW_DISPOSITION, $weeeTaxRowDisposition);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setBaseWeeeTaxDisposition($baseWeeeTaxDisposition)
    {
        return $this->setData(OrderItemInterface::BASE_WEEE_TAX_DISPOSITION, $baseWeeeTaxDisposition);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setBaseWeeeTaxRowDisposition($baseWeeeTaxRowDisposition)
    {
        return $this->setData(OrderItemInterface::BASE_WEEE_TAX_ROW_DISPOSITION, $baseWeeeTaxRowDisposition);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getProductOption()
    {
        return $this->getData(self::KEY_PRODUCT_OPTION);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setProductOption(\Magento\Catalog\Api\Data\ProductOptionInterface $productOption)
    {
        return $this->setData(self::KEY_PRODUCT_OPTION, $productOption);
    }

    /**
     * {@inheritdoc}
     *
     * @return \Magento\Sales\Api\Data\OrderItemExtensionInterface|null
     * @since 2.0.0
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * {@inheritdoc}
     *
     * @param \Magento\Sales\Api\Data\OrderItemExtensionInterface $extensionAttributes
     * @return $this
     * @since 2.0.0
     */
    public function setExtensionAttributes(\Magento\Sales\Api\Data\OrderItemExtensionInterface $extensionAttributes)
    {
        return $this->_setExtensionAttributes($extensionAttributes);
    }

    //@codeCoverageIgnoreEnd

    /**
     * Check if it is possible to process item after cancellation
     *
     * @return bool
     * @since 2.2.0
     */
    public function isProcessingAvailable()
    {
        return $this->getQtyToShip() > $this->getQtyToCancel();
    }
}
