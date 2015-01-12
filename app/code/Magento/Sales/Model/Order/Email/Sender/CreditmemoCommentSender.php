<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order\Email\Sender;

use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Creditmemo;
use Magento\Sales\Model\Order\Email\Container\CreditmemoCommentIdentity;
use Magento\Sales\Model\Order\Email\Container\Template;
use Magento\Sales\Model\Order\Email\NotifySender;

class CreditmemoCommentSender extends NotifySender
{
    /**
     * @param Template $templateContainer
     * @param CreditmemoCommentIdentity $identityContainer
     * @param Order\Email\SenderBuilderFactory $senderBuilderFactory
     */
    public function __construct(
        Template $templateContainer,
        CreditmemoCommentIdentity $identityContainer,
        \Magento\Sales\Model\Order\Email\SenderBuilderFactory $senderBuilderFactory
    ) {
        parent::__construct($templateContainer, $identityContainer, $senderBuilderFactory);
    }

    /**
     * Send email to customer
     *
     * @param Creditmemo $creditmemo
     * @param bool $notify
     * @param string $comment
     * @return bool
     */
    public function send(Creditmemo $creditmemo, $notify = true, $comment = '')
    {
        $order = $creditmemo->getOrder();
        $this->templateContainer->setTemplateVars(
            [
                'order' => $order,
                'creditmemo' => $creditmemo,
                'comment' => $comment,
                'billing' => $order->getBillingAddress(),
                'store' => $order->getStore(),
            ]
        );

        return $this->checkAndSend($order, $notify);
    }
}
