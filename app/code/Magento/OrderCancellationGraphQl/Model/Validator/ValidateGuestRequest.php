<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\OrderCancellationGraphQl\Model\Validator;

use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Sales\Api\Data\OrderInterface;

/**
 * Ensure all conditions to cancel guest order are met
 */
class ValidateGuestRequest
{
    /**
     * Ensure the input to cancel guest order is valid
     *
     * @param mixed $input
     * @return void
     * @throws GraphQlInputException
     */
    public function validateInput(mixed $input): void
    {
        if (!is_array($input) || empty($input)) {
            throw new GraphQlInputException(
                __('GuestOrderCancelInput is missing.')
            );
        }

        if (!$input['token'] || !is_string($input['token'])) {
            throw new GraphQlInputException(
                __(
                    'Required parameter "%field" is missing or incorrect.',
                    [
                        'field' => 'token'
                    ]
                )
            );
        }

        if (!$input['reason'] || !is_string($input['reason'])) {
            throw new GraphQlInputException(
                __(
                    'Required parameter "%field" is missing or incorrect.',
                    [
                        'field' => 'reason'
                    ]
                )
            );
        }
    }

    /**
     * Ensure the order matches the provided criteria
     *
     * @param OrderInterface $order
     * @param string $lastname
     * @param string $email
     * @return void
     * @throws GraphQlAuthorizationException
     * @throws GraphQlNoSuchEntityException
     */
    public function validateOrderDetails(OrderInterface $order, string $lastname, string $email): void
    {
        $billingAddress = $order->getBillingAddress();

        if ($billingAddress->getLastname() !== $lastname || $billingAddress->getEmail() !== $email) {
            $this->cannotLocateOrder();
        }

        if ($order->getCustomerId()) {
            throw new GraphQlAuthorizationException(__('Please login to view the order.'));
        }
    }

    /**
     * Throw exception when the order cannot be found or does not match the criteria
     *
     * @return void
     * @throws GraphQlNoSuchEntityException
     */
    public function cannotLocateOrder(): void
    {
        throw new GraphQlNoSuchEntityException(__('We couldn\'t locate an order with the information provided.'));
    }
}
