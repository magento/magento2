<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\OrderCancellationGraphQl\Model\Validator;

use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;

/**
 * Ensure all conditions to cancel order are met
 */
class ValidateRequest
{
    /**
     * Ensure customer is authorized and the field is populated
     *
     * @param ContextInterface $context
     * @param array|null $input
     * @return void
     * @throws GraphQlInputException|GraphQlAuthorizationException
     */
    public function execute(
        ContextInterface $context,
        ?array $input,
    ): void {
        if ($context->getExtensionAttributes()->getIsCustomer() === false) {
            throw new GraphQlAuthorizationException(__('The current customer isn\'t authorized.'));
        }

        if (!is_array($input) || empty($input)) {
            throw new GraphQlInputException(
                __('CancelOrderInput is missing.')
            );
        }

        if (empty($input['order_id'])) {
            throw new GraphQlInputException(
                __(
                    'Required parameter "%field" is missing or incorrect.',
                    [
                        'field' => 'order_id'
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
}
