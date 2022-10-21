<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Model\Payflow\Service\Response\Validator;

use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Validator\Exception as ValidatorException;
use Magento\Paypal\Model\Payflow\Transparent;
use Magento\Paypal\Model\Payflowpro;
use Magento\Paypal\Model\Payflow\Service\Response\ValidatorInterface;

/**
 * Aggregate validator class for a payflow response
 */
class ResponseValidator implements ValidatorInterface
{
    /**
     * @var ValidatorInterface[]
     */
    protected $validators;

    /**
     * Constructor
     *
     * @param ValidatorInterface[] $validators
     */
    public function __construct(array $validators)
    {
        $this->validators = $validators;
    }

    /**
     * Validate data
     *
     * @param DataObject $response
     * @param Transparent $transparentModel
     * @return bool
     * @throws ValidatorException
     * @throws LocalizedException
     */
    public function validate(DataObject $response, Transparent $transparentModel)
    {
        switch ($response->getResult()) {
            case Payflowpro::RESPONSE_CODE_APPROVED:
            case Payflowpro::RESPONSE_CODE_FRAUDSERVICE_FILTER:
                foreach ($this->validators as $validator) {
                    if ($validator->validate($response, $transparentModel) === false) {
                        throw new ValidatorException(__('Transaction has been declined'));
                    }
                }
                break;
            case Payflowpro::RESPONSE_CODE_INVALID_AMOUNT:
                break;
            default:
                throw new ValidatorException(__('Transaction has been declined'));
        }
        return true;
    }
}
