<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Model;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Paypal\Model\Config as PaypalConfig;

/**
 * PayPal module observer
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Observer
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * Paypal hss
     *
     * @var \Magento\Paypal\Helper\Hss
     */
    protected $_paypalHss;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;

    /**
     * @var \Magento\Paypal\Model\Report\SettlementFactory
     */
    protected $_settlementFactory;

    /**
     * @var \Magento\Framework\App\ViewInterface
     */
    protected $_view;

    /**
     * @var \Magento\Paypal\Model\Billing\AgreementFactory
     */
    protected $_agreementFactory;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var \Magento\Paypal\Helper\Shortcut\Factory
     */
    protected $_shortcutFactory;

    /**
     * @var \Magento\Paypal\Helper\Data
     */
    private $paypalData;

    /**
     * Constructor
     *
     * @param \Magento\Paypal\Helper\Hss $paypalHss
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Psr\Log\LoggerInterface $logger
     * @param Report\SettlementFactory $settlementFactory
     * @param \Magento\Framework\App\ViewInterface $view
     * @param \Magento\Paypal\Model\Billing\AgreementFactory $agreementFactory
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Paypal\Helper\Shortcut\Factory $shortcutFactory
     * @param \Magento\Paypal\Helper\Data $paypalData
     * @param PaypalConfig $paypalConfig
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Paypal\Helper\Hss $paypalHss,
        \Magento\Framework\Registry $coreRegistry,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Paypal\Model\Report\SettlementFactory $settlementFactory,
        \Magento\Framework\App\ViewInterface $view,
        \Magento\Paypal\Model\Billing\AgreementFactory $agreementFactory,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Paypal\Helper\Shortcut\Factory $shortcutFactory,
        \Magento\Paypal\Helper\Data $paypalData,
        PaypalConfig $paypalConfig
    ) {
        $this->_paypalHss = $paypalHss;
        $this->_coreRegistry = $coreRegistry;
        $this->_logger = $logger;
        $this->_settlementFactory = $settlementFactory;
        $this->_view = $view;
        $this->_agreementFactory = $agreementFactory;
        $this->_checkoutSession = $checkoutSession;
        $this->_shortcutFactory = $shortcutFactory;
        $this->_paypalConfig = $paypalConfig;
        $this->paypalData = $paypalData;
    }

    /**
     * Goes to reports.paypal.com and fetches Settlement reports.
     *
     * @return void
     */
    public function fetchReports()
    {
        try {
            /** @var \Magento\Paypal\Model\Report\Settlement $reports */
            $reports = $this->_settlementFactory->create();
            /* @var $reports \Magento\Paypal\Model\Report\Settlement */
            $credentials = $reports->getSftpCredentials(true);
            foreach ($credentials as $config) {
                try {
                    $reports->fetchAndSave(\Magento\Paypal\Model\Report\Settlement::createConnection($config));
                } catch (\Exception $e) {
                    $this->_logger->critical($e);
                }
            }
        } catch (\Exception $e) {
            $this->_logger->critical($e);
        }
    }

    /**
     * Save order into registry to use it in the overloaded controller.
     *
     * @param EventObserver $observer
     * @return $this
     */
    public function saveOrderAfterSubmit(EventObserver $observer)
    {
        /* @var $order \Magento\Sales\Model\Order */
        $order = $observer->getEvent()->getData('order');
        $this->_coreRegistry->register('hss_order', $order, true);

        return $this;
    }

    /**
     * Set data for response of frontend saveOrder action
     *
     * @param EventObserver $observer
     * @return $this
     */
    public function setResponseAfterSaveOrder(EventObserver $observer)
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

        return $this;
    }

    /**
     * @param EventObserver $observer
     * @return void
     */
    public function addBillingAgreementToSession(EventObserver $observer)
    {
        /** @var \Magento\Sales\Model\Order\Payment $orderPayment */
        $orderPayment = $observer->getEvent()->getPayment();
        $agreementCreated = false;
        if ($orderPayment->getBillingAgreementData()) {
            $order = $orderPayment->getOrder();
            /** @var \Magento\Paypal\Model\Billing\Agreement $agreement */
            $agreement = $this->_agreementFactory->create()->importOrderPayment($orderPayment);
            if ($agreement->isValid()) {
                $message = __('Created billing agreement #%1.', $agreement->getReferenceId());
                $order->addRelatedObject($agreement);
                $this->_checkoutSession->setLastBillingAgreementReferenceId($agreement->getReferenceId());
                $agreementCreated = true;
            } else {
                $message = __('We can\'t create a billing agreement for this order.');
            }
            $comment = $order->addStatusHistoryComment($message);
            $order->addRelatedObject($comment);
        }
        if (!$agreementCreated) {
            $this->_checkoutSession->unsLastBillingAgreementReferenceId();
        }
    }

    /**
     * Add PayPal shortcut buttons
     *
     * @param EventObserver $observer
     * @return void
     */
    public function addPaypalShortcuts(EventObserver $observer)
    {
        /** @var \Magento\Catalog\Block\ShortcutButtons $shortcutButtons */
        $shortcutButtons = $observer->getEvent()->getContainer();
        $blocks = [
            'Magento\Paypal\Block\Express\ShortcutContainer' => PaypalConfig::METHOD_WPP_EXPRESS,
            'Magento\Paypal\Block\WpsExpress\ShortcutContainer' => PaypalConfig::METHOD_WPS_EXPRESS,
            'Magento\Paypal\Block\Express\Shortcut' => PaypalConfig::METHOD_WPP_EXPRESS,
            'Magento\Paypal\Block\WpsExpress\Shortcut' => PaypalConfig::METHOD_WPS_EXPRESS,
            'Magento\Paypal\Block\PayflowExpress\Shortcut' => PaypalConfig::METHOD_WPP_PE_EXPRESS,
            'Magento\Paypal\Block\PayflowExpress\ShortcutContainer' => PaypalConfig::METHOD_WPP_PE_EXPRESS,
            'Magento\Paypal\Block\Bml\Shortcut' => PaypalConfig::METHOD_WPP_EXPRESS,
            'Magento\Paypal\Block\WpsBml\Shortcut' => PaypalConfig::METHOD_WPS_EXPRESS,
            'Magento\Paypal\Block\Payflow\Bml\Shortcut' => PaypalConfig::METHOD_WPP_PE_EXPRESS
        ];
        foreach ($blocks as $blockInstanceName => $paymentMethodCode) {
            if (!$this->_paypalConfig->isMethodAvailable($paymentMethodCode)) {
                continue;
            }

            $params = [
                'shortcutValidator' => $this->_shortcutFactory->create($observer->getEvent()->getCheckoutSession()),
            ];
            if (!in_array('Bml', explode('\\', $blockInstanceName))) {
                $params['checkoutSession'] = $observer->getEvent()->getCheckoutSession();
            }

            // we believe it's \Magento\Framework\View\Element\Template
            $shortcut = $shortcutButtons->getLayout()->createBlock(
                $blockInstanceName,
                '',
                $params
            );
            $shortcut->setIsInCatalogProduct(
                $observer->getEvent()->getIsCatalogProduct()
            )->setShowOrPosition(
                $observer->getEvent()->getOrPosition()
            );
            $shortcutButtons->addShortcut($shortcut);
        }
    }

    /**
     * Update transaction with HTML representation of txn_id
     *
     * @param EventObserver $observer
     * @return void
     */
    public function observeHtmlTransactionId(EventObserver $observer)
    {
        /** @var \Magento\Sales\Model\Order\Payment\Transaction $transaction */
        $transaction = $observer->getDataObject();
        $order = $transaction->getOrder();
        $payment = $order->getPayment();
        $methodInstance = $payment->getMethodInstance();
        $paymentCode = $methodInstance->getCode();
        $transactionLink = $this->paypalData->getHtmlTransactionId($paymentCode, $transaction->getTxnId());
        $transaction->setData('html_txn_id', $transactionLink);
    }
}
