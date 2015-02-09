<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Model;

/**
 * Payment Observer
 */
class Observer
{
    /**
     * @var \Magento\Sales\Model\Order\Config
     */
    protected $_salesOrderConfig;

    /**
     * @var Config
     */
    protected $_paymentConfig;

    /**
     * @var \Magento\Core\Model\Resource\Config
     */
    protected $_resourceConfig;

    /**
     * Construct
     *
     * @param \Magento\Sales\Model\Order\Config $salesOrderConfig
     * @param \Magento\Payment\Model\Config $paymentConfig
     * @param \Magento\Core\Model\Resource\Config $resourceConfig
     */
    public function __construct(
        \Magento\Sales\Model\Order\Config $salesOrderConfig,
        \Magento\Payment\Model\Config $paymentConfig,
        \Magento\Core\Model\Resource\Config $resourceConfig
    ) {
        $this->_salesOrderConfig = $salesOrderConfig;
        $this->_paymentConfig = $paymentConfig;
        $this->_resourceConfig = $resourceConfig;
    }

    /**
     * Set forced canCreditmemo flag
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function salesOrderBeforeSave($observer)
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $observer->getEvent()->getOrder();

        if ($order->getPayment()->getMethodInstance()->getCode() != 'free') {
            return $this;
        }

        if ($order->canUnhold()) {
            return $this;
        }

        if ($order->isCanceled() || $order->getState() == \Magento\Sales\Model\Order::STATE_CLOSED) {
            return $this;
        }
        /**
         * Allow forced creditmemo just in case if it wasn't defined before
         */
        if (!$order->hasForcedCanCreditmemo()) {
            $order->setForcedCanCreditmemo(true);
        }
        return $this;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function updateOrderStatusForPaymentMethods(\Magento\Framework\Event\Observer $observer)
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
