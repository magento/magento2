<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Security\Model\Plugin;

use Magento\Framework\Validator\DataObject as ValidatorDataObject;
use Magento\Security\Model\UserExpiration\Validator;
use Magento\User\Model\UserValidationRules as ModelUserValidationRules;

/**
 * \Magento\User\Model\UserValidationRules decorator
 */
class UserValidationRules
{
    /**
     * UserValidationRules constructor.
     *
     * @param Validator $validator
     */
    public function __construct(
        private readonly Validator $validator
    ) {
    }

    /**
     * Add the Expires At validator to user validation rules.
     *
     * @param ModelUserValidationRules $userValidationRules
     * @param ValidatorDataObject $result
     * @return ValidatorDataObject
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterAddUserInfoRules(ModelUserValidationRules $userValidationRules, $result)
    {
        return $result->addRule($this->validator, 'expires_at');
    }
}
