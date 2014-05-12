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

/**
 * Order Item Model
 *
 * @method \Magento\Sales\Model\Resource\Order\Item _getResource()
 * @method \Magento\Sales\Model\Resource\Order\Item getResource()
 * @method int getOrderId()
 * @method \Magento\Sales\Model\Order\Item setOrderId(int $value)
 * @method int getParentItemId()
 * @method \Magento\Sales\Model\Order\Item setParentItemId(int $value)
 * @method int getQuoteItemId()
 * @method \Magento\Sales\Model\Order\Item setQuoteItemId(int $value)
 * @method int getStoreId()
 * @method \Magento\Sales\Model\Order\Item setStoreId(int $value)
 * @method string getCreatedAt()
 * @method \Magento\Sales\Model\Order\Item setCreatedAt(string $value)
 * @method string getUpdatedAt()
 * @method \Magento\Sales\Model\Order\Item setUpdatedAt(string $value)
 * @method int getProductId()
 * @method \Magento\Sales\Model\Order\Item setProductId(int $value)
 * @method string getProductType()
 * @method \Magento\Sales\Model\Order\Item setProductType(string $value)
 * @method float getWeight()
 * @method \Magento\Sales\Model\Order\Item setWeight(float $value)
 * @method int getIsVirtual()
 * @method \Magento\Sales\Model\Order\Item setIsVirtual(int $value)
 * @method string getSku()
 * @method \Magento\Sales\Model\Order\Item setSku(string $value)
 * @method string getName()
 * @method \Magento\Sales\Model\Order\Item setName(string $value)
 * @method string getDescription()
 * @method \Magento\Sales\Model\Order\Item setDescription(string $value)
 * @method string getAppliedRuleIds()
 * @method \Magento\Sales\Model\Order\Item setAppliedRuleIds(string $value)
 * @method string getAdditionalData()
 * @method \Magento\Sales\Model\Order\Item setAdditionalData(string $value)
 * @method int getFreeShipping()
 * @method \Magento\Sales\Model\Order\Item setFreeShipping(int $value)
 * @method int getIsQtyDecimal()
 * @method \Magento\Sales\Model\Order\Item setIsQtyDecimal(int $value)
 * @method int getNoDiscount()
 * @method \Magento\Sales\Model\Order\Item setNoDiscount(int $value)
 * @method float getQtyBackordered()
 * @method \Magento\Sales\Model\Order\Item setQtyBackordered(float $value)
 * @method float getQtyCanceled()
 * @method \Magento\Sales\Model\Order\Item setQtyCanceled(float $value)
 * @method float getQtyInvoiced()
 * @method \Magento\Sales\Model\Order\Item setQtyInvoiced(float $value)
 * @method float getQtyOrdered()
 * @method \Magento\Sales\Model\Order\Item setQtyOrdered(float $value)
 * @method float getQtyRefunded()
 * @method \Magento\Sales\Model\Order\Item setQtyRefunded(float $value)
 * @method float getQtyShipped()
 * @method \Magento\Sales\Model\Order\Item setQtyShipped(float $value)
 * @method float getBaseCost()
 * @method \Magento\Sales\Model\Order\Item setBaseCost(float $value)
 * @method float getPrice()
 * @method \Magento\Sales\Model\Order\Item setPrice(float $value)
 * @method float getBasePrice()
 * @method \Magento\Sales\Model\Order\Item setBasePrice(float $value)
 * @method \Magento\Sales\Model\Order\Item setOriginalPrice(float $value)
 * @method float getBaseOriginalPrice()
 * @method \Magento\Sales\Model\Order\Item setBaseOriginalPrice(float $value)
 * @method float getTaxPercent()
 * @method \Magento\Sales\Model\Order\Item setTaxPercent(float $value)
 * @method float getTaxAmount()
 * @method \Magento\Sales\Model\Order\Item setTaxAmount(float $value)
 * @method float getBaseTaxAmount()
 * @method \Magento\Sales\Model\Order\Item setBaseTaxAmount(float $value)
 * @method float getTaxInvoiced()
 * @method \Magento\Sales\Model\Order\Item setTaxInvoiced(float $value)
 * @method float getBaseTaxInvoiced()
 * @method \Magento\Sales\Model\Order\Item setBaseTaxInvoiced(float $value)
 * @method float getDiscountPercent()
 * @method \Magento\Sales\Model\Order\Item setDiscountPercent(float $value)
 * @method float getDiscountAmount()
 * @method \Magento\Sales\Model\Order\Item setDiscountAmount(float $value)
 * @method float getBaseDiscountAmount()
 * @method \Magento\Sales\Model\Order\Item setBaseDiscountAmount(float $value)
 * @method float getDiscountInvoiced()
 * @method \Magento\Sales\Model\Order\Item setDiscountInvoiced(float $value)
 * @method float getBaseDiscountInvoiced()
 * @method \Magento\Sales\Model\Order\Item setBaseDiscountInvoiced(float $value)
 * @method float getAmountRefunded()
 * @method \Magento\Sales\Model\Order\Item setAmountRefunded(float $value)
 * @method float getBaseAmountRefunded()
 * @method \Magento\Sales\Model\Order\Item setBaseAmountRefunded(float $value)
 * @method float getRowTotal()
 * @method \Magento\Sales\Model\Order\Item setRowTotal(float $value)
 * @method float getBaseRowTotal()
 * @method \Magento\Sales\Model\Order\Item setBaseRowTotal(float $value)
 * @method float getRowInvoiced()
 * @method \Magento\Sales\Model\Order\Item setRowInvoiced(float $value)
 * @method float getBaseRowInvoiced()
 * @method \Magento\Sales\Model\Order\Item setBaseRowInvoiced(float $value)
 * @method float getRowWeight()
 * @method \Magento\Sales\Model\Order\Item setRowWeight(float $value)
 * @method int getGiftMessageId()
 * @method \Magento\Sales\Model\Order\Item setGiftMessageId(int $value)
 * @method int getGiftMessageAvailable()
 * @method \Magento\Sales\Model\Order\Item setGiftMessageAvailable(int $value)
 * @method float getBaseTaxBeforeDiscount()
 * @method \Magento\Sales\Model\Order\Item setBaseTaxBeforeDiscount(float $value)
 * @method float getTaxBeforeDiscount()
 * @method \Magento\Sales\Model\Order\Item setTaxBeforeDiscount(float $value)
 * @method string getExtOrderItemId()
 * @method \Magento\Sales\Model\Order\Item setExtOrderItemId(string $value)
 * @method string getWeeeTaxApplied()
 * @method \Magento\Sales\Model\Order\Item setWeeeTaxApplied(string $value)
 * @method float getWeeeTaxAppliedAmount()
 * @method \Magento\Sales\Model\Order\Item setWeeeTaxAppliedAmount(float $value)
 * @method float getWeeeTaxAppliedRowAmount()
 * @method \Magento\Sales\Model\Order\Item setWeeeTaxAppliedRowAmount(float $value)
 * @method float getBaseWeeeTaxAppliedAmount()
 * @method \Magento\Sales\Model\Order\Item setBaseWeeeTaxAppliedAmount(float $value)
 * @method float getBaseWeeeTaxAppliedRowAmnt()
 * @method \Magento\Sales\Model\Order\Item setBaseWeeeTaxAppliedRowAmnt(float $value)
 * @method float getWeeeTaxDisposition()
 * @method \Magento\Sales\Model\Order\Item setWeeeTaxDisposition(float $value)
 * @method float getWeeeTaxRowDisposition()
 * @method \Magento\Sales\Model\Order\Item setWeeeTaxRowDisposition(float $value)
 * @method float getBaseWeeeTaxDisposition()
 * @method \Magento\Sales\Model\Order\Item setBaseWeeeTaxDisposition(float $value)
 * @method float getBaseWeeeTaxRowDisposition()
 * @method \Magento\Sales\Model\Order\Item setBaseWeeeTaxRowDisposition(float $value)
 * @method int getLockedDoInvoice()
 * @method \Magento\Sales\Model\Order\Item setLockedDoInvoice(int $value)
 * @method int getLockedDoShip()
 * @method \Magento\Sales\Model\Order\Item setLockedDoShip(int $value)
 * @method float getPriceInclTax()
 * @method \Magento\Sales\Model\Order\Item setPriceInclTax(float $value)
 * @method float getBasePriceInclTax()
 * @method \Magento\Sales\Model\Order\Item setBasePriceInclTax(float $value)
 * @method float getRowTotalInclTax()
 * @method \Magento\Sales\Model\Order\Item setRowTotalInclTax(float $value)
 * @method float getBaseRowTotalInclTax()
 * @method \Magento\Sales\Model\Order\Item setBaseRowTotalInclTax(float $value)
 * @method float getHiddenTaxAmount()
 * @method \Magento\Sales\Model\Order\Item setHiddenTaxAmount(float $value)
 * @method float getBaseHiddenTaxAmount()
 * @method \Magento\Sales\Model\Order\Item setBaseHiddenTaxAmount(float $value)
 * @method float getHiddenTaxInvoiced()
 * @method \Magento\Sales\Model\Order\Item setHiddenTaxInvoiced(float $value)
 * @method float getBaseHiddenTaxInvoiced()
 * @method \Magento\Sales\Model\Order\Item setBaseHiddenTaxInvoiced(float $value)
 * @method float getHiddenTaxRefunded()
 * @method \Magento\Sales\Model\Order\Item setHiddenTaxRefunded(float $value)
 * @method float getBaseHiddenTaxRefunded()
 * @method \Magento\Sales\Model\Order\Item setBaseHiddenTaxRefunded(float $value)
 * @method int getIsNominal()
 * @method \Magento\Sales\Model\Order\Item setIsNominal(int $value)
 * @method float getTaxCanceled()
 * @method \Magento\Sales\Model\Order\Item setTaxCanceled(float $value)
 * @method float getHiddenTaxCanceled()
 * @method \Magento\Sales\Model\Order\Item setHiddenTaxCanceled(float $value)
 * @method float getTaxRefunded()
 * @method \Magento\Sales\Model\Order\Item setTaxRefunded(float $value)
 * @method float getBaseTaxRefunded()
 * @method \Magento\Sales\Model\Order\Item setBaseTaxRefunded(float $value)
 * @method float getDiscountRefunded()
 * @method \Magento\Sales\Model\Order\Item setDiscountRefunded(float $value)
 * @method float getBaseDiscountRefunded()
 * @method \Magento\Sales\Model\Order\Item setBaseDiscountRefunded(float $value)
 */
