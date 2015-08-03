<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Model\Payflow\Service\Response\Validator;

use Magento\Framework\Object;
use Magento\Paypal\Model\Payflow\Service\Response\ValidatorInterface;

/**
 * Class CVV2Match
 */
class CVV2Match extends AbstractFilterValidator implements ValidatorInterface
{
    /**
     * Result of the card security code (CVV2) check
     */
    const CVV2MATCH = 'cvv2match';

    /**
     * This field returns the transaction amount, or if performing a partial authorization,
     * the amount approved for the partial authorization.
     */
    const AMT = 'amt';

    /**
     * Message if validation fail
     */
    const ERROR_MESSAGE = 'Card security code does not match.';

    /** Values of the response */
    const RESPONSE_YES = 'y';

    const RESPONSE_NO = 'n';

    const RESPONSE_NOT_SUPPORTED = 'x';
    /**  */

    /** Validation settings payments */
    const CONFIG_ON = 1;

    const CONFIG_OFF = 0;

    const CONFIG_NAME = 'avs_security_code';
    /**  */

    /**
     * Validate data
     *
     * @param Object $response
     * @return bool
     */
    public function validate(Object $response)
    {
        if ($this->isValidationOff()) {
            return true;
        }

        if ($this->isMatchCvv($response)) {
            return true;
        }

        if ($this->isNotMatchCvv($response)) {
            $response->setRespmsg(static::ERROR_MESSAGE);
            return false;
        }

        if ($this->isCvvDoNotExists($response)) {
            return true;
        }

        $response->setRespmsg(static::ERROR_MESSAGE);
        return false;
    }

    /**
     * Check whether validation is enabled
     *
     * @return bool
     */
    protected function isValidationOff()
    {
        return $this->getConfig()->getValue(static::CONFIG_NAME) == static::CONFIG_OFF;
    }

    /**
     * Matching card CVV (positive)
     *
     * @param Object $response
     * @return bool
     */
    protected function isMatchCvv(Object $response)
    {
        $cvvMatch = strtolower((string) $response->getData(static::CVV2MATCH));
        return $cvvMatch === static::RESPONSE_YES || $cvvMatch === static::RESPONSE_NOT_SUPPORTED;
    }

    /**
     * Matching card CVV (negative)
     *
     * @param Object $response
     * @return bool
     */
    protected function isNotMatchCvv(Object $response)
    {
        return strtolower((string) $response->getData(static::CVV2MATCH)) === static::RESPONSE_NO;
    }

    /**
     * Checking that the CVV does not exist in the response
     *
     * @param Object $response
     * @return bool
     */
    protected function isCvvDoNotExists(Object $response)
    {
        return $response->getData(static::CVV2MATCH) == '';
    }
}
