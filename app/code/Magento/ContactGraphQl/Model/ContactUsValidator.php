<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ContactGraphQl\Model;

use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\Validator\EmailAddress;

class ContactUsValidator
{
    /**
     * @var EmailAddress
     */
    private EmailAddress $emailValidator;

    /**
     * @param EmailAddress $emailValidator
     */
    public function __construct(
        EmailAddress $emailValidator
    ) {
        $this->emailValidator = $emailValidator;
    }

    /**
     * Validate input data
     *
     * @param string[] $input
     * @return void
     * @throws GraphQlInputException
     */
    public function execute(array $input): void
    {
        if (!$this->emailValidator->isValid($input['email'])) {
            throw new GraphQlInputException(
                __('The email address is invalid. Verify the email address and try again.')
            );
        }

        if ($input['name'] === '') {
            throw new GraphQlInputException(__('Name field is required.'));
        }

        if ($input['comment'] === '') {
            throw new GraphQlInputException(__('Comment field is required.'));
        }
    }
}
