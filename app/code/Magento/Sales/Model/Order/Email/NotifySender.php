<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Model\Order\Email;

use Magento\Sales\Model\Order;

/**
 * Class NotifySender
 * phpcs:disable Magento2.Classes.AbstractApi
 * @api
 * @since 100.0.2
 */
abstract class NotifySender extends Sender
{
    /**
     * Send email to customer
     *
     * @param Order $order
     * @param bool $notify
     * @return bool
     */
    protected function checkAndSend(Order $order, $notify = true)
    {
        $this->identityContainer->setStore($order->getStore());
        if (!$this->identityContainer->isEnabled()) {
            return false;
        }
        $this->prepareTemplate($order);

        /** @var SenderBuilder $sender */
        $sender = $this->getSender();

        if ($notify) {
            $sender->send();
        } elseif ($this->identityContainer->getCopyMethod() === 'copy') {
            // Email copies are sent as separated emails if their copy method
            // is 'copy' or a customer should not be notified
            $sender->sendCopyTo();
        }

        return true;
    }
}
