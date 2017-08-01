<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Model\Cart\SalesModel;

/**
 * Factory for creating payment cart sales models
 * @since 2.0.0
 */
class Factory
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     * @since 2.0.0
     */
    protected $_objectManager;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function create($salesModel)
    {
        $arguments = ['salesModel' => $salesModel];
        if ($salesModel instanceof \Magento\Quote\Model\Quote) {
            return $this->_objectManager->create(\Magento\Payment\Model\Cart\SalesModel\Quote::class, $arguments);
        } elseif ($salesModel instanceof \Magento\Sales\Model\Order) {
            return $this->_objectManager->create(\Magento\Payment\Model\Cart\SalesModel\Order::class, $arguments);
        }
        throw new \InvalidArgumentException('Sales model has bad type!');
    }
}
