<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Authorizenet\Controller\Adminhtml\Authorizenet\Directpost\Payment;

use Magento\Backend\App\Action;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Framework\View\Result\LayoutFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Payment\Block\Transparent\Iframe;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Redirect extends \Magento\Sales\Controller\Adminhtml\Order\Create
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var LayoutFactory
     */
    protected $resultLayoutFactory;

    /**
     * @var \Magento\Authorizenet\Helper\Backend\Data
     */
    protected $helper;

    /**
     * @param Action\Context $context
     * @param \Magento\Catalog\Helper\Product $productHelper
     * @param \Magento\Framework\Escaper $escaper
     * @param PageFactory $resultPageFactory
     * @param ForwardFactory $resultForwardFactory
     * @param \Magento\Framework\Registry $coreRegistry
     * @param LayoutFactory $resultLayoutFactory
     * @param \Magento\Authorizenet\Helper\Backend\Data $helper
     */
    public function __construct(
        Action\Context $context,
        \Magento\Catalog\Helper\Product $productHelper,
        \Magento\Framework\Escaper $escaper,
        PageFactory $resultPageFactory,
        ForwardFactory $resultForwardFactory,
        \Magento\Framework\Registry $coreRegistry,
        LayoutFactory $resultLayoutFactory,
        \Magento\Authorizenet\Helper\Backend\Data $helper
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->resultLayoutFactory = $resultLayoutFactory;
        $this->helper = $helper;
        parent::__construct(
            $context,
            $productHelper,
            $escaper,
            $resultPageFactory,
            $resultForwardFactory
        );
    }

    /**
     * Return quote
     *
     * @param bool $cancelOrder
     * @param string $errorMsg
     * @return void
     */
    protected function _returnQuote($cancelOrder, $errorMsg)
    {
        $directpostSession = $this->_objectManager->get(\Magento\Authorizenet\Model\Directpost\Session::class);
        $incrementId = $directpostSession->getLastOrderIncrementId();
        if ($incrementId && $directpostSession->isCheckoutOrderIncrementIdExist($incrementId)) {
            /* @var $order \Magento\Sales\Model\Order */
            $order = $this->_objectManager->create(\Magento\Sales\Model\Order::class)->loadByIncrementId($incrementId);
            if ($order->getId()) {
                $directpostSession->removeCheckoutOrderIncrementId($order->getIncrementId());
                if ($cancelOrder && $order->getState() == \Magento\Sales\Model\Order::STATE_PENDING_PAYMENT) {
                    $order->registerCancellation($errorMsg)->save();
                }
            }
        }
    }

    /**
     * Retrieve params and put javascript into iframe
     *
     * @return \Magento\Framework\View\Result\Layout
     */
    public function execute()
    {
        $redirectParams = $this->getRequest()->getParams();
        $params = [];
        if (!empty($redirectParams['success'])
            && isset($redirectParams['x_invoice_num'])
            && isset($redirectParams['controller_action_name'])
        ) {
            $params['redirect_parent'] = $this->helper->getSuccessOrderUrl($redirectParams);
            $directpostSession = $this->_objectManager->get(\Magento\Authorizenet\Model\Directpost\Session::class);
            $directpostSession->unsetData('quote_id');
            //cancel old order
            $oldOrder = $this->_getOrderCreateModel()->getSession()->getOrder();
            if ($oldOrder->getId()) {
                /* @var $order \Magento\Sales\Model\Order */
                $order = $this->_objectManager->create(\Magento\Sales\Model\Order::class)
                    ->loadByIncrementId($redirectParams['x_invoice_num']);

                if ($order->getId()) {
                    $oldOrder->cancel()->save();
                    $order->save();
                    $this->_getOrderCreateModel()->getSession()->unsOrderId();
                }
            }
            //clear sessions
            $this->_getSession()->clearStorage();
            $directpostSession->removeCheckoutOrderIncrementId($redirectParams['x_invoice_num']);
            $this->_objectManager->get(\Magento\Backend\Model\Session::class)->clearStorage();
            $this->messageManager->addSuccess(__('You created the order.'));
        }

        if (!empty($redirectParams['error_msg'])) {
            $cancelOrder = empty($redirectParams['x_invoice_num']);
            $this->_returnQuote($cancelOrder, $redirectParams['error_msg']);
        }

        $this->_objectManager->get(\Magento\Payment\Model\IframeService::class)->setParams(
            array_merge($params, $redirectParams)
        );
        return $this->resultLayoutFactory->create();
    }
}
