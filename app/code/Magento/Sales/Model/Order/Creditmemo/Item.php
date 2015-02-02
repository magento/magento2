<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order\Creditmemo;

use Magento\Framework\Api\AttributeDataBuilder;
use Magento\Framework\Model\AbstractExtensibleModel;
use Magento\Sales\Api\Data\CreditmemoItemInterface;

/**
 * @method \Magento\Sales\Model\Resource\Order\Creditmemo\Item _getResource()
 * @method \Magento\Sales\Model\Resource\Order\Creditmemo\Item getResource()
 * @method \Magento\Sales\Model\Order\Creditmemo\Item setParentId(int $value)
 * @method \Magento\Sales\Model\Order\Creditmemo\Item setWeeeTaxAppliedRowAmount(float $value)
 * @method \Magento\Sales\Model\Order\Creditmemo\Item setBasePrice(float $value)
 * @method \Magento\Sales\Model\Order\Creditmemo\Item setBaseWeeeTaxRowDisposition(float $value)
 * @method \Magento\Sales\Model\Order\Creditmemo\Item setTaxAmount(float $value)
 * @method \Magento\Sales\Model\Order\Creditmemo\Item setBaseWeeeTaxAppliedAmount(float $value)
 * @method \Magento\Sales\Model\Order\Creditmemo\Item setWeeeTaxRowDisposition(float $value)
 * @method \Magento\Sales\Model\Order\Creditmemo\Item setBaseRowTotal(float $value)
 * @method \Magento\Sales\Model\Order\Creditmemo\Item setDiscountAmount(float $value)
 * @method \Magento\Sales\Model\Order\Creditmemo\Item setRowTotal(float $value)
 * @method \Magento\Sales\Model\Order\Creditmemo\Item setWeeeTaxAppliedAmount(float $value)
 * @method \Magento\Sales\Model\Order\Creditmemo\Item setBaseDiscountAmount(float $value)
 * @method \Magento\Sales\Model\Order\Creditmemo\Item setBaseWeeeTaxDisposition(float $value)
 * @method \Magento\Sales\Model\Order\Creditmemo\Item setPriceInclTax(float $value)
 * @method \Magento\Sales\Model\Order\Creditmemo\Item setBaseTaxAmount(float $value)
 * @method \Magento\Sales\Model\Order\Creditmemo\Item setWeeeTaxDisposition(float $value)
 * @method \Magento\Sales\Model\Order\Creditmemo\Item setBasePriceInclTax(float $value)
 * @method \Magento\Sales\Model\Order\Creditmemo\Item setBaseCost(float $value)
 * @method \Magento\Sales\Model\Order\Creditmemo\Item setBaseWeeeTaxAppliedRowAmnt(float $value)
 * @method \Magento\Sales\Model\Order\Creditmemo\Item setPrice(float $value)
 * @method \Magento\Sales\Model\Order\Creditmemo\Item setBaseRowTotalInclTax(float $value)
 * @method \Magento\Sales\Model\Order\Creditmemo\Item setRowTotalInclTax(float $value)
 * @method \Magento\Sales\Model\Order\Creditmemo\Item setProductId(int $value)
 * @method \Magento\Sales\Model\Order\Creditmemo\Item setOrderItemId(int $value)
 * @method \Magento\Sales\Model\Order\Creditmemo\Item setAdditionalData(string $value)
 * @method \Magento\Sales\Model\Order\Creditmemo\Item setDescription(string $value)
 * @method \Magento\Sales\Model\Order\Creditmemo\Item setWeeeTaxApplied(string $value)
 * @method \Magento\Sales\Model\Order\Creditmemo\Item setSku(string $value)
 * @method \Magento\Sales\Model\Order\Creditmemo\Item setName(string $value)
 * @method \Magento\Sales\Model\Order\Creditmemo\Item setHiddenTaxAmount(float $value)
 * @method \Magento\Sales\Model\Order\Creditmemo\Item setBaseHiddenTaxAmount(float $value)
 */
