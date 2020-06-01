<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Braintree\Gateway\Validator;

use Braintree\Error\ErrorCollection;
use Braintree\Error\Validation;
use Braintree\Result\Error;
use Braintree\Result\Successful;
use Braintree\Transaction;

/**
 * Processes errors codes from Braintree response.
 *
 * @deprecated Starting from Magento 2.3.6 Braintree payment method core integration is deprecated
 * in favor of official payment integration available on the marketplace
 */
class ErrorCodeProvider
{
    /**
     * Retrieves list of error codes from Braintree response.
     *
     * @param Successful|Error $response
     * @return array
     */
    public function getErrorCodes($response): array
    {
        $result = [];
        if (!$response instanceof Error) {
            return $result;
        }

        /** @var ErrorCollection $collection */
        $collection = $response->errors;

        /** @var Validation $error */
        foreach ($collection->deepAll() as $error) {
            $result[] = $error->code;
        }

        if (isset($response->transaction) && $response->transaction) {
            if ($response->transaction->status === Transaction::GATEWAY_REJECTED) {
                $result[] = $response->transaction->gatewayRejectionReason;
            }

            if ($response->transaction->status === Transaction::PROCESSOR_DECLINED) {
                $result[] = $response->transaction->processorResponseCode;
            }
        }

        return $result;
    }
}
