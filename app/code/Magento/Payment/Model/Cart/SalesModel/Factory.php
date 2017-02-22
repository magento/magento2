<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Model\Cart\SalesModel;

/**
 * Factory for creating payment cart sales models
 */
class Factory
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    /**
     * Wrap sales model with Magento\Payment\Model\Cart\SalesModel\SalesModelInterface
     *
     * @param \Magento\Quote\Api\Data\CartInterface $salesModel
     * @return \Magento\Payment\Model\Cart\SalesModel\SalesModelInterface
     * @throws \InvalidArgumentException
     */
    public function create($salesModel)
    {
        $arguments = ['salesModel' => $salesModel];
        if ($salesModel instanceof \Magento\Quote\Model\Quote) {
            return $this->_objectManager->create('Magento\Payment\Model\Cart\SalesModel\Quote', $arguments);
        } elseif ($salesModel instanceof \Magento\Sales\Model\Order) {
            return $this->_objectManager->create('Magento\Payment\Model\Cart\SalesModel\Order', $arguments);
        }
        throw new \InvalidArgumentException('Sales model has bad type!');
    }
}