class Item extends AbstractExtensibleModel implements CreditmemoItemInterface
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
     * @param \Magento\Framework\Api\MetadataServiceInterface $metadataService
     * @param AttributeDataBuilder $customAttributeBuilder
     * @param \Magento\Sales\Model\Order\ItemFactory $orderItemFactory
     * @param \Magento\Framework\Model\Resource\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\MetadataServiceInterface $metadataService,
        AttributeDataBuilder $customAttributeBuilder,
        \Magento\Sales\Model\Order\ItemFactory $orderItemFactory,
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

        if (!$this->isLast() && $orderItemQtyInvoiced > 0 && $this->getQty() > 0) {
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
        if ((string)(double)$this->getQty() == (string)(double)$orderItem->getQtyToRefund()) {
            return true;
        }
        return false;
    }

    /**
     * Returns additional_data
     *
     * @return string
     */
    public function getAdditionalData()
    {
        return $this->getData(CreditmemoItemInterface::ADDITIONAL_DATA);
    }

    /**
     * Returns base_cost
     *
     * @return float
     */
    public function getBaseCost()
    {
        return $this->getData(CreditmemoItemInterface::BASE_COST);
    }

    /**
     * Returns base_discount_amount
     *
     * @return float
     */
    public function getBaseDiscountAmount()
    {
        return $this->getData(CreditmemoItemInterface::BASE_DISCOUNT_AMOUNT);
    }

    /**
     * Returns base_hidden_tax_amount
     *
     * @return float
     */
    public function getBaseHiddenTaxAmount()
    {
        return $this->getData(CreditmemoItemInterface::BASE_HIDDEN_TAX_AMOUNT);
    }

    /**
     * Returns base_price
     *
     * @return float
     */
    public function getBasePrice()
    {
        return $this->getData(CreditmemoItemInterface::BASE_PRICE);
    }

    /**
     * Returns base_price_incl_tax
     *
     * @return float
     */
    public function getBasePriceInclTax()
    {
        return $this->getData(CreditmemoItemInterface::BASE_PRICE_INCL_TAX);
    }

    /**
     * Returns base_row_total
     *
     * @return float
     */
    public function getBaseRowTotal()
    {
        return $this->getData(CreditmemoItemInterface::BASE_ROW_TOTAL);
    }

    /**
     * Returns base_row_total_incl_tax
     *
     * @return float
     */
    public function getBaseRowTotalInclTax()
    {
        return $this->getData(CreditmemoItemInterface::BASE_ROW_TOTAL_INCL_TAX);
    }

    /**
     * Returns base_tax_amount
     *
     * @return float
     */
    public function getBaseTaxAmount()
    {
        return $this->getData(CreditmemoItemInterface::BASE_TAX_AMOUNT);
    }

    /**
     * Returns base_weee_tax_applied_amount
     *
     * @return float
     */
    public function getBaseWeeeTaxAppliedAmount()
    {
        return $this->getData(CreditmemoItemInterface::BASE_WEEE_TAX_APPLIED_AMOUNT);
    }

    /**
     * Returns base_weee_tax_applied_row_amnt
     *
     * @return float
     */
    public function getBaseWeeeTaxAppliedRowAmnt()
    {
        return $this->getData(CreditmemoItemInterface::BASE_WEEE_TAX_APPLIED_ROW_AMNT);
    }

    /**
     * Returns base_weee_tax_disposition
     *
     * @return float
     */
    public function getBaseWeeeTaxDisposition()
    {
        return $this->getData(CreditmemoItemInterface::BASE_WEEE_TAX_DISPOSITION);
    }

    /**
     * Returns base_weee_tax_row_disposition
     *
     * @return float
     */
    public function getBaseWeeeTaxRowDisposition()
    {
        return $this->getData(CreditmemoItemInterface::BASE_WEEE_TAX_ROW_DISPOSITION);
    }

    /**
     * Returns description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->getData(CreditmemoItemInterface::DESCRIPTION);
    }

    /**
     * Returns discount_amount
     *
     * @return float
     */
    public function getDiscountAmount()
    {
        return $this->getData(CreditmemoItemInterface::DISCOUNT_AMOUNT);
    }

    /**
     * Returns hidden_tax_amount
     *
     * @return float
     */
    public function getHiddenTaxAmount()
    {
        return $this->getData(CreditmemoItemInterface::HIDDEN_TAX_AMOUNT);
    }

    /**
     * Returns name
     *
     * @return string
     */
    public function getName()
    {
        return $this->getData(CreditmemoItemInterface::NAME);
    }

    /**
     * Returns order_item_id
     *
     * @return int
     */
    public function getOrderItemId()
    {
        return $this->getData(CreditmemoItemInterface::ORDER_ITEM_ID);
    }

    /**
     * Returns parent_id
     *
     * @return int
     */
    public function getParentId()
    {
        return $this->getData(CreditmemoItemInterface::PARENT_ID);
    }

    /**
     * Returns price
     *
     * @return float
     */
    public function getPrice()
    {
        return $this->getData(CreditmemoItemInterface::PRICE);
    }

    /**
     * Returns price_incl_tax
     *
     * @return float
     */
    public function getPriceInclTax()
    {
        return $this->getData(CreditmemoItemInterface::PRICE_INCL_TAX);
    }

    /**
     * Returns product_id
     *
     * @return int
     */
    public function getProductId()
    {
        return $this->getData(CreditmemoItemInterface::PRODUCT_ID);
    }

    /**
     * Returns qty
     *
     * @return float
     */
    public function getQty()
    {
        return $this->getData(CreditmemoItemInterface::QTY);
    }

    /**
     * Returns row_total
     *
     * @return float
     */
    public function getRowTotal()
    {
        return $this->getData(CreditmemoItemInterface::ROW_TOTAL);
    }

    /**
     * Returns row_total_incl_tax
     *
     * @return float
     */
    public function getRowTotalInclTax()
    {
        return $this->getData(CreditmemoItemInterface::ROW_TOTAL_INCL_TAX);
    }

    /**
     * Returns sku
     *
     * @return string
     */
    public function getSku()
    {
        return $this->getData(CreditmemoItemInterface::SKU);
    }

    /**
     * Returns tax_amount
     *
     * @return float
     */
    public function getTaxAmount()
    {
        return $this->getData(CreditmemoItemInterface::TAX_AMOUNT);
    }

    /**
     * Returns weee_tax_applied
     *
     * @return string
     */
    public function getWeeeTaxApplied()
    {
        return $this->getData(CreditmemoItemInterface::WEEE_TAX_APPLIED);
    }

    /**
     * Returns weee_tax_applied_amount
     *
     * @return float
     */
    public function getWeeeTaxAppliedAmount()
    {
        return $this->getData(CreditmemoItemInterface::WEEE_TAX_APPLIED_AMOUNT);
    }

    /**
     * Returns weee_tax_applied_row_amount
     *
     * @return float
     */
    public function getWeeeTaxAppliedRowAmount()
    {
        return $this->getData(CreditmemoItemInterface::WEEE_TAX_APPLIED_ROW_AMOUNT);
    }

    /**
     * Returns weee_tax_disposition
     *
     * @return float
     */
    public function getWeeeTaxDisposition()
    {
        return $this->getData(CreditmemoItemInterface::WEEE_TAX_DISPOSITION);
    }

    /**
     * Returns weee_tax_row_disposition
     *
     * @return float
     */
    public function getWeeeTaxRowDisposition()
    {
        return $this->getData(CreditmemoItemInterface::WEEE_TAX_ROW_DISPOSITION);
    }
}
