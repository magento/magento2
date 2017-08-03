<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;

/**
 * PayPal module observer
 * @since 2.0.0
 */
class SetResponseAfterSaveOrderObserver implements ObserverInterface
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     * @since 2.0.0
     */
    protected $_coreRegistry;

    /**
     * Paypal hss
     *
     * @var \Magento\Paypal\Helper\Hss
     * @since 2.0.0
     */
    protected $_paypalHss;

    /**
     * @var \Magento\Paypal\Model\Report\SettlementFactory
     * @since 2.0.0
     */
    protected $_settlementFactory;

    /**
     * @var \Magento\Framework\App\ViewInterface
     * @since 2.0.0
     */
    protected $_view;

    /**
     * Constructor
     *
     * @param \Magento\Paypal\Helper\Hss $paypalHss
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\App\ViewInterface $view
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Paypal\Helper\Hss $paypalHss,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\App\ViewInterface $view
    ) {
        $this->_paypalHss = $paypalHss;
        $this->_coreRegistry = $coreRegistry;
        $this->_view = $view;
    }

    /**
     * Set data for response of frontend saveOrder action
     *
     * @param EventObserver $observer
     * @return void
     * @since 2.0.0
     */
    public function execute(EventObserver $observer)
    {
        /* @var $order \Magento\Sales\Model\Order */
        $order = $this->_coreRegistry->registry('hss_order');

        if ($order && $order->getId()) {
            $payment = $order->getPayment();
            if ($payment && in_array($payment->getMethod(), $this->_paypalHss->getHssMethods())) {
                $result = $observer->getData('result')->getData();
                if (empty($result['error'])) {
                    $this->_view->loadLayout('checkout_onepage_review', true, true, false);
                    $html = $this->_view->getLayout()->getBlock('paypal.iframe')->toHtml();
                    $result['update_section'] = ['name' => 'paypaliframe', 'html' => $html];
                    $result['redirect'] = false;
                    $result['success'] = false;
                    $observer->getData('result')->setData($result);
                }
            }
        }
    }
}
