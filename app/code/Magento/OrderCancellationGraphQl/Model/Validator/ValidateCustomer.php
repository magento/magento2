<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 *
 * NOTICE: All information contained herein is, and remains
 * the property of Adobe and its suppliers, if any. The intellectual
 * and technical concepts contained herein are proprietary to Adobe
 * and its suppliers and are protected by all applicable intellectual
 * property laws, including trade secret and copyright laws.
 * Dissemination of this information or reproduction of this material
 * is strictly forbidden unless prior written permission is obtained
 * from Adobe.
 */
declare(strict_types=1);

namespace Magento\OrderCancellationGraphQl\Model\Validator;

use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Sales\Api\Data\OrderInterface;

class ValidateCustomer
{
    /**
     * Validate customer data
     *
     * @param OrderInterface $order
     * @param ContextInterface $context
     * @return void
     * @throws GraphQlAuthorizationException
     */
    public function execute(OrderInterface $order, ContextInterface $context): void
    {
        if ($context->getExtensionAttributes()->getIsCustomer() === false) {
            throw new GraphQlAuthorizationException(__('The current customer isn\'t authorized.'));
        }

        if ((int)$order->getCustomerId() !== $context->getUserId()) {
            throw new GraphQlAuthorizationException(__('Current user is not authorized to cancel this order'));
        }
    }
}
