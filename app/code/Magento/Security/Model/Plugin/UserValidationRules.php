<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Security\Model\Plugin;

use Magento\Security\Model\UserExpiration\Validator;

/**
 * \Magento\User\Model\UserValidationRules decorator
 */
class UserValidationRules
{
    /**@var Validator */
    private $validator;

    /**
     * UserValidationRules constructor.
     *
     * @param Validator $validator
     */
    public function __construct(Validator $validator)
    {
        $this->validator = $validator;
    }

    /**
     * Add the Expires At validator to user validation rules.
     *
     * @param \Magento\User\Model\UserValidationRules $userValidationRules
     * @param \Magento\Framework\Validator\DataObject $result
     * @return \Magento\Framework\Validator\DataObject
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterAddUserInfoRules(\Magento\User\Model\UserValidationRules $userValidationRules, $result)
    {
        return $result->addRule($this->validator, 'expires_at');
    }
}
