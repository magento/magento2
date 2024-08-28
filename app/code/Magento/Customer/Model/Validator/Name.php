<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Model\Validator;

use Magento\Customer\Model\Customer;
use Magento\Framework\Validator\AbstractValidator;
use Magento\Framework\Validator\GlobalNameValidator;

/**
 * Customer name fields validator.
 */
class Name extends AbstractValidator
{
    /**
     * @var GlobalNameValidator
     */
    private $nameValidator;

    /**
     * Name constructor.
     *
     * @param GlobalNameValidator $nameValidator
     */
    public function __construct(GlobalNameValidator $nameValidator)
    {
        $this->nameValidator = $nameValidator;
    }

    /**
     * Validate name fields.
     *
     * @param Customer $customer
     * @return bool
     */
    public function isValid($customer)
    {
        if (!$this->nameValidator->isValidName($customer->getFirstname())) {
            parent::_addMessages([['firstname' => __('First Name is not valid!')]]);
        }

        if (!$this->nameValidator->isValidName($customer->getLastname())) {
            parent::_addMessages([['lastname' => __('Last Name is not valid!')]]);
        }

        if (!$this->nameValidator->isValidName($customer->getMiddlename())) {
            parent::_addMessages([['middlename' => __('Middle Name is not valid!')]]);
        }

        return count($this->_messages) == 0;
    }
}
