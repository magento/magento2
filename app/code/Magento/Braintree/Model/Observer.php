<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Model;

use Magento\Braintree\Model\Config\PayPal as PayPalConfig;

class Observer
{
    const CONFIG_PATH_CAPTURE_ACTION    = 'capture_action';
    const CONFIG_PATH_PAYMENT_ACTION    = 'payment_action';
    const PAYPAL_SHORTCUT_BLOCK = 'Magento\Braintree\Block\PayPal\Shortcut';

    /**
     * @var Vault
     */
    protected $vault;

    /**
     * @var \Magento\Braintree\Model\Config\Cc
     */
    protected $config;

    /**
     * @var \Magento\Braintree\Model\Config\PayPal
     */
    protected $paypalConfig;

    /**
     * @var \Magento\Braintree\Helper\Data
     */
    protected $helper;

    /**
     * @var PaymentMethod\PayPal
     */
    protected $methodPayPal;

    /**
     * @var \Magento\Framework\DB\TransactionFactory
     */
    protected $transactionFactory;

    /**
     * @param Vault $vault
     * @param \Magento\Braintree\Model\Config\Cc $config
     * @param \Magento\Braintree\Helper\Data $helper
     * @param PaymentMethod\PayPal $methodPayPal
     * @param \Magento\Braintree\Model\Config\PayPal $paypalConfig
     * @param \Magento\Framework\DB\TransactionFactory $transactionFactory
     */
    public function __construct(
        Vault $vault,
        \Magento\Braintree\Model\Config\Cc $config,
        \Magento\Braintree\Helper\Data $helper,
        PaymentMethod\PayPal $methodPayPal,
        \Magento\Braintree\Model\Config\PayPal $paypalConfig,
        \Magento\Framework\DB\TransactionFactory $transactionFactory
    ) {
        $this->vault = $vault;
        $this->config = $config;
        $this->helper = $helper;
        $this->methodPayPal = $methodPayPal;
        $this->paypalConfig = $paypalConfig;
        $this->transactionFactory = $transactionFactory;
    }

    /**
     * If it's configured to capture on shipment - do this
     * 
     * @param \Magento\Framework\Object $observer
     * @return $this
     */
    public function processBraintreePayment(\Magento\Framework\Object $observer)
    {
        $shipment = $observer->getEvent()->getShipment();
        $order = $shipment->getOrder();
        if ($order->getPayment()->getMethod() == PaymentMethod::METHOD_CODE
            && $order->canInvoice() && $this->shouldInvoice()) {
            $qtys = [];
            foreach ($shipment->getAllItems() as $shipmentItem) {
                $qtys[$shipmentItem->getOrderItem()->getId()] = $shipmentItem->getQty();
            }
            foreach ($order->getAllItems() as $orderItem) {
                if (!array_key_exists($orderItem->getId(), $qtys)) {
                    $qtys[$orderItem->getId()] = 0;
                }
            }
            $invoice = $order->prepareInvoice($qtys);
            $invoice->setRequestedCaptureCase(\Magento\Sales\Model\Order\Invoice::CAPTURE_ONLINE);
            $invoice->register();
            /** @var \Magento\Framework\DB\Transaction $transaction */
            $transaction = $this->transactionFactory->create();
            $transaction->addObject($invoice)
                ->addObject($invoice->getOrder())
                ->save();
        }
        return $this;
    }

    /**
     * If it's configured to capture on each shipment
     * 
     * @return bool
     */
    protected function shouldInvoice()
    {
        $flag = (($this->config->getConfigData(self::CONFIG_PATH_PAYMENT_ACTION) ==
            \Magento\Payment\Model\Method\AbstractMethod::ACTION_AUTHORIZE) &&
            ($this->config->getConfigData(self::CONFIG_PATH_CAPTURE_ACTION) ==
                PaymentMethod::CAPTURE_ON_SHIPMENT));
        return $flag;
    }

    /**
     * Delete Braintree customer when Magento customer is deleted
     * 
     * @param \Magento\Framework\Object $observer
     * @return $this
     */
    public function deleteBraintreeCustomer(\Magento\Framework\Object $observer)
    {
        if (!$this->config->isActive()) {
            return $this;
        }
        $customer = $observer->getEvent()->getCustomer();
        $customerId = $this->helper->generateCustomerId($customer->getId(), $customer->getEmail());
        if ($this->vault->exists($customerId)) {
            $this->vault->deleteCustomer($customerId);
        }
        return $this;
    }

    /**
     * Add Braintree PayPal shortcut buttons
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function addPaypalShortcuts(\Magento\Framework\Event\Observer $observer)
    {
        //Don't display shortcut on product view page
        if (!$this->methodPayPal->isActive() ||
            !$this->paypalConfig->isShortcutCheckoutEnabled() ||
            $observer->getEvent()->getIsCatalogProduct()) {
            return;
        }

        /** @var \Magento\Catalog\Block\ShortcutButtons $shortcutButtons */
        $shortcutButtons = $observer->getEvent()->getContainer();

        /** @var \Magento\Braintree\Block\PayPal\Shortcut $shortcut */
        $shortcut = $shortcutButtons->getLayout()->createBlock(
            self::PAYPAL_SHORTCUT_BLOCK,
            '',
            [
                'data' => [
                    'container' => $shortcutButtons,
                ]
            ]
        );

        if ($shortcut->skipShortcutForGuest()) {
            return;
        }
        $shortcut->setShowOrPosition(
            $observer->getEvent()->getOrPosition()
        );
        $shortcutButtons->addShortcut($shortcut);
    }
}
