<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Model\ResourceModel\Order\Plugin;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\Exception\NoSuchEntityException;

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
     * @param \Magento\Sales\Model\ResourceModel\Order $subject
     * @param \Magento\Sales\Model\ResourceModel\Order $result
     * @param \Magento\Framework\Model\AbstractModel $order
     * @return \Magento\Sales\Model\ResourceModel\Order
     * @throws NoSuchEntityException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterLoad(
        \Magento\Sales\Model\ResourceModel\Order $subject,
        \Magento\Sales\Model\ResourceModel\Order $result,
        \Magento\Framework\Model\AbstractModel $order
    ) {
        if ($order instanceof \Magento\Sales\Model\Order) {
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
    protected function isAllowed(\Magento\Sales\Model\Order $order)
    {
        return $this->userContext->getUserType() == UserContextInterface::USER_TYPE_CUSTOMER
            ? $order->getCustomerId() == $this->userContext->getUserId()
            : true;
    }
}
