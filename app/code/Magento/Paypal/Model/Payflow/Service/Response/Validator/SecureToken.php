<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Model\Payflow\Service\Response\Validator;

use Magento\Framework\DataObject;
use Magento\Paypal\Model\Payflow\Service\Response\ValidatorInterface;
use Magento\Paypal\Model\Payflow\Transparent;

/**
 * Class SecureToken
 * @since 2.0.0
 */
class SecureToken implements ValidatorInterface
{
    /**
     * Secure Token Error: Secure Token already been used
     */
    const ST_ALREADY_USED = 160;

    /**
     * Secure Token Error: Transaction using secure token is already in progress
     */
    const ST_TRANSACTION_IN_PROCESS = 161;

    /**
     * Secure Token Error: Secure Token Expired
     */
    const ST_EXPIRED = 162;

    /**
     * Validate data
     * @param DataObject $response
     * @param Transparent $transparentModel
     * @return bool
     * @since 2.0.0
     */
    public function validate(DataObject $response, Transparent $transparentModel)
    {
        return (bool) $response->getSecuretoken()
            && is_numeric($response->getResult())
            && !in_array(
                $response->getResult(),
                [
                    static::ST_ALREADY_USED,
                    static::ST_TRANSACTION_IN_PROCESS,
                    static::ST_EXPIRED,
                ]
            );
    }
}