class Item extends \Magento\Framework\Model\AbstractModel
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
    protected $_children = array();

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $_orderFactory;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Framework\Model\Resource\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Framework\Model\Resource\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\Db $resourceCollection = null,
        array $data = array()
    ) {
        $this->_orderFactory = $orderFactory;
        $this->_productFactory = $productFactory;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
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
     * Prepare data before save
     *
     * @return $this
     */
    protected function _beforeSave()
    {
        parent::_beforeSave();
        if (!$this->getOrderId() && $this->getOrder()) {
            $this->setOrderId($this->getOrder()->getId());
        }
        if ($this->getParentItem()) {
            $this->setParentItemId($this->getParentItem()->getId());
        }
        return $this;
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
     * @return \Magento\Sales\Model\Order\Item|null
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
            $this->_eventManager->dispatch('sales_order_item_cancel', array('item' => $this));
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
            self::$_statuses = array(
                self::STATUS_PENDING => __('Ordered'),
                self::STATUS_SHIPPED => __('Shipped'),
                self::STATUS_INVOICED => __('Invoiced'),
                self::STATUS_BACKORDERED => __('Backordered'),
                self::STATUS_RETURNED => __('Returned'),
                self::STATUS_REFUNDED => __('Refunded'),
                self::STATUS_CANCELED => __('Canceled'),
                self::STATUS_PARTIAL => __('Partial'),
                self::STATUS_MIXED => __('Mixed')
            );
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
        $price = $this->getData('original_price');
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
        $this->setData('product_options', serialize($options));
        return $this;
    }

    /**
     * Get product options array
     *
     * @return array
     */
    public function getProductOptions()
    {
        $options = $this->_getData('product_options');
        if ($options) {
            return unserialize($options);
        }
        return array();
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
            $option = array();
        }
        $buyRequest = new \Magento\Framework\Object($option);
        $buyRequest->setQty($this->getQtyOrdered() * 1);
        return $buyRequest;
    }

    /**
     * Retrieve product
     *
     * @return \Magento\Catalog\Model\Product
     */
    public function getProduct()
    {
        if (!$this->getData('product')) {
            $product = $this->_productFactory->create()->load($this->getProductId());
            $this->setProduct($product);
        }
        return $this->getData('product');
    }
}
