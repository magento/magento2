<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\OrderCancellationGraphQl\Model;

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
     * @throws GraphQlAuthorizationException
     * @throws GraphQlInputException
     */
    public function execute(
        $context,
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

        if (!$input['order_id'] || (int)$input['order_id'] === 0) {
            throw new GraphQlInputException(
                __(
                    'Required parameter "%field" is missing or incorrect.',
                    [
                        'field' => 'order_id'
                    ]
                )
            );
        }

        if (!$input['reason'] || !is_string($input['reason']) || (string)$input['reason'] === "") {
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
