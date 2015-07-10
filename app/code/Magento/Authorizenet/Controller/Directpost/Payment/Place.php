<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Authorizenet\Controller\Directpost\Payment;

use Magento\Authorizenet\Controller\Directpost\Payment;
use Magento\Authorizenet\Helper\DataFactory;
use Magento\Checkout\Model\Type\Onepage;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Object;
use Magento\Framework\Registry;
use Magento\Payment\Model\IframeConfigProvider;
use Magento\Quote\Api\CartManagementInterface;

class Place extends Payment
{
    /**
     * @var CartManagementInterface
     */
    protected $cartManagement;

    /**
     * @var ManagerInterface
     */
    protected $eventManager;

    /**
     * @param Context $context
     * @param Registry $coreRegistry
     * @param DataFactory $dataFactory
     * @param CartManagementInterface $cartManagement
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        DataFactory $dataFactory,
        CartManagementInterface $cartManagement
    ) {
        $this->eventManager = $context->getEventManager();
        $this->cartManagement = $cartManagement;
        parent::__construct($context, $coreRegistry, $dataFactory);
    }

    /**
     * Send request to authorize.net
     *
     * @return string
     */
    public function execute()
    {
        $paymentParam = $this->getRequest()->getParam('payment');
        $controller = $this->getRequest()->getParam('controller');

        if (isset($paymentParam['method'])) {
            $this->_getDirectPostSession()->setQuoteId($this->_getCheckout()->getQuote()->getId());
            $this->_getCheckout()->getQuote()->setCheckoutMethod($this->getCheckoutMethod());

            if ($controller == IframeConfigProvider::CHECKOUT_IDENTIFIER) {
                return $this->placeCheckoutOrder();
            }

            $params = $this->_objectManager->get(
                'Magento\Authorizenet\Helper\Data'
            )->getSaveOrderUrlParams(
                $controller
            );
            $this->_forward(
                $params['action'],
                $params['controller'],
                $params['module'],
                $this->getRequest()->getParams()
            );
        } else {
            $result = ['error_messages' => __('Please choose a payment method.'), 'goto_section' => 'payment'];
            $this->getResponse()->representJson($this->getJsonHelper()->jsonEncode($result));
        }
    }

    /**
     * Get quote checkout method
     *
     * @return string
     */
    protected function getCheckoutMethod()
    {
        $checkoutMethod = $this->_getCheckout()->getQuote()->getCheckoutMethod();

        if ($this->getCustomerSession()->isLoggedIn()) {
            $checkoutMethod = Onepage::METHOD_CUSTOMER;
        }

        if (!$checkoutMethod) {
            if ($this->getCheckoutHelper()->isAllowedGuestCheckout($this->_getCheckout()->getQuote())) {
                $checkoutMethod = Onepage::METHOD_GUEST;
            } else {
                $checkoutMethod = Onepage::METHOD_REGISTER;
            }
        }

        return $checkoutMethod;
    }

    /**
     * Place order for checkout flow
     *
     * @return string
     */
    protected function placeCheckoutOrder()
    {
        $result = new Object();
        try {
            $this->cartManagement->placeOrder($this->_getCheckout()->getQuote()->getId());
            $result->setData('success', true);
            $this->eventManager->dispatch(
                'checkout_directpost_placeOrder',
                [
                    'result' => $result,
                    'action' => $this
                ]
            );
        } catch (\Exception $exception) {
            $result->setData('error', true);
            $result->setData('error_messages', __('Cannot place order.'));
        }
        $this->getResponse()->representJson($this->getJsonHelper()->jsonEncode($result));
    }

    /**
     * @return \Magento\Customer\Model\Session
     */
    protected function getCustomerSession()
    {
        return $this->_objectManager->get('Magento\Customer\Model\Session');
    }

    /**
     * @return \Magento\Checkout\Helper\Data
     */
    protected function getCheckoutHelper()
    {
        return $this->_objectManager->get('Magento\Checkout\Helper\Data');
    }

    /**
     * @return \Magento\Framework\Json\Helper\Data
     */
    protected function getJsonHelper()
    {
        return $this->_objectManager->get('Magento\Framework\Json\Helper\Data');
    }
}
