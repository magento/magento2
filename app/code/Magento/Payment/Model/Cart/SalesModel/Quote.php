<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Model\Cart\SalesModel;

/**
 * Wrapper for \Magento\Quote\Model\Quote sales model
 */
class Quote implements \Magento\Payment\Model\Cart\SalesModel\SalesModelInterface
{
    /**
     * Sales quote model instance
     *
     * @var \Magento\Quote\Model\Quote
     */
    protected $_salesModel;

    /**
     * @var \Magento\Quote\Model\Quote\Address
     */
    protected $_address;

    /**
     * @param \Magento\Quote\Model\Quote $salesModel
     */
    public function __construct(\Magento\Quote\Model\Quote $salesModel)
    {
        $this->_salesModel = $salesModel;
        $this->_address = $this
            ->_salesModel
            ->getIsVirtual() ? $this
            ->_salesModel
            ->getBillingAddress() : $this
            ->_salesModel
            ->getShippingAddress();
    }

    /**
     * {@inheritdoc}
     */
    public function getAllItems()
    {
        $resultItems = [];

        foreach ($this->_salesModel->getAllItems() as $item) {
            $resultItems[] = new \Magento\Framework\DataObject(
                [
                    'parent_item' => $item->getParentItem(),
                    'name' => $item->getName(),
                    'qty' => (int)$item->getTotalQty(),
                    'price' => (double)$item->getBaseCalculationPrice(),
                    'original_item' => $item,
                ]
            );
        }

        return $resultItems;
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseSubtotal()
    {
        return $this->_salesModel->getBaseSubtotal();
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseTaxAmount()
    {
        return $this->_address->getBaseTaxAmount();
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseShippingAmount()
    {
        return $this->_address->getBaseShippingAmount();
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseDiscountAmount()
    {
        return $this->_address->getBaseDiscountAmount();
    }

    /**
     * {@inheritdoc}
     */
    public function getDataUsingMethod($key, $args = null)
    {
        return $this->_salesModel->getDataUsingMethod($key, $args);
    }

    /**
     * {@inheritdoc}
     */
    public function getTaxContainer()
    {
        return $this->_salesModel
            ->getIsVirtual() ? $this
            ->_salesModel
            ->getBillingAddress() : $this
            ->_salesModel
            ->getShippingAddress();
    }
}
