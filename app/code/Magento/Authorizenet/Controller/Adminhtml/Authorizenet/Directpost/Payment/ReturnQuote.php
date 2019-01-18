<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Authorizenet\Controller\Adminhtml\Authorizenet\Directpost\Payment;

/**
 * Class ReturnQuote
 * @package Magento\Authorizenet\Controller\Adminhtml\Authorizenet\Directpost\Payment
 * @deprecated 2.3.1 Authorize.net is removing all support for this payment method in July 2019
 */
class ReturnQuote extends \Magento\Sales\Controller\Adminhtml\Order\Create
{
    /**
     * Return quote
     *
     * @return void
     * @deprecated
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
     * @deprecated
     */
    public function execute()
    {
        $this->_returnQuote();
        $this->getResponse()->representJson(
            $this->_objectManager->get(\Magento\Framework\Json\Helper\Data::class)->jsonEncode(['success' => 1])
        );
    }
}
