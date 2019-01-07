<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AuthorizenetAcceptjs\Gateway\Validator;

/**
 * Processes errors codes from response.
 */
class ErrorCodeProvider
{
    /**
     * Retrieves list of error codes from response.
     *
     * @param array $response
     * @return array
     */
    public function getErrorCodes(array $response): array
    {
        $result = [];

        return $result;
    }
}
