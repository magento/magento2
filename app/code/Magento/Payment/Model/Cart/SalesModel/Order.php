<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Model\Cart\SalesModel;

/**
 * Wrapper for \Magento\Sales\Model\Order sales model
 */
class Order implements \Magento\Payment\Model\Cart\SalesModel\SalesModelInterface
{
    /**
     * Sales order model instance
     *
     * @var \Magento\Sales\Model\Order
     */
    protected $_salesModel;

    /**
     * @param \Magento\Sales\Model\Order $salesModel
     */
    public function __construct(\Magento\Sales\Model\Order $salesModel)
    {
        $this->_salesModel = $salesModel;
    }

    /**
     * {@inheritdoc}
     */
    public function getAllItems()
    {
        $resultItems = [];

        foreach ($this->_salesModel->getAllItems() as $item) {
            $resultItems[] = new \Magento\Framework\Object(
                [
                    'parent_item' => $item->getParentItem(),
                    'name' => $item->getName(),
                    'qty' => (int)$item->getQtyOrdered(),
                    'price' => (double)$item->getBasePrice(),
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
        return $this->_salesModel->getBaseTaxAmount();
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseShippingAmount()
    {
        return $this->_salesModel->getBaseShippingAmount();
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseDiscountAmount()
    {
        return $this->_salesModel->getBaseDiscountAmount();
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
        return $this->_salesModel;
    }
}
