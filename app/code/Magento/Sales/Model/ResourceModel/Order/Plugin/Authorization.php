<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Model\ResourceModel\Order\Plugin;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\ResourceModel\Order as ResourceOrder;

class Authorization
{
    /**
     * @var UserContextInterface
     */
    protected $userContext;

    /**
     * @param UserContextInterface $userContext
     */
    public function __construct(
        UserContextInterface $userContext
    ) {
        $this->userContext = $userContext;
    }

    /**
     * @param ResourceOrder $subject
     * @param ResourceOrder $result
     * @param \Magento\Framework\Model\AbstractModel $order
     * @return ResourceOrder
     * @throws NoSuchEntityException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterLoad(
        ResourceOrder $subject,
        ResourceOrder $result,
        \Magento\Framework\Model\AbstractModel $order
    ) {
        if ($order instanceof Order) {
            if (!$this->isAllowed($order)) {
                throw NoSuchEntityException::singleField('orderId', $order->getId());
            }
        }
        return $result;
    }

    /**
     * Checks if order is allowed for current customer
     *
     * @param \Magento\Sales\Model\Order $order
     * @return bool
     */
    protected function isAllowed(Order $order)
    {
        return $this->userContext->getUserType() == UserContextInterface::USER_TYPE_CUSTOMER
            ? $order->getCustomerId() == $this->userContext->getUserId()
            : true;
    }
}
