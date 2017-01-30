<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Authorizenet\Controller\Directpost;

use Magento\Payment\Block\Transparent\Iframe;

/**
 * DirectPost Payment Controller
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
abstract class Payment extends \Magento\Framework\App\Action\Action
{
    /**
     * Core registry
     *
     * @deprecated
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Authorizenet\Helper\DataFactory
     */
    protected $dataFactory;

    /**
     * @var \Magento\Payment\Model\IframeService
     */
    private $iframeService;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Authorizenet\Helper\DataFactory $dataFactory
     * @param \Magento\Payment\Model\IframeService|null $iframeService
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Authorizenet\Helper\DataFactory $dataFactory,
        \Magento\Payment\Model\IframeService $iframeService = null
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->dataFactory = $dataFactory;
        $this->iframeService = $iframeService ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Payment\Model\IframeService::class);
        parent::__construct($context);
    }

    /**
     * @return \Magento\Checkout\Model\Session
     */
    protected function _getCheckout()
    {
        return $this->_objectManager->get(\Magento\Checkout\Model\Session::class);
    }

    /**
     * Get session model
     *
     * @return \Magento\Authorizenet\Model\Directpost\Session
     */
    protected function _getDirectPostSession()
    {
        return $this->_objectManager->get(\Magento\Authorizenet\Model\Directpost\Session::class);
    }

    /**
     * Response action.
     * Action for Authorize.net SIM Relay Request.
     *
     * @param string $area
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _responseAction($area = 'frontend')
    {
        $helper = $this->dataFactory->create($area);

        $params = [];
        $data = $this->getRequest()->getParams();

        $paymentMethod = $this->_objectManager->create(\Magento\Authorizenet\Model\Directpost::class);

        $result = [];
        if (!empty($data['x_invoice_num'])) {
            $result['x_invoice_num'] = $data['x_invoice_num'];
            $params['order_success'] = $helper->getSuccessOrderUrl($result);
        }

        try {
            if (!empty($data['store_id'])) {
                $paymentMethod->setStore($data['store_id']);
            }
            $paymentMethod->process($data);
            $result['success'] = 1;
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->_objectManager->get(\Psr\Log\LoggerInterface::class)->critical($e);
            $result['success'] = 0;
            $result['error_msg'] = $e->getMessage();
        } catch (\Exception $e) {
            $this->_objectManager->get(\Psr\Log\LoggerInterface::class)->critical($e);
            $result['success'] = 0;
            $result['error_msg'] = __('We can\'t process your order right now. Please try again later.');
        }

        if (!empty($data['controller_action_name'])
            && strpos($data['controller_action_name'], 'sales_order_') === false
        ) {
            if (!empty($data['key'])) {
                $result['key'] = $data['key'];
            }
            $result['controller_action_name'] = $data['controller_action_name'];
            $result['is_secure'] = isset($data['is_secure']) ? $data['is_secure'] : false;
            $params['redirect'] = $helper->getRedirectIframeUrl($result);
        }

        //registering response parameters for iframe content
        $this->iframeService->setParams($params);
    }

    /**
     * Return customer quote
     *
     * @param bool $cancelOrder
     * @param string $errorMsg
     * @return void
     */
    protected function _returnCustomerQuote($cancelOrder = false, $errorMsg = '')
    {
        $incrementId = $this->_getDirectPostSession()->getLastOrderIncrementId();
        if ($incrementId && $this->_getDirectPostSession()->isCheckoutOrderIncrementIdExist($incrementId)) {
            /* @var $order \Magento\Sales\Model\Order */
            $order = $this->_objectManager->create(\Magento\Sales\Model\Order::class)->loadByIncrementId($incrementId);
            if ($order->getId()) {
                try {
                    /** @var \Magento\Quote\Api\CartRepositoryInterface $quoteRepository */
                    $quoteRepository = $this->_objectManager->create(\Magento\Quote\Api\CartRepositoryInterface::class);
                    /** @var \Magento\Quote\Model\Quote $quote */
                    $quote = $quoteRepository->get($order->getQuoteId());

                    $quote->setIsActive(1)->setReservedOrderId(null);
                    $quoteRepository->save($quote);
                    $this->_getCheckout()->replaceQuote($quote);
                } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                }
                $this->_getDirectPostSession()->removeCheckoutOrderIncrementId($incrementId);
                $this->_getDirectPostSession()->unsetData('quote_id');
                if ($cancelOrder) {
                    $order->registerCancellation($errorMsg)->save();
                }
            }
        }
    }
}
