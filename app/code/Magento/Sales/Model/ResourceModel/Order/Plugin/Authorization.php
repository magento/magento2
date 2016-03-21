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
        \Magento\Authorization\Model\UserContextInterface $userContext
    ) {
        $this->userContext = $userContext;
    }

    /**
     * Checks if order is allowed
     *
     * @param \Magento\Sales\Model\ResourceModel\Order $subject
     * @param callable $proceed
     * @param \Magento\Framework\Model\AbstractModel $order
     * @param mixed $value
     * @param null|string $field
     * @return \Magento\Sales\Model\Order
     * @throws NoSuchEntityException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundLoad(
        \Magento\Sales\Model\ResourceModel\Order $subject,
        \Closure $proceed,
        \Magento\Framework\Model\AbstractModel $order,
        $value,
        $field = null
    ) {
        $result = $proceed($order, $value, $field);
        if (!$this->isAllowed($order)) {
            throw NoSuchEntityException::singleField('orderId', $order->getId());
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
