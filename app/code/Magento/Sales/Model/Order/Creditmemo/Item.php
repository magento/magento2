<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order\Creditmemo;

use Magento\Framework\Api\AttributeValueFactory;
use Magento\Sales\Api\Data\CreditmemoItemInterface;
use Magento\Sales\Model\AbstractModel;

/**
 * Creditmemo item model.
 *
 * @api
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @since 100.0.2
 */
class Item extends AbstractModel implements CreditmemoItemInterface
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
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param \Magento\Sales\Model\Order\ItemFactory $orderItemFactory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        \Magento\Sales\Model\Order\ItemFactory $orderItemFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
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
        $this->_orderItemFactory = $orderItemFactory;
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Magento\Sales\Model\ResourceModel\Order\Creditmemo\Item::class);
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
        if ($this->_orderItem === null) {
            if ($this->getCreditmemo()) {
                $orderItem = $this->getCreditmemo()->getOrder()->getItemById($this->getOrderItemId());
            } else {
                $orderItem = $this->_orderItemFactory->create()->load($this->getOrderItemId());
            }
            $this->_orderItem = $orderItem;
        }
        return $this->_orderItem;
    }

    /**
     * Checks if quantity available for refund
     *
     * @param int $qty
     * @param \Magento\Sales\Model\Order\Item $orderItem
     * @return bool
     */
    private function isQtyAvailable($qty, \Magento\Sales\Model\Order\Item $orderItem)
    {
        return $qty <= $orderItem->getQtyToRefund() || $orderItem->isDummy();
    }

    /**
     * Declare qty
     *
     * @param float $qty
     * @return $this
     */
    public function setQty($qty)
    {
        $this->setData(CreditmemoItemInterface::QTY, $qty);
        return $this;
    }

    /**
     * Applying qty to order item
     *
     * @return \Magento\Sales\Model\Order\Creditmemo\Item
     */
    public function register()
    {
        $orderItem = $this->getOrderItem();

        $qty = $this->processQty();
        $orderItem->setQtyRefunded($orderItem->getQtyRefunded() + $qty);
        $orderItem->setTaxRefunded($orderItem->getTaxRefunded() + $this->getTaxAmount());
        $orderItem->setBaseTaxRefunded($orderItem->getBaseTaxRefunded() + $this->getBaseTaxAmount());
        $orderItem->setDiscountTaxCompensationRefunded(
            $orderItem->getDiscountTaxCompensationRefunded() + $this->getDiscountTaxCompensationAmount()
        );
        $orderItem->setBaseDiscountTaxCompensationRefunded(
            $orderItem->getBaseDiscountTaxCompensationRefunded() + $this->getBaseDiscountTaxCompensationAmount()
        );
        $orderItem->setAmountRefunded($orderItem->getAmountRefunded() + $this->getRowTotal());
        $orderItem->setBaseAmountRefunded($orderItem->getBaseAmountRefunded() + $this->getBaseRowTotal());
        $orderItem->setDiscountRefunded($orderItem->getDiscountRefunded() + $this->getDiscountAmount());
        $orderItem->setBaseDiscountRefunded($orderItem->getBaseDiscountRefunded() + $this->getBaseDiscountAmount());

        return $this;
    }

    /**
     * Calculate qty for creditmemo item.
     *
     * @return int|float
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function processQty()
    {
        $orderItem = $this->getOrderItem();
        $qty = $this->getQty();
        if ($orderItem->getIsQtyDecimal()) {
            $qty = (double)$qty;
        } else {
            $qty = (int)$qty;
        }
        $qty = $qty > 0 ? $qty : 0;
        if ($this->isQtyAvailable($qty, $orderItem)) {
            return $qty;
        } else {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('We found an invalid quantity to refund item "%1".', $this->getName())
            );
        }
    }

    /**
     * Cancel creaditmemeo item.
     *
     * @return $this
     */
    public function cancel()
    {
        $qty = $this->processQty();
        $this->getOrderItem()->setQtyRefunded($this->getOrderItem()->getQtyRefunded() - $qty);
        $this->getOrderItem()->setTaxRefunded(
            $this->getOrderItem()->getTaxRefunded() -
            $this->getOrderItem()->getBaseTaxAmount() *
            $qty /
            $this->getOrderItem()->getQtyOrdered()
        );
        $this->getOrderItem()->setDiscountTaxCompensationRefunded(
            $this->getOrderItem()->getDiscountTaxCompensationRefunded() -
            $this->getOrderItem()->getDiscountTaxCompensationAmount() *
            $qty /
            $this->getOrderItem()->getQtyOrdered()
        );
        return $this;
    }

    /**
     * Invoice item row total calculation
     *
     * @return $this
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

        $qty = $this->processQty();
        if (!$this->isLast() && $orderItemQtyInvoiced > 0 && $qty >= 0) {
            $availableQty = $orderItemQtyInvoiced - $orderItem->getQtyRefunded();
            $rowTotal = $creditmemo->roundPrice($rowTotal / $availableQty * $qty);
            $baseRowTotal = $creditmemo->roundPrice($baseRowTotal / $availableQty * $qty, 'base');
        }
        $this->setRowTotal($rowTotal);
        $this->setBaseRowTotal($baseRowTotal);

        if ($rowTotalInclTax && $baseRowTotalInclTax) {
            $orderItemQty = $orderItem->getQtyOrdered();
            $this->setRowTotalInclTax(
                $creditmemo->roundPrice($rowTotalInclTax / $orderItemQty * $qty, 'including')
            );
            $this->setBaseRowTotalInclTax(
                $creditmemo->roundPrice($baseRowTotalInclTax / $orderItemQty * $qty, 'including_base')
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
        $qty = $this->processQty();
        if ((string)(double)$qty == (string)(double)$orderItem->getQtyToRefund()) {
            return true;
        }
        return false;
    }

    /**
     * Returns additional_data
     *
     * @return string|null
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
     * @return float|null
     */
    public function getBaseDiscountAmount()
    {
        return $this->getData(CreditmemoItemInterface::BASE_DISCOUNT_AMOUNT);
    }

    /**
     * Returns base_discount_tax_compensation_amount
     *
     * @return float|null
     */
    public function getBaseDiscountTaxCompensationAmount()
    {
        return $this->getData(CreditmemoItemInterface::BASE_DISCOUNT_TAX_COMPENSATION_AMOUNT);
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
     * @return float|null
     */
    public function getBasePriceInclTax()
    {
        return $this->getData(CreditmemoItemInterface::BASE_PRICE_INCL_TAX);
    }

    /**
     * Returns base_row_total
     *
     * @return float|null
     */
    public function getBaseRowTotal()
    {
        return $this->getData(CreditmemoItemInterface::BASE_ROW_TOTAL);
    }

    /**
     * Returns base_row_total_incl_tax
     *
     * @return float|null
     */
    public function getBaseRowTotalInclTax()
    {
        return $this->getData(CreditmemoItemInterface::BASE_ROW_TOTAL_INCL_TAX);
    }

    /**
     * Returns base_tax_amount
     *
     * @return float|null
     */
    public function getBaseTaxAmount()
    {
        return $this->getData(CreditmemoItemInterface::BASE_TAX_AMOUNT);
    }

    /**
     * Returns base_weee_tax_applied_amount
     *
     * @return float|null
     */
    public function getBaseWeeeTaxAppliedAmount()
    {
        return $this->getData(CreditmemoItemInterface::BASE_WEEE_TAX_APPLIED_AMOUNT);
    }

    /**
     * Returns base_weee_tax_applied_row_amnt
     *
     * @return float|null
     */
    public function getBaseWeeeTaxAppliedRowAmnt()
    {
        return $this->getData(CreditmemoItemInterface::BASE_WEEE_TAX_APPLIED_ROW_AMNT);
    }

    /**
     * Returns base_weee_tax_disposition
     *
     * @return float|null
     */
    public function getBaseWeeeTaxDisposition()
    {
        return $this->getData(CreditmemoItemInterface::BASE_WEEE_TAX_DISPOSITION);
    }

    /**
     * Returns base_weee_tax_row_disposition
     *
     * @return float|null
     */
    public function getBaseWeeeTaxRowDisposition()
    {
        return $this->getData(CreditmemoItemInterface::BASE_WEEE_TAX_ROW_DISPOSITION);
    }

    /**
     * Returns description
     *
     * @return string|null
     */
    public function getDescription()
    {
        return $this->getData(CreditmemoItemInterface::DESCRIPTION);
    }

    /**
     * Returns discount_amount
     *
     * @return float|null
     */
    public function getDiscountAmount()
    {
        return $this->getData(CreditmemoItemInterface::DISCOUNT_AMOUNT);
    }

    /**
     * Returns discount_tax_compensation_amount
     *
     * @return float|null
     */
    public function getDiscountTaxCompensationAmount()
    {
        return $this->getData(CreditmemoItemInterface::DISCOUNT_TAX_COMPENSATION_AMOUNT);
    }

    /**
     * Returns name
     *
     * @return string|null
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
     * @return int|null
     */
    public function getParentId()
    {
        return $this->getData(CreditmemoItemInterface::PARENT_ID);
    }

    /**
     * Returns price
     *
     * @return float|null
     */
    public function getPrice()
    {
        return $this->getData(CreditmemoItemInterface::PRICE);
    }

    /**
     * Returns price_incl_tax
     *
     * @return float|null
     */
    public function getPriceInclTax()
    {
        return $this->getData(CreditmemoItemInterface::PRICE_INCL_TAX);
    }

    /**
     * Returns product_id
     *
     * @return int|null
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
     * @return float|null
     */
    public function getRowTotal()
    {
        return $this->getData(CreditmemoItemInterface::ROW_TOTAL);
    }

    /**
     * Returns row_total_incl_tax
     *
     * @return float|null
     */
    public function getRowTotalInclTax()
    {
        return $this->getData(CreditmemoItemInterface::ROW_TOTAL_INCL_TAX);
    }

    /**
     * Returns sku
     *
     * @return string|null
     */
    public function getSku()
    {
        return $this->getData(CreditmemoItemInterface::SKU);
    }

    /**
     * Returns tax_amount
     *
     * @return float|null
     */
    public function getTaxAmount()
    {
        return $this->getData(CreditmemoItemInterface::TAX_AMOUNT);
    }

    /**
     * Returns weee_tax_applied
     *
     * @return string|null
     */
    public function getWeeeTaxApplied()
    {
        return $this->getData(CreditmemoItemInterface::WEEE_TAX_APPLIED);
    }

    /**
     * Returns weee_tax_applied_amount
     *
     * @return float|null
     */
    public function getWeeeTaxAppliedAmount()
    {
        return $this->getData(CreditmemoItemInterface::WEEE_TAX_APPLIED_AMOUNT);
    }

    /**
     * Returns weee_tax_applied_row_amount
     *
     * @return float|null
     */
    public function getWeeeTaxAppliedRowAmount()
    {
        return $this->getData(CreditmemoItemInterface::WEEE_TAX_APPLIED_ROW_AMOUNT);
    }

    /**
     * Returns weee_tax_disposition
     *
     * @return float|null
     */
    public function getWeeeTaxDisposition()
    {
        return $this->getData(CreditmemoItemInterface::WEEE_TAX_DISPOSITION);
    }

    /**
     * Returns weee_tax_row_disposition
     *
     * @return float|null
     */
    public function getWeeeTaxRowDisposition()
    {
        return $this->getData(CreditmemoItemInterface::WEEE_TAX_ROW_DISPOSITION);
    }

    //@codeCoverageIgnoreStart

    /**
     * @inheritdoc
     */
    public function setParentId($id)
    {
        return $this->setData(CreditmemoItemInterface::PARENT_ID, $id);
    }

    /**
     * @inheritdoc
     */
    public function setBasePrice($price)
    {
        return $this->setData(CreditmemoItemInterface::BASE_PRICE, $price);
    }

    /**
     * @inheritdoc
     */
    public function setTaxAmount($amount)
    {
        return $this->setData(CreditmemoItemInterface::TAX_AMOUNT, $amount);
    }

    /**
     * @inheritdoc
     */
    public function setBaseRowTotal($amount)
    {
        return $this->setData(CreditmemoItemInterface::BASE_ROW_TOTAL, $amount);
    }

    /**
     * @inheritdoc
     */
    public function setDiscountAmount($amount)
    {
        return $this->setData(CreditmemoItemInterface::DISCOUNT_AMOUNT, $amount);
    }

    /**
     * @inheritdoc
     */
    public function setRowTotal($amount)
    {
        return $this->setData(CreditmemoItemInterface::ROW_TOTAL, $amount);
    }

    /**
     * @inheritdoc
     */
    public function setBaseDiscountAmount($amount)
    {
        return $this->setData(CreditmemoItemInterface::BASE_DISCOUNT_AMOUNT, $amount);
    }

    /**
     * @inheritdoc
     */
    public function setPriceInclTax($amount)
    {
        return $this->setData(CreditmemoItemInterface::PRICE_INCL_TAX, $amount);
    }

    /**
     * @inheritdoc
     */
    public function setBaseTaxAmount($amount)
    {
        return $this->setData(CreditmemoItemInterface::BASE_TAX_AMOUNT, $amount);
    }

    /**
     * @inheritdoc
     */
    public function setBasePriceInclTax($amount)
    {
        return $this->setData(CreditmemoItemInterface::BASE_PRICE_INCL_TAX, $amount);
    }

    /**
     * @inheritdoc
     */
    public function setBaseCost($baseCost)
    {
        return $this->setData(CreditmemoItemInterface::BASE_COST, $baseCost);
    }

    /**
     * @inheritdoc
     */
    public function setPrice($price)
    {
        return $this->setData(CreditmemoItemInterface::PRICE, $price);
    }

    /**
     * @inheritdoc
     */
    public function setBaseRowTotalInclTax($amount)
    {
        return $this->setData(CreditmemoItemInterface::BASE_ROW_TOTAL_INCL_TAX, $amount);
    }

    /**
     * @inheritdoc
     */
    public function setRowTotalInclTax($amount)
    {
        return $this->setData(CreditmemoItemInterface::ROW_TOTAL_INCL_TAX, $amount);
    }

    /**
     * @inheritdoc
     */
    public function setProductId($id)
    {
        return $this->setData(CreditmemoItemInterface::PRODUCT_ID, $id);
    }

    /**
     * @inheritdoc
     */
    public function setOrderItemId($id)
    {
        return $this->setData(CreditmemoItemInterface::ORDER_ITEM_ID, $id);
    }

    /**
     * @inheritdoc
     */
    public function setAdditionalData($additionalData)
    {
        return $this->setData(CreditmemoItemInterface::ADDITIONAL_DATA, $additionalData);
    }

    /**
     * @inheritdoc
     */
    public function setDescription($description)
    {
        return $this->setData(CreditmemoItemInterface::DESCRIPTION, $description);
    }

    /**
     * @inheritdoc
     */
    public function setSku($sku)
    {
        return $this->setData(CreditmemoItemInterface::SKU, $sku);
    }

    /**
     * @inheritdoc
     */
    public function setName($name)
    {
        return $this->setData(CreditmemoItemInterface::NAME, $name);
    }

    /**
     * @inheritdoc
     */
    public function setDiscountTaxCompensationAmount($amount)
    {
        return $this->setData(CreditmemoItemInterface::DISCOUNT_TAX_COMPENSATION_AMOUNT, $amount);
    }

    /**
     * @inheritdoc
     */
    public function setBaseDiscountTaxCompensationAmount($amount)
    {
        return $this->setData(CreditmemoItemInterface::BASE_DISCOUNT_TAX_COMPENSATION_AMOUNT, $amount);
    }

    /**
     * @inheritdoc
     */
    public function setWeeeTaxDisposition($weeeTaxDisposition)
    {
        return $this->setData(CreditmemoItemInterface::WEEE_TAX_DISPOSITION, $weeeTaxDisposition);
    }

    /**
     * @inheritdoc
     */
    public function setWeeeTaxRowDisposition($weeeTaxRowDisposition)
    {
        return $this->setData(CreditmemoItemInterface::WEEE_TAX_ROW_DISPOSITION, $weeeTaxRowDisposition);
    }

    /**
     * @inheritdoc
     */
    public function setBaseWeeeTaxDisposition($baseWeeeTaxDisposition)
    {
        return $this->setData(CreditmemoItemInterface::BASE_WEEE_TAX_DISPOSITION, $baseWeeeTaxDisposition);
    }

    /**
     * @inheritdoc
     */
    public function setBaseWeeeTaxRowDisposition($baseWeeeTaxRowDisposition)
    {
        return $this->setData(CreditmemoItemInterface::BASE_WEEE_TAX_ROW_DISPOSITION, $baseWeeeTaxRowDisposition);
    }

    /**
     * @inheritdoc
     */
    public function setWeeeTaxApplied($weeeTaxApplied)
    {
        return $this->setData(CreditmemoItemInterface::WEEE_TAX_APPLIED, $weeeTaxApplied);
    }

    /**
     * @inheritdoc
     */
    public function setBaseWeeeTaxAppliedAmount($amount)
    {
        return $this->setData(CreditmemoItemInterface::BASE_WEEE_TAX_APPLIED_AMOUNT, $amount);
    }

    /**
     * @inheritdoc
     */
    public function setBaseWeeeTaxAppliedRowAmnt($amnt)
    {
        return $this->setData(CreditmemoItemInterface::BASE_WEEE_TAX_APPLIED_ROW_AMNT, $amnt);
    }

    /**
     * @inheritdoc
     */
    public function setWeeeTaxAppliedAmount($amount)
    {
        return $this->setData(CreditmemoItemInterface::WEEE_TAX_APPLIED_AMOUNT, $amount);
    }

    /**
     * @inheritdoc
     */
    public function setWeeeTaxAppliedRowAmount($amount)
    {
        return $this->setData(CreditmemoItemInterface::WEEE_TAX_APPLIED_ROW_AMOUNT, $amount);
    }

    /**
     * @inheritdoc
     *
     * @return \Magento\Sales\Api\Data\CreditmemoItemExtensionInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * @inheritdoc
     *
     * @param \Magento\Sales\Api\Data\CreditmemoItemExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Magento\Sales\Api\Data\CreditmemoItemExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }

    //@codeCoverageIgnoreEnd
}
