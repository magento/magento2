<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Payment\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Class \Magento\Payment\Observer\UpdateOrderStatusForPaymentMethodsObserver
 *
 */
class UpdateOrderStatusForPaymentMethodsObserver implements ObserverInterface
{
    /**
     * @var \Magento\Sales\Model\Order\Config
     */
    protected $_salesOrderConfig;

    /**
     * @var \Magento\Payment\Model\Config
     */
    protected $_paymentConfig;

    /**
     * @var \Magento\Config\Model\ResourceModel\Config
     */
    protected $_resourceConfig;

    /**
     * Construct
     *
     * @param \Magento\Sales\Model\Order\Config $salesOrderConfig
     * @param \Magento\Payment\Model\Config $paymentConfig
     * @param \Magento\Config\Model\ResourceModel\Config $resourceConfig
     */
    public function __construct(
        \Magento\Sales\Model\Order\Config $salesOrderConfig,
        \Magento\Payment\Model\Config $paymentConfig,
        \Magento\Config\Model\ResourceModel\Config $resourceConfig
    ) {
        $this->_salesOrderConfig = $salesOrderConfig;
        $this->_paymentConfig = $paymentConfig;
        $this->_resourceConfig = $resourceConfig;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($observer->getEvent()->getState() != \Magento\Sales\Model\Order::STATE_NEW) {
            return;
        }
        $status = $observer->getEvent()->getStatus();
        $defaultStatus = $this->_salesOrderConfig->getStateDefaultStatus(\Magento\Sales\Model\Order::STATE_NEW);
        $methods = $this->_paymentConfig->getActiveMethods();
        foreach ($methods as $method) {
            if ($method->getConfigData('order_status') == $status) {
                $this->_resourceConfig->saveConfig(
                    'payment/' . $method->getCode() . '/order_status',
                    $defaultStatus,
                    'default',
                    0
                );
            }
        }
    }
}
