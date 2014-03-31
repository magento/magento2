<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Magento
 * @package     Magento_Paypal
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Paypal\Model;

use Magento\Event\Observer as EventObserver;

/**
 * PayPal module observer
 */
class Observer
{
    /**
     * Core registry
     *
     * @var \Magento\Registry
     */
    protected $_coreRegistry;

    /**
     * Paypal hss
     *
     * @var \Magento\Paypal\Helper\Hss
     */
    protected $_paypalHss;

    /**
     * Core data
     *
     * @var \Magento\Core\Helper\Data
     */
    protected $_coreData;

    /**
     * @var \Magento\Logger
     */
    protected $_logger;

    /**
     * @var \Magento\Paypal\Model\Report\SettlementFactory
     */
    protected $_settlementFactory;

    /**
     * @var \Magento\App\ViewInterface
     */
    protected $_view;

    /**
     * @var \Magento\AuthorizationInterface
     */
    protected $_authorization;

    /**
     * @var \Magento\Paypal\Model\Billing\AgreementFactory
     */
    protected $_agreementFactory;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @param \Magento\Core\Helper\Data $coreData
     * @param \Magento\Paypal\Helper\Hss $paypalHss
     * @param \Magento\Registry $coreRegistry
     * @param \Magento\Logger $logger
     * @param Report\SettlementFactory $settlementFactory
     * @param \Magento\App\ViewInterface $view
     * @param \Magento\AuthorizationInterface $authorization
     * @param \Magento\Paypal\Model\Billing\AgreementFactory $agreementFactory
     * @param \Magento\Checkout\Model\Session $checkoutSession
     */
    public function __construct(
        \Magento\Core\Helper\Data $coreData,
        \Magento\Paypal\Helper\Hss $paypalHss,
        \Magento\Registry $coreRegistry,
        \Magento\Logger $logger,
        \Magento\Paypal\Model\Report\SettlementFactory $settlementFactory,
        \Magento\App\ViewInterface $view,
        \Magento\AuthorizationInterface $authorization,
        \Magento\Paypal\Model\Billing\AgreementFactory $agreementFactory,
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        $this->_coreData = $coreData;
        $this->_paypalHss = $paypalHss;
        $this->_coreRegistry = $coreRegistry;
        $this->_logger = $logger;
        $this->_settlementFactory = $settlementFactory;
        $this->_view = $view;
        $this->_authorization = $authorization;
        $this->_agreementFactory = $agreementFactory;
        $this->_checkoutSession = $checkoutSession;
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
                    $this->_logger->logException($e);
                }
            }
        } catch (\Exception $e) {
            $this->_logger->logException($e);
        }
    }

    /**
     * Clean unfinished transaction
     *
     * @deprecated since 1.6.2.0
     * @return $this
     */
    public function cleanTransactions()
    {
        return $this;
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
                /* @var $controller \Magento\App\Action\Action */
                $controller = $observer->getEvent()->getData('controller_action');
                $result = $this->_coreData->jsonDecode($controller->getResponse()->getBody('default'));

                if (empty($result['error'])) {
                    $this->_view->loadLayout('checkout_onepage_review');
                    $html = $this->_view->getLayout()->getBlock('paypal.iframe')->toHtml();
                    $result['update_section'] = array('name' => 'paypaliframe', 'html' => $html);
                    $result['redirect'] = false;
                    $result['success'] = false;
                    $controller->getResponse()->clearHeader('Location');
                    $controller->getResponse()->setBody($this->_coreData->jsonEncode($result));
                }
            }
        }

        return $this;
    }

    /**
     * Block admin ability to use customer billing agreements
     *
     * @param EventObserver $observer
     * @return void
     */
    public function restrictAdminBillingAgreementUsage($observer)
    {
        $event = $observer->getEvent();
        $methodInstance = $event->getMethodInstance();
        if ($methodInstance instanceof \Magento\Paypal\Model\Payment\Method\Billing\AbstractAgreement &&
            false == $this->_authorization->isAllowed(
                'Magento_Paypal::use'
            )
        ) {
            $event->getResult()->isAvailable = false;
        }
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
                $message = __('We couldn\'t create a billing agreement for this order.');
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
        // PayPal Express Checkout
        $shortcut = $shortcutButtons->getLayout()->createBlock(
            'Magento\Paypal\Block\Express\Shortcut',
            '',
            array('checkoutSession' => $observer->getEvent()->getCheckoutSession())
        );
        $shortcut->setIsInCatalogProduct(
            $observer->getEvent()->getIsCatalogProduct()
        )->setShowOrPosition(
            $observer->getEvent()->getOrPosition()
        )->setTemplate(
            'express/shortcut.phtml'
        );
        $shortcutButtons->addShortcut($shortcut);
        // PayPal Express Checkout Payflow Edition
        $shortcut = $shortcutButtons->getLayout()->createBlock(
            'Magento\Paypal\Block\PayflowExpress\Shortcut',
            '',
            array('checkoutSession' => $observer->getEvent()->getCheckoutSession())
        );
        $shortcut->setIsInCatalogProduct(
            $observer->getEvent()->getIsCatalogProduct()
        )->setShowOrPosition(
            $observer->getEvent()->getOrPosition()
        )->setTemplate(
            'express/shortcut.phtml'
        );
        $shortcutButtons->addShortcut($shortcut);
    }
}
