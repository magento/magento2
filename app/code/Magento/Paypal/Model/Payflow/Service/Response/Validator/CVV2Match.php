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
 * Class CVV2Match
 */
class CVV2Match implements ValidatorInterface
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
     * @param DataObject $response
     * @param Transparent $transparentModel
     * @return bool
     */
    public function validate(DataObject $response, Transparent $transparentModel)
    {
        if ($transparentModel->getConfig()->getValue(static::CONFIG_NAME) === static::CONFIG_OFF) {
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
     * Matching card CVV (positive)
     *
     * @param DataObject $response
     * @return bool
     */
    protected function isMatchCvv(DataObject $response)
    {
        $cvvMatch = strtolower((string) $response->getData(static::CVV2MATCH));
        return $cvvMatch === static::RESPONSE_YES || $cvvMatch === static::RESPONSE_NOT_SUPPORTED;
    }

    /**
     * Matching card CVV (negative)
     *
     * @param DataObject $response
     * @return bool
     */
    protected function isNotMatchCvv(DataObject $response)
    {
        return strtolower((string) $response->getData(static::CVV2MATCH)) === static::RESPONSE_NO;
    }

    /**
     * Checking that the CVV does not exist in the response
     *
     * @param DataObject $response
     * @return bool
     */
    protected function isCvvDoNotExists(DataObject $response)
    {
        return $response->getData(static::CVV2MATCH) == '';
    }
}
