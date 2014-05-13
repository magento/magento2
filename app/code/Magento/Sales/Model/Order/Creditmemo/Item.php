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
namespace Magento\Sales\Model\Order\Creditmemo;

/**
 * @method \Magento\Sales\Model\Resource\Order\Creditmemo\Item _getResource()
 * @method \Magento\Sales\Model\Resource\Order\Creditmemo\Item getResource()
 * @method int getParentId()
 * @method \Magento\Sales\Model\Order\Creditmemo\Item setParentId(int $value)
 * @method float getWeeeTaxAppliedRowAmount()
 * @method \Magento\Sales\Model\Order\Creditmemo\Item setWeeeTaxAppliedRowAmount(float $value)
 * @method float getBasePrice()
 * @method \Magento\Sales\Model\Order\Creditmemo\Item setBasePrice(float $value)
 * @method float getBaseWeeeTaxRowDisposition()
 * @method \Magento\Sales\Model\Order\Creditmemo\Item setBaseWeeeTaxRowDisposition(float $value)
 * @method float getTaxAmount()
 * @method \Magento\Sales\Model\Order\Creditmemo\Item setTaxAmount(float $value)
 * @method float getBaseWeeeTaxAppliedAmount()
 * @method \Magento\Sales\Model\Order\Creditmemo\Item setBaseWeeeTaxAppliedAmount(float $value)
 * @method float getWeeeTaxRowDisposition()
 * @method \Magento\Sales\Model\Order\Creditmemo\Item setWeeeTaxRowDisposition(float $value)
 * @method float getBaseRowTotal()
 * @method \Magento\Sales\Model\Order\Creditmemo\Item setBaseRowTotal(float $value)
 * @method float getDiscountAmount()
 * @method \Magento\Sales\Model\Order\Creditmemo\Item setDiscountAmount(float $value)
 * @method float getRowTotal()
 * @method \Magento\Sales\Model\Order\Creditmemo\Item setRowTotal(float $value)
 * @method float getWeeeTaxAppliedAmount()
 * @method \Magento\Sales\Model\Order\Creditmemo\Item setWeeeTaxAppliedAmount(float $value)
 * @method float getBaseDiscountAmount()
 * @method \Magento\Sales\Model\Order\Creditmemo\Item setBaseDiscountAmount(float $value)
 * @method float getBaseWeeeTaxDisposition()
 * @method \Magento\Sales\Model\Order\Creditmemo\Item setBaseWeeeTaxDisposition(float $value)
 * @method float getPriceInclTax()
 * @method \Magento\Sales\Model\Order\Creditmemo\Item setPriceInclTax(float $value)
 * @method float getBaseTaxAmount()
 * @method \Magento\Sales\Model\Order\Creditmemo\Item setBaseTaxAmount(float $value)
 * @method float getWeeeTaxDisposition()
 * @method \Magento\Sales\Model\Order\Creditmemo\Item setWeeeTaxDisposition(float $value)
 * @method float getBasePriceInclTax()
 * @method \Magento\Sales\Model\Order\Creditmemo\Item setBasePriceInclTax(float $value)
 * @method float getQty()
 * @method float getBaseCost()
 * @method \Magento\Sales\Model\Order\Creditmemo\Item setBaseCost(float $value)
 * @method float getBaseWeeeTaxAppliedRowAmnt()
 * @method \Magento\Sales\Model\Order\Creditmemo\Item setBaseWeeeTaxAppliedRowAmnt(float $value)
 * @method float getPrice()
 * @method \Magento\Sales\Model\Order\Creditmemo\Item setPrice(float $value)
 * @method float getBaseRowTotalInclTax()
 * @method \Magento\Sales\Model\Order\Creditmemo\Item setBaseRowTotalInclTax(float $value)
 * @method float getRowTotalInclTax()
 * @method \Magento\Sales\Model\Order\Creditmemo\Item setRowTotalInclTax(float $value)
 * @method int getProductId()
 * @method \Magento\Sales\Model\Order\Creditmemo\Item setProductId(int $value)
 * @method int getOrderItemId()
 * @method \Magento\Sales\Model\Order\Creditmemo\Item setOrderItemId(int $value)
 * @method string getAdditionalData()
 * @method \Magento\Sales\Model\Order\Creditmemo\Item setAdditionalData(string $value)
 * @method string getDescription()
 * @method \Magento\Sales\Model\Order\Creditmemo\Item setDescription(string $value)
 * @method string getWeeeTaxApplied()
 * @method \Magento\Sales\Model\Order\Creditmemo\Item setWeeeTaxApplied(string $value)
 * @method string getSku()
 * @method \Magento\Sales\Model\Order\Creditmemo\Item setSku(string $value)
 * @method string getName()
 * @method \Magento\Sales\Model\Order\Creditmemo\Item setName(string $value)
 * @method float getHiddenTaxAmount()
 * @method \Magento\Sales\Model\Order\Creditmemo\Item setHiddenTaxAmount(float $value)
 * @method float getBaseHiddenTaxAmount()
 * @method \Magento\Sales\Model\Order\Creditmemo\Item setBaseHiddenTaxAmount(float $value)
 */
