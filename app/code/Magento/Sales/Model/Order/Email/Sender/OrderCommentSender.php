<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order\Email\Sender;

use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Container\OrderCommentIdentity;
use Magento\Sales\Model\Order\Email\Container\Template;
use Magento\Sales\Model\Order\Email\NotifySender;

class OrderCommentSender extends NotifySender
{
    /**
     * @param Template $templateContainer
     * @param OrderCommentIdentity $identityContainer
     * @param Order\Email\SenderBuilderFactory $senderBuilderFactory
     */
    public function __construct(
        Template $templateContainer,
        OrderCommentIdentity $identityContainer,
        \Magento\Sales\Model\Order\Email\SenderBuilderFactory $senderBuilderFactory
    ) {
        parent::__construct($templateContainer, $identityContainer, $senderBuilderFactory);
    }

    /**
     * Send email to customer
     *
     * @param Order $order
     * @param bool $notify
     * @param string $comment
     * @return bool
     */
    public function send(Order $order, $notify = true, $comment = '')
    {
        $this->templateContainer->setTemplateVars(
            [
                'order' => $order,
                'comment' => $comment,
                'billing' => $order->getBillingAddress(),
                'store' => $order->getStore(),
            ]
        );
        return $this->checkAndSend($order, $notify);
    }
}
