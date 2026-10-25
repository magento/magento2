<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\OrderCancellationGraphQl\Model\Validator;

use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\OrderCancellation\Model\GetConfirmationKey;

/**
 * Ensure all conditions to cancel guest order are met
 */
class ValidateConfirmRequest
{
    /**
     * Ensure the input to cancel guest order is valid
     *
     * @param mixed $input
     * @return void
     * @throws GraphQlInputException
     */
    public function execute(mixed $input): void
    {
        if (!is_array($input) || empty($input)) {
            throw new GraphQlInputException(
                __('ConfirmCancelOrderInput is missing.')
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

        if (!$input['confirmation_key'] ||
            !is_string($input['confirmation_key']) ||
            strlen($input['confirmation_key']) !== GetConfirmationKey::CONFIRMATION_KEY_LENGTH
        ) {
            throw new GraphQlInputException(
                __(
                    'Required parameter "%field" is missing or incorrect.',
                    [
                        'field' => 'confirmation_key'
                    ]
                )
            );
        }
    }
}