class Item extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'sales_creditmemo_item';

    /**
     * @var string
     */
    protected $_eventObject = 'creditmemo_item';

    /**
     * @var \Magento\Sales\Model\Order\Creditmemo|null
     */
    protected $_creditmemo = null;

    /**
     * @var \Magento\Sales\Model\Order\Item|null
     */
    protected $_orderItem = null;

    /**
     * @var \Magento\Sales\Model\Order\ItemFactory
     */
    protected $_orderItemFactory;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Sales\Model\Order\ItemFactory $orderItemFactory
     * @param \Magento\Framework\Model\Resource\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Sales\Model\Order\ItemFactory $orderItemFactory,
        \Magento\Framework\Model\Resource\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\Db $resourceCollection = null,
        array $data = array()
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->_orderItemFactory = $orderItemFactory;
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\Sales\Model\Resource\Order\Creditmemo\Item');
    }

    /**
     * Declare creditmemo instance
     *
     * @param \Magento\Sales\Model\Order\Creditmemo $creditmemo
     * @return $this
     */
    public function setCreditmemo(\Magento\Sales\Model\Order\Creditmemo $creditmemo)
    {
        $this->_creditmemo = $creditmemo;
        return $this;
    }

    /**
     * Retrieve creditmemo instance
     *
     * @return \Magento\Sales\Model\Order\Creditmemo
     */
    public function getCreditmemo()
    {
        return $this->_creditmemo;
    }

    /**
     * Declare order item instance
     *
     * @param \Magento\Sales\Model\Order\Item $item
     * @return $this
     */
    public function setOrderItem(\Magento\Sales\Model\Order\Item $item)
    {
        $this->_orderItem = $item;
        $this->setOrderItemId($item->getId());
        return $this;
    }

    /**
     * Retrieve order item instance
     *
     * @return \Magento\Sales\Model\Order\Item
     */
    public function getOrderItem()
    {
        if (is_null($this->_orderItem)) {
            if ($this->getCreditmemo()) {
                $this->_orderItem = $this->getCreditmemo()->getOrder()->getItemById($this->getOrderItemId());
            } else {
                $this->_orderItem = $this->_orderItemFactory->create()->load($this->getOrderItemId());
            }
        }
        return $this->_orderItem;
    }

    /**
     * Declare qty
     *
     * @param   float $qty
     * @return $this
     * @throws \Magento\Framework\Model\Exception
     */
    public function setQty($qty)
    {
        if ($this->getOrderItem()->getIsQtyDecimal()) {
            $qty = (double)$qty;
        } else {
            $qty = (int)$qty;
        }
        $qty = $qty > 0 ? $qty : 0;
        /**
         * Check qty availability
         */
        if ($qty <= $this->getOrderItem()->getQtyToRefund() || $this->getOrderItem()->isDummy()) {
            $this->setData('qty', $qty);
        } else {
            throw new \Magento\Framework\Model\Exception(
                __('We found an invalid quantity to refund item "%1".', $this->getName())
            );
        }
        return $this;
    }

    /**
     * Applying qty to order item
     *
     * @return \Magento\Sales\Model\Order\Shipment\Item
     */
    public function register()
    {
        $orderItem = $this->getOrderItem();

        $orderItem->setQtyRefunded($orderItem->getQtyRefunded() + $this->getQty());
        $orderItem->setTaxRefunded($orderItem->getTaxRefunded() + $this->getTaxAmount());
        $orderItem->setBaseTaxRefunded($orderItem->getBaseTaxRefunded() + $this->getBaseTaxAmount());
        $orderItem->setHiddenTaxRefunded($orderItem->getHiddenTaxRefunded() + $this->getHiddenTaxAmount());
        $orderItem->setBaseHiddenTaxRefunded($orderItem->getBaseHiddenTaxRefunded() + $this->getBaseHiddenTaxAmount());
        $orderItem->setAmountRefunded($orderItem->getAmountRefunded() + $this->getRowTotal());
        $orderItem->setBaseAmountRefunded($orderItem->getBaseAmountRefunded() + $this->getBaseRowTotal());
        $orderItem->setDiscountRefunded($orderItem->getDiscountRefunded() + $this->getDiscountAmount());
        $orderItem->setBaseDiscountRefunded($orderItem->getBaseDiscountRefunded() + $this->getBaseDiscountAmount());

        return $this;
    }

    /**
     * @return $this
     */
    public function cancel()
    {
        $this->getOrderItem()->setQtyRefunded($this->getOrderItem()->getQtyRefunded() - $this->getQty());
        $this->getOrderItem()->setTaxRefunded(
            $this->getOrderItem()->getTaxRefunded() -
            $this->getOrderItem()->getBaseTaxAmount() * $this->getQty() / $this->getOrderItem()->getQtyOrdered()
        );
        $this->getOrderItem()->setHiddenTaxRefunded(
            $this->getOrderItem()->getHiddenTaxRefunded() -
            $this->getOrderItem()->getHiddenTaxAmount() * $this->getQty() / $this->getOrderItem()->getQtyOrdered()
        );
        return $this;
    }

    /**
     * Invoice item row total calculation
     *
     * @return \Magento\Sales\Model\Order\Invoice\Item
     */
    public function calcRowTotal()
    {
        $creditmemo = $this->getCreditmemo();
        $orderItem = $this->getOrderItem();
        $orderItemQtyInvoiced = $orderItem->getQtyInvoiced();

        $rowTotal = $orderItem->getRowInvoiced() - $orderItem->getAmountRefunded();
        $baseRowTotal = $orderItem->getBaseRowInvoiced() - $orderItem->getBaseAmountRefunded();
        $rowTotalInclTax = $orderItem->getRowTotalInclTax();
        $baseRowTotalInclTax = $orderItem->getBaseRowTotalInclTax();

        if (!$this->isLast() && $orderItemQtyInvoiced > 0) {
            $availableQty = $orderItemQtyInvoiced - $orderItem->getQtyRefunded();
            $rowTotal = $creditmemo->roundPrice($rowTotal / $availableQty * $this->getQty());
            $baseRowTotal = $creditmemo->roundPrice($baseRowTotal / $availableQty * $this->getQty(), 'base');
        }
        $this->setRowTotal($rowTotal);
        $this->setBaseRowTotal($baseRowTotal);

        if ($rowTotalInclTax && $baseRowTotalInclTax) {
            $orderItemQty = $orderItem->getQtyOrdered();
            $this->setRowTotalInclTax(
                $creditmemo->roundPrice($rowTotalInclTax / $orderItemQty * $this->getQty(), 'including')
            );
            $this->setBaseRowTotalInclTax(
                $creditmemo->roundPrice($baseRowTotalInclTax / $orderItemQty * $this->getQty(), 'including_base')
            );
        }
        return $this;
    }

    /**
     * Checking if the item is last
     *
     * @return bool
     */
    public function isLast()
    {
        $orderItem = $this->getOrderItem();
        if ((string)(double)$this->getQty() == (string)(double)$orderItem->getQtyToRefund() &&
            !$orderItem->getQtyToInvoice()
        ) {
            return true;
        }
        return false;
    }

    /**
     * Before object save
     *
     * @return $this
     */
    protected function _beforeSave()
    {
        parent::_beforeSave();

        if (!$this->getParentId() && $this->getCreditmemo()) {
            $this->setParentId($this->getCreditmemo()->getId());
        }

        return $this;
    }
}
