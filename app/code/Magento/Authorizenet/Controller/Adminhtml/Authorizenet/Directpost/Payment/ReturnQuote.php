<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Authorizenet\Controller\Adminhtml\Authorizenet\Directpost\Payment;

/**
 * Class \Magento\Authorizenet\Controller\Adminhtml\Authorizenet\Directpost\Payment\ReturnQuote
 *
 */
class ReturnQuote extends \Magento\Sales\Controller\Adminhtml\Order\Create
{
    /**
     * Return quote
     *
     * @return void
     */
    protected function _returnQuote()
    {
        $directpostSession = $this->_objectManager->get(\Magento\Authorizenet\Model\Directpost\Session::class);
        $incrementId = $directpostSession->getLastOrderIncrementId();
        if ($incrementId && $directpostSession->isCheckoutOrderIncrementIdExist($incrementId)) {
            /* @var $order \Magento\Sales\Model\Order */
            $order = $this->_objectManager->create(\Magento\Sales\Model\Order::class)->loadByIncrementId($incrementId);
            if ($order->getId()) {
                $directpostSession->removeCheckoutOrderIncrementId($order->getIncrementId());
            }
        }
    }

    /**
     * Return order quote by ajax
     *
     * @return void
     */
    public function execute()
    {
        $this->_returnQuote();
        $this->getResponse()->representJson(
            $this->_objectManager->get(\Magento\Framework\Json\Helper\Data::class)->jsonEncode(['success' => 1])
        );
    }
}
