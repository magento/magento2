<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order;

use Magento\Framework\Api\AttributeDataBuilder;
use Magento\Framework\Model\AbstractExtensibleModel;
use Magento\Sales\Api\Data\OrderItemInterface;

/**
 * Order Item Model
 *
 * @method \Magento\Sales\Model\Resource\Order\Item _getResource()
 * @method \Magento\Sales\Model\Resource\Order\Item getResource()
 * @method \Magento\Sales\Model\Order\Item setOrderId(int $value)
 * @method \Magento\Sales\Model\Order\Item setParentItemId(int $value)
 * @method \Magento\Sales\Model\Order\Item setQuoteItemId(int $value)
 * @method \Magento\Sales\Model\Order\Item setStoreId(int $value)
 * @method \Magento\Sales\Model\Order\Item setCreatedAt(string $value)
 * @method \Magento\Sales\Model\Order\Item setUpdatedAt(string $value)
 * @method \Magento\Sales\Model\Order\Item setProductId(int $value)
 * @method \Magento\Sales\Model\Order\Item setProductType(string $value)
 * @method \Magento\Sales\Model\Order\Item setWeight(float $value)
 * @method \Magento\Sales\Model\Order\Item setIsVirtual(int $value)
 * @method \Magento\Sales\Model\Order\Item setSku(string $value)
 * @method \Magento\Sales\Model\Order\Item setName(string $value)
 * @method \Magento\Sales\Model\Order\Item setDescription(string $value)
 * @method \Magento\Sales\Model\Order\Item setAppliedRuleIds(string $value)
 * @method \Magento\Sales\Model\Order\Item setAdditionalData(string $value)
 * @method \Magento\Sales\Model\Order\Item setFreeShipping(int $value)
 * @method \Magento\Sales\Model\Order\Item setIsQtyDecimal(int $value)
 * @method \Magento\Sales\Model\Order\Item setNoDiscount(int $value)
 * @method \Magento\Sales\Model\Order\Item setQtyBackordered(float $value)
 * @method \Magento\Sales\Model\Order\Item setQtyCanceled(float $value)
 * @method \Magento\Sales\Model\Order\Item setQtyInvoiced(float $value)
 * @method \Magento\Sales\Model\Order\Item setQtyOrdered(float $value)
 * @method \Magento\Sales\Model\Order\Item setQtyRefunded(float $value)
 * @method \Magento\Sales\Model\Order\Item setQtyShipped(float $value)
 * @method \Magento\Sales\Model\Order\Item setBaseCost(float $value)
 * @method \Magento\Sales\Model\Order\Item setPrice(float $value)
 * @method \Magento\Sales\Model\Order\Item setBasePrice(float $value)
 * @method \Magento\Sales\Model\Order\Item setOriginalPrice(float $value)
 * @method \Magento\Sales\Model\Order\Item setBaseOriginalPrice(float $value)
 * @method \Magento\Sales\Model\Order\Item setTaxPercent(float $value)
 * @method \Magento\Sales\Model\Order\Item setTaxAmount(float $value)
 * @method \Magento\Sales\Model\Order\Item setBaseTaxAmount(float $value)
 * @method \Magento\Sales\Model\Order\Item setTaxInvoiced(float $value)
 * @method \Magento\Sales\Model\Order\Item setBaseTaxInvoiced(float $value)
 * @method \Magento\Sales\Model\Order\Item setDiscountPercent(float $value)
 * @method \Magento\Sales\Model\Order\Item setDiscountAmount(float $value)
 * @method \Magento\Sales\Model\Order\Item setBaseDiscountAmount(float $value)
 * @method \Magento\Sales\Model\Order\Item setDiscountInvoiced(float $value)
 * @method \Magento\Sales\Model\Order\Item setBaseDiscountInvoiced(float $value)
 * @method \Magento\Sales\Model\Order\Item setAmountRefunded(float $value)
 * @method \Magento\Sales\Model\Order\Item setBaseAmountRefunded(float $value)
 * @method \Magento\Sales\Model\Order\Item setRowTotal(float $value)
 * @method \Magento\Sales\Model\Order\Item setBaseRowTotal(float $value)
 * @method \Magento\Sales\Model\Order\Item setRowInvoiced(float $value)
 * @method \Magento\Sales\Model\Order\Item setBaseRowInvoiced(float $value)
 * @method \Magento\Sales\Model\Order\Item setRowWeight(float $value)
 * @method int getGiftMessageId()
 * @method \Magento\Sales\Model\Order\Item setGiftMessageId(int $value)
 * @method int getGiftMessageAvailable()
 * @method \Magento\Sales\Model\Order\Item setGiftMessageAvailable(int $value)
 * @method \Magento\Sales\Model\Order\Item setBaseTaxBeforeDiscount(float $value)
 * @method \Magento\Sales\Model\Order\Item setTaxBeforeDiscount(float $value)
 * @method \Magento\Sales\Model\Order\Item setExtOrderItemId(string $value)
 * @method \Magento\Sales\Model\Order\Item setWeeeTaxApplied(string $value)
 * @method \Magento\Sales\Model\Order\Item setWeeeTaxAppliedAmount(float $value)
 * @method \Magento\Sales\Model\Order\Item setWeeeTaxAppliedRowAmount(float $value)
 * @method \Magento\Sales\Model\Order\Item setBaseWeeeTaxAppliedAmount(float $value)
 * @method \Magento\Sales\Model\Order\Item setBaseWeeeTaxAppliedRowAmnt(float $value)
 * @method \Magento\Sales\Model\Order\Item setWeeeTaxDisposition(float $value)
 * @method \Magento\Sales\Model\Order\Item setWeeeTaxRowDisposition(float $value)
 * @method \Magento\Sales\Model\Order\Item setBaseWeeeTaxDisposition(float $value)
 * @method \Magento\Sales\Model\Order\Item setBaseWeeeTaxRowDisposition(float $value)
 * @method \Magento\Sales\Model\Order\Item setLockedDoInvoice(int $value)
 * @method \Magento\Sales\Model\Order\Item setLockedDoShip(int $value)
 * @method \Magento\Sales\Model\Order\Item setPriceInclTax(float $value)
 * @method \Magento\Sales\Model\Order\Item setBasePriceInclTax(float $value)
 * @method \Magento\Sales\Model\Order\Item setRowTotalInclTax(float $value)
 * @method \Magento\Sales\Model\Order\Item setBaseRowTotalInclTax(float $value)
 * @method \Magento\Sales\Model\Order\Item setHiddenTaxAmount(float $value)
 * @method \Magento\Sales\Model\Order\Item setBaseHiddenTaxAmount(float $value)
 * @method \Magento\Sales\Model\Order\Item setHiddenTaxInvoiced(float $value)
 * @method \Magento\Sales\Model\Order\Item setBaseHiddenTaxInvoiced(float $value)
 * @method \Magento\Sales\Model\Order\Item setHiddenTaxRefunded(float $value)
 * @method \Magento\Sales\Model\Order\Item setBaseHiddenTaxRefunded(float $value)
 * @method \Magento\Sales\Model\Order\Item setTaxCanceled(float $value)
 * @method \Magento\Sales\Model\Order\Item setHiddenTaxCanceled(float $value)
 * @method \Magento\Sales\Model\Order\Item setTaxRefunded(float $value)
 * @method \Magento\Sales\Model\Order\Item setBaseTaxRefunded(float $value)
 * @method \Magento\Sales\Model\Order\Item setDiscountRefunded(float $value)
 * @method \Magento\Sales\Model\Order\Item setBaseDiscountRefunded(float $value)
 */
