<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Authorizenet\Controller\Directpost\Payment;

class Place extends \Magento\Authorizenet\Controller\Directpost\Payment
{
    /**
     * @var \Magento\Quote\Api\CartManagementInterface
     */
    protected $cartManagement;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Authorizenet\Helper\DataFactory $dataFactory
     * @param \Magento\Quote\Api\CartManagementInterface $cartManagement
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Authorizenet\Helper\DataFactory $dataFactory,
        \Magento\Quote\Api\CartManagementInterface $cartManagement
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

            if ($controller == \Magento\Payment\Model\IframeConfigProvider::CHECKOUT_IDENTIFIER) {
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
            $this->getResponse()->representJson(
                $this->_objectManager->get('Magento\Framework\Json\Helper\Data')->jsonEncode($result)
            );
        }
    }

    /**
     * Place order for checkout flow
     *
     * @return string
     */
    protected function placeCheckoutOrder()
    {
        $result = new \Magento\Framework\Object();
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
        $this->getResponse()->representJson(
            $this->_objectManager->get('Magento\Framework\Json\Helper\Data')->jsonEncode($result)
        );
    }
}
