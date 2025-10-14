<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerGraphQl\Model\Customer;

use Magento\CustomerGraphQl\Api\ValidateCustomerDataInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\Validator\EmailAddress as EmailAddressValidator;

/**
 * Customer data validation used during customer account creation and updating
 */
class ValidateCustomerData
{
    /**
     * @var EmailAddressValidator
     */
    private $emailAddressValidator;

    /**
     * @var ValidateCustomerDataInterface[]
     */
    private $validators = [];

    /**
     * ValidateCustomerData constructor.
     *
     * @param EmailAddressValidator $emailAddressValidator
     * @param array $validators
     */
    public function __construct(
        EmailAddressValidator $emailAddressValidator,
        $validators = []
    ) {
        $this->emailAddressValidator = $emailAddressValidator;
        $this->validators = $validators;
    }

    /**
     * Validate customer data
     *
     * @param array $customerData
     * @throws GraphQlInputException
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function execute(array $customerData)
    {
        /** @var ValidateCustomerDataInterface $validator */
        foreach ($this->validators as $validator) {
            $validator->execute($customerData);
        }
    }
}