class Item extends AbstractExtensibleModel implements OrderItemInterface
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
     */
    protected $_eventPrefix = 'sales_order_item';

    /**
     * @var string
     */
    protected $_eventObject = 'item';

    /**
     * @var array
     */
    protected static $_statuses = null;

    /**
     * Order instance
     *
     * @var \Magento\Sales\Model\Order
     */
    protected $_order = null;

    /**
     * @var \Magento\Sales\Model\Order\Item|null
     */
    protected $_parentItem = null;

    /**
     * @var array
     */
    protected $_children = [];

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $_orderFactory;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Initialize dependencies.
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\MetadataServiceInterface $metadataService
     * @param AttributeDataBuilder $customAttributeBuilder
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Framework\Model\Resource\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\Db $resourceCollection
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\MetadataServiceInterface $metadataService,
        AttributeDataBuilder $customAttributeBuilder,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Framework\Model\Resource\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\Db $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $metadataService,
            $customAttributeBuilder,
            $resource,
            $resourceCollection,
            $data
        );
        $this->_orderFactory = $orderFactory;
        $this->_storeManager = $storeManager;
        $this->productRepository = $productRepository;
    }

    /**
     * Init resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\Sales\Model\Resource\Order\Item');
    }

    /**
     * Set parent item
     *
     * @param Item $item
     * @return $this
     */
    public function setParentItem($item)
    {
        if ($item) {
            $this->_parentItem = $item;
            $item->setHasChildren(true);
            $item->addChildItem($this);
        }
        return $this;
    }

    /**
     * Get parent item
     *
     * @return OrderItemInterface|null
     */
    public function getParentItem()
    {
        return $this->_parentItem;
    }

    /**
     * Check item invoice availability
     *
     * @return bool
     */
    public function canInvoice()
    {
        return $this->getQtyToInvoice() > 0;
    }

    /**
     * Check item ship availability
     *
     * @return bool
     */
    public function canShip()
    {
        return $this->getQtyToShip() > 0;
    }

    /**
     * Check item refund availability
     *
     * @return bool
     */
    public function canRefund()
    {
        return $this->getQtyToRefund() > 0;
    }

    /**
     * Retrieve item qty available for ship
     *
     * @return float|integer
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
     */
    public function getOrder()
    {
        if (is_null($this->_order) && ($orderId = $this->getOrderId())) {
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
     */
    public function getStatus()
    {
        return $this->getStatusName($this->getStatusId());
    }

    /**
     * Retrieve status name
     *
     * @param string $statusId
     * @return string
     */
    public static function getStatusName($statusId)
    {
        if (is_null(self::$_statuses)) {
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
     */
    public function cancel()
    {
        if ($this->getStatusId() !== self::STATUS_CANCELED) {
            $this->_eventManager->dispatch('sales_order_item_cancel', ['item' => $this]);
            $this->setQtyCanceled($this->getQtyToCancel());
            $this->setTaxCanceled(
                $this->getTaxCanceled() + $this->getBaseTaxAmount() * $this->getQtyCanceled() / $this->getQtyOrdered()
            );
            $this->setHiddenTaxCanceled(
                $this->getHiddenTaxCanceled() +
                $this->getHiddenTaxAmount() * $this->getQtyCanceled() / $this->getQtyOrdered()
            );
        }
        return $this;
    }

    /**
     * Retrieve order item statuses array
     *
     * @return array
     */
    public static function getStatuses()
    {
        if (is_null(self::$_statuses)) {
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
     * @return float
     */
    public function getOriginalPrice()
    {
        $price = $this->getData(OrderItemInterface::ORIGINAL_PRICE);
        if (is_null($price)) {
            return $this->getPrice();
        }
        return $price;
    }

    /**
     * Set product options
     *
     * @param array $options
     * @return $this
     */
    public function setProductOptions(array $options)
    {
        $this->setData('product_options', $options);
        return $this;
    }

    /**
     * Get product options array
     *
     * @return array
     */
    public function getProductOptions()
    {
        $data = $this->_getData(OrderItemInterface::PRODUCT_OPTIONS);
        return is_string($data) ? unserialize($data) : $data;
    }

    /**
     * Get product options array by code.
     * If code is null return all options
     *
     * @param string $code
     * @return array
     */
    public function getProductOptionByCode($code = null)
    {
        $options = $this->getProductOptions();
        if (is_null($code)) {
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
     * @return \Magento\Framework\Object
     */
    public function getBuyRequest()
    {
        $option = $this->getProductOptionByCode('info_buyRequest');
        if (!$option) {
            $option = [];
        }
        $buyRequest = new \Magento\Framework\Object($option);
        $buyRequest->setQty($this->getQtyOrdered() * 1);
        return $buyRequest;
    }

    /**
     * Retrieve product
     *
     * @return \Magento\Catalog\Model\Product|null
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
     */
    public function getStore()
    {
        $storeId = $this->getStoreId();
        if ($storeId) {
            return $this->_storeManager->getStore($storeId);
        }
        return $this->_storeManager->getStore();
    }

    /**
     * Returns additional_data
     *
     * @return string
     */
    public function getAdditionalData()
    {
        return $this->getData(OrderItemInterface::ADDITIONAL_DATA);
    }

    /**
     * Returns amount_refunded
     *
     * @return float
     */
    public function getAmountRefunded()
    {
        return $this->getData(OrderItemInterface::AMOUNT_REFUNDED);
    }

    /**
     * Returns applied_rule_ids
     *
     * @return string
     */
    public function getAppliedRuleIds()
    {
        return $this->getData(OrderItemInterface::APPLIED_RULE_IDS);
    }

    /**
     * Returns base_amount_refunded
     *
     * @return float
     */
    public function getBaseAmountRefunded()
    {
        return $this->getData(OrderItemInterface::BASE_AMOUNT_REFUNDED);
    }

    /**
     * Returns base_cost
     *
     * @return float
     */
    public function getBaseCost()
    {
        return $this->getData(OrderItemInterface::BASE_COST);
    }

    /**
     * Returns base_discount_amount
     *
     * @return float
     */
    public function getBaseDiscountAmount()
    {
        return $this->getData(OrderItemInterface::BASE_DISCOUNT_AMOUNT);
    }

    /**
     * Returns base_discount_invoiced
     *
     * @return float
     */
    public function getBaseDiscountInvoiced()
    {
        return $this->getData(OrderItemInterface::BASE_DISCOUNT_INVOICED);
    }

    /**
     * Returns base_discount_refunded
     *
     * @return float
     */
    public function getBaseDiscountRefunded()
    {
        return $this->getData(OrderItemInterface::BASE_DISCOUNT_REFUNDED);
    }

    /**
     * Returns base_hidden_tax_amount
     *
     * @return float
     */
    public function getBaseHiddenTaxAmount()
    {
        return $this->getData(OrderItemInterface::BASE_HIDDEN_TAX_AMOUNT);
    }

    /**
     * Returns base_hidden_tax_invoiced
     *
     * @return float
     */
    public function getBaseHiddenTaxInvoiced()
    {
        return $this->getData(OrderItemInterface::BASE_HIDDEN_TAX_INVOICED);
    }

    /**
     * Returns base_hidden_tax_refunded
     *
     * @return float
     */
    public function getBaseHiddenTaxRefunded()
    {
        return $this->getData(OrderItemInterface::BASE_HIDDEN_TAX_REFUNDED);
    }

    /**
     * Returns base_original_price
     *
     * @return float
     */
    public function getBaseOriginalPrice()
    {
        return $this->getData(OrderItemInterface::BASE_ORIGINAL_PRICE);
    }

    /**
     * Returns base_price
     *
     * @return float
     */
    public function getBasePrice()
    {
        return $this->getData(OrderItemInterface::BASE_PRICE);
    }

    /**
     * Returns base_price_incl_tax
     *
     * @return float
     */
    public function getBasePriceInclTax()
    {
        return $this->getData(OrderItemInterface::BASE_PRICE_INCL_TAX);
    }

    /**
     * Returns base_row_invoiced
     *
     * @return float
     */
    public function getBaseRowInvoiced()
    {
        return $this->getData(OrderItemInterface::BASE_ROW_INVOICED);
    }

    /**
     * Returns base_row_total
     *
     * @return float
     */
    public function getBaseRowTotal()
    {
        return $this->getData(OrderItemInterface::BASE_ROW_TOTAL);
    }

    /**
     * Returns base_row_total_incl_tax
     *
     * @return float
     */
    public function getBaseRowTotalInclTax()
    {
        return $this->getData(OrderItemInterface::BASE_ROW_TOTAL_INCL_TAX);
    }

    /**
     * Returns base_tax_amount
     *
     * @return float
     */
    public function getBaseTaxAmount()
    {
        return $this->getData(OrderItemInterface::BASE_TAX_AMOUNT);
    }

    /**
     * Returns base_tax_before_discount
     *
     * @return float
     */
    public function getBaseTaxBeforeDiscount()
    {
        return $this->getData(OrderItemInterface::BASE_TAX_BEFORE_DISCOUNT);
    }

    /**
     * Returns base_tax_invoiced
     *
     * @return float
     */
    public function getBaseTaxInvoiced()
    {
        return $this->getData(OrderItemInterface::BASE_TAX_INVOICED);
    }

    /**
     * Returns base_tax_refunded
     *
     * @return float
     */
    public function getBaseTaxRefunded()
    {
        return $this->getData(OrderItemInterface::BASE_TAX_REFUNDED);
    }

    /**
     * Returns base_weee_tax_applied_amount
     *
     * @return float
     */
    public function getBaseWeeeTaxAppliedAmount()
    {
        return $this->getData(OrderItemInterface::BASE_WEEE_TAX_APPLIED_AMOUNT);
    }

    /**
     * Returns base_weee_tax_applied_row_amnt
     *
     * @return float
     */
    public function getBaseWeeeTaxAppliedRowAmnt()
    {
        return $this->getData(OrderItemInterface::BASE_WEEE_TAX_APPLIED_ROW_AMNT);
    }

    /**
     * Returns base_weee_tax_disposition
     *
     * @return float
     */
    public function getBaseWeeeTaxDisposition()
    {
        return $this->getData(OrderItemInterface::BASE_WEEE_TAX_DISPOSITION);
    }

    /**
     * Returns base_weee_tax_row_disposition
     *
     * @return float
     */
    public function getBaseWeeeTaxRowDisposition()
    {
        return $this->getData(OrderItemInterface::BASE_WEEE_TAX_ROW_DISPOSITION);
    }

    /**
     * Returns created_at
     *
     * @return string
     */
    public function getCreatedAt()
    {
        return $this->getData(OrderItemInterface::CREATED_AT);
    }

    /**
     * Returns description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->getData(OrderItemInterface::DESCRIPTION);
    }

    /**
     * Returns discount_amount
     *
     * @return float
     */
    public function getDiscountAmount()
    {
        return $this->getData(OrderItemInterface::DISCOUNT_AMOUNT);
    }

    /**
     * Returns discount_invoiced
     *
     * @return float
     */
    public function getDiscountInvoiced()
    {
        return $this->getData(OrderItemInterface::DISCOUNT_INVOICED);
    }

    /**
     * Returns discount_percent
     *
     * @return float
     */
    public function getDiscountPercent()
    {
        return $this->getData(OrderItemInterface::DISCOUNT_PERCENT);
    }

    /**
     * Returns discount_refunded
     *
     * @return float
     */
    public function getDiscountRefunded()
    {
        return $this->getData(OrderItemInterface::DISCOUNT_REFUNDED);
    }

    /**
     * Returns event_id
     *
     * @return int
     */
    public function getEventId()
    {
        return $this->getData(OrderItemInterface::EVENT_ID);
    }

    /**
     * Returns ext_order_item_id
     *
     * @return string
     */
    public function getExtOrderItemId()
    {
        return $this->getData(OrderItemInterface::EXT_ORDER_ITEM_ID);
    }

    /**
     * Returns free_shipping
     *
     * @return int
     */
    public function getFreeShipping()
    {
        return $this->getData(OrderItemInterface::FREE_SHIPPING);
    }

    /**
     * Returns gw_base_price
     *
     * @return float
     */
    public function getGwBasePrice()
    {
        return $this->getData(OrderItemInterface::GW_BASE_PRICE);
    }

    /**
     * Returns gw_base_price_invoiced
     *
     * @return float
     */
    public function getGwBasePriceInvoiced()
    {
        return $this->getData(OrderItemInterface::GW_BASE_PRICE_INVOICED);
    }

    /**
     * Returns gw_base_price_refunded
     *
     * @return float
     */
    public function getGwBasePriceRefunded()
    {
        return $this->getData(OrderItemInterface::GW_BASE_PRICE_REFUNDED);
    }

    /**
     * Returns gw_base_tax_amount
     *
     * @return float
     */
    public function getGwBaseTaxAmount()
    {
        return $this->getData(OrderItemInterface::GW_BASE_TAX_AMOUNT);
    }

    /**
     * Returns gw_base_tax_amount_invoiced
     *
     * @return float
     */
    public function getGwBaseTaxAmountInvoiced()
    {
        return $this->getData(OrderItemInterface::GW_BASE_TAX_AMOUNT_INVOICED);
    }

    /**
     * Returns gw_base_tax_amount_refunded
     *
     * @return float
     */
    public function getGwBaseTaxAmountRefunded()
    {
        return $this->getData(OrderItemInterface::GW_BASE_TAX_AMOUNT_REFUNDED);
    }

    /**
     * Returns gw_id
     *
     * @return int
     */
    public function getGwId()
    {
        return $this->getData(OrderItemInterface::GW_ID);
    }

    /**
     * Returns gw_price
     *
     * @return float
     */
    public function getGwPrice()
    {
        return $this->getData(OrderItemInterface::GW_PRICE);
    }

    /**
     * Returns gw_price_invoiced
     *
     * @return float
     */
    public function getGwPriceInvoiced()
    {
        return $this->getData(OrderItemInterface::GW_PRICE_INVOICED);
    }

    /**
     * Returns gw_price_refunded
     *
     * @return float
     */
    public function getGwPriceRefunded()
    {
        return $this->getData(OrderItemInterface::GW_PRICE_REFUNDED);
    }

    /**
     * Returns gw_tax_amount
     *
     * @return float
     */
    public function getGwTaxAmount()
    {
        return $this->getData(OrderItemInterface::GW_TAX_AMOUNT);
    }

    /**
     * Returns gw_tax_amount_invoiced
     *
     * @return float
     */
    public function getGwTaxAmountInvoiced()
    {
        return $this->getData(OrderItemInterface::GW_TAX_AMOUNT_INVOICED);
    }

    /**
     * Returns gw_tax_amount_refunded
     *
     * @return float
     */
    public function getGwTaxAmountRefunded()
    {
        return $this->getData(OrderItemInterface::GW_TAX_AMOUNT_REFUNDED);
    }

    /**
     * Returns hidden_tax_amount
     *
     * @return float
     */
    public function getHiddenTaxAmount()
    {
        return $this->getData(OrderItemInterface::HIDDEN_TAX_AMOUNT);
    }

    /**
     * Returns hidden_tax_canceled
     *
     * @return float
     */
    public function getHiddenTaxCanceled()
    {
        return $this->getData(OrderItemInterface::HIDDEN_TAX_CANCELED);
    }

    /**
     * Returns hidden_tax_invoiced
     *
     * @return float
     */
    public function getHiddenTaxInvoiced()
    {
        return $this->getData(OrderItemInterface::HIDDEN_TAX_INVOICED);
    }

    /**
     * Returns hidden_tax_refunded
     *
     * @return float
     */
    public function getHiddenTaxRefunded()
    {
        return $this->getData(OrderItemInterface::HIDDEN_TAX_REFUNDED);
    }

    /**
     * Returns is_qty_decimal
     *
     * @return int
     */
    public function getIsQtyDecimal()
    {
        return $this->getData(OrderItemInterface::IS_QTY_DECIMAL);
    }

    /**
     * Returns is_virtual
     *
     * @return int
     */
    public function getIsVirtual()
    {
        return $this->getData(OrderItemInterface::IS_VIRTUAL);
    }

    /**
     * Returns item_id
     *
     * @return int
     */
    public function getItemId()
    {
        return $this->getData(OrderItemInterface::ITEM_ID);
    }

    /**
     * Returns locked_do_invoice
     *
     * @return int
     */
    public function getLockedDoInvoice()
    {
        return $this->getData(OrderItemInterface::LOCKED_DO_INVOICE);
    }

    /**
     * Returns locked_do_ship
     *
     * @return int
     */
    public function getLockedDoShip()
    {
        return $this->getData(OrderItemInterface::LOCKED_DO_SHIP);
    }

    /**
     * Returns name
     *
     * @return string
     */
    public function getName()
    {
        return $this->getData(OrderItemInterface::NAME);
    }

    /**
     * Returns no_discount
     *
     * @return int
     */
    public function getNoDiscount()
    {
        return $this->getData(OrderItemInterface::NO_DISCOUNT);
    }

    /**
     * Returns order_id
     *
     * @return int
     */
    public function getOrderId()
    {
        return $this->getData(OrderItemInterface::ORDER_ID);
    }

    /**
     * Returns parent_item_id
     *
     * @return int
     */
    public function getParentItemId()
    {
        return $this->getData(OrderItemInterface::PARENT_ITEM_ID);
    }

    /**
     * Returns price
     *
     * @return float
     */
    public function getPrice()
    {
        return $this->getData(OrderItemInterface::PRICE);
    }

    /**
     * Returns price_incl_tax
     *
     * @return float
     */
    public function getPriceInclTax()
    {
        return $this->getData(OrderItemInterface::PRICE_INCL_TAX);
    }

    /**
     * Returns product_id
     *
     * @return int
     */
    public function getProductId()
    {
        return $this->getData(OrderItemInterface::PRODUCT_ID);
    }

    /**
     * Returns product_type
     *
     * @return string
     */
    public function getProductType()
    {
        return $this->getData(OrderItemInterface::PRODUCT_TYPE);
    }

    /**
     * Returns qty_backordered
     *
     * @return float
     */
    public function getQtyBackordered()
    {
        return $this->getData(OrderItemInterface::QTY_BACKORDERED);
    }

    /**
     * Returns qty_canceled
     *
     * @return float
     */
    public function getQtyCanceled()
    {
        return $this->getData(OrderItemInterface::QTY_CANCELED);
    }

    /**
     * Returns qty_invoiced
     *
     * @return float
     */
    public function getQtyInvoiced()
    {
        return $this->getData(OrderItemInterface::QTY_INVOICED);
    }

    /**
     * Returns qty_ordered
     *
     * @return float
     */
    public function getQtyOrdered()
    {
        return $this->getData(OrderItemInterface::QTY_ORDERED);
    }

    /**
     * Returns qty_refunded
     *
     * @return float
     */
    public function getQtyRefunded()
    {
        return $this->getData(OrderItemInterface::QTY_REFUNDED);
    }

    /**
     * Returns qty_returned
     *
     * @return float
     */
    public function getQtyReturned()
    {
        return $this->getData(OrderItemInterface::QTY_RETURNED);
    }

    /**
     * Returns qty_shipped
     *
     * @return float
     */
    public function getQtyShipped()
    {
        return $this->getData(OrderItemInterface::QTY_SHIPPED);
    }

    /**
     * Returns quote_item_id
     *
     * @return int
     */
    public function getQuoteItemId()
    {
        return $this->getData(OrderItemInterface::QUOTE_ITEM_ID);
    }

    /**
     * Returns row_invoiced
     *
     * @return float
     */
    public function getRowInvoiced()
    {
        return $this->getData(OrderItemInterface::ROW_INVOICED);
    }

    /**
     * Returns row_total
     *
     * @return float
     */
    public function getRowTotal()
    {
        return $this->getData(OrderItemInterface::ROW_TOTAL);
    }

    /**
     * Returns row_total_incl_tax
     *
     * @return float
     */
    public function getRowTotalInclTax()
    {
        return $this->getData(OrderItemInterface::ROW_TOTAL_INCL_TAX);
    }

    /**
     * Returns row_weight
     *
     * @return float
     */
    public function getRowWeight()
    {
        return $this->getData(OrderItemInterface::ROW_WEIGHT);
    }

    /**
     * Returns sku
     *
     * @return string
     */
    public function getSku()
    {
        return $this->getData(OrderItemInterface::SKU);
    }

    /**
     * Returns store_id
     *
     * @return int
     */
    public function getStoreId()
    {
        return $this->getData(OrderItemInterface::STORE_ID);
    }

    /**
     * Returns tax_amount
     *
     * @return float
     */
    public function getTaxAmount()
    {
        return $this->getData(OrderItemInterface::TAX_AMOUNT);
    }

    /**
     * Returns tax_before_discount
     *
     * @return float
     */
    public function getTaxBeforeDiscount()
    {
        return $this->getData(OrderItemInterface::TAX_BEFORE_DISCOUNT);
    }

    /**
     * Returns tax_canceled
     *
     * @return float
     */
    public function getTaxCanceled()
    {
        return $this->getData(OrderItemInterface::TAX_CANCELED);
    }

    /**
     * Returns tax_invoiced
     *
     * @return float
     */
    public function getTaxInvoiced()
    {
        return $this->getData(OrderItemInterface::TAX_INVOICED);
    }

    /**
     * Returns tax_percent
     *
     * @return float
     */
    public function getTaxPercent()
    {
        return $this->getData(OrderItemInterface::TAX_PERCENT);
    }

    /**
     * Returns tax_refunded
     *
     * @return float
     */
    public function getTaxRefunded()
    {
        return $this->getData(OrderItemInterface::TAX_REFUNDED);
    }

    /**
     * Returns updated_at
     *
     * @return string
     */
    public function getUpdatedAt()
    {
        return $this->getData(OrderItemInterface::UPDATED_AT);
    }

    /**
     * Returns weee_tax_applied
     *
     * @return string
     */
    public function getWeeeTaxApplied()
    {
        return $this->getData(OrderItemInterface::WEEE_TAX_APPLIED);
    }

    /**
     * Returns weee_tax_applied_amount
     *
     * @return float
     */
    public function getWeeeTaxAppliedAmount()
    {
        return $this->getData(OrderItemInterface::WEEE_TAX_APPLIED_AMOUNT);
    }

    /**
     * Returns weee_tax_applied_row_amount
     *
     * @return float
     */
    public function getWeeeTaxAppliedRowAmount()
    {
        return $this->getData(OrderItemInterface::WEEE_TAX_APPLIED_ROW_AMOUNT);
    }

    /**
     * Returns weee_tax_disposition
     *
     * @return float
     */
    public function getWeeeTaxDisposition()
    {
        return $this->getData(OrderItemInterface::WEEE_TAX_DISPOSITION);
    }

    /**
     * Returns weee_tax_row_disposition
     *
     * @return float
     */
    public function getWeeeTaxRowDisposition()
    {
        return $this->getData(OrderItemInterface::WEEE_TAX_ROW_DISPOSITION);
    }

    /**
     * Returns weight
     *
     * @return float
     */
    public function getWeight()
    {
        return $this->getData(OrderItemInterface::WEIGHT);
    }
}
