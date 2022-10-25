<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Quote\Plugin;

use Magento\Framework\Event\Observer;
use Magento\Framework\Webapi\Rest\Request as RestRequest;
use Magento\Quote\Observer\SubmitObserver;
use Magento\Sales\Model\Order;

/**
 * Send admin order confirmation
 */
class SendOrderNotification
{
    /**
     * @var RestRequest $request
     */
    private RestRequest $request;

    /**
     * @param RestRequest $request
     */
    public function __construct(RestRequest $request)
    {
        $this->request = $request;
    }

    /**
     * Adjusts order flag for confirmation email delivery
     *
     * @param SubmitObserver $subject
     * @param Observer $observer
     * @return Observer[]
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeExecute(SubmitObserver $subject, Observer $observer): array
    {
        /** @var  Order $order */
        $order = $observer->getEvent()->getOrder();
        $requestInfo = $this->request->getPostValue('order');
        $order->setCanSendNewEmailFlag((bool)($requestInfo['send_confirmation'] ?? 0));

        return [$observer];
    }
}
