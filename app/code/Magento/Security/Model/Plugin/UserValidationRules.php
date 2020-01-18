<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Security\Model\Plugin;

/**
 * \Magento\User\Model\UserValidationRules decorator
 */
class UserValidationRules
{
    /**@var \Magento\Security\Model\UserExpiration\Validator */
    private $validator;

    /**
     * UserValidationRules constructor.
     *
     * @param \Magento\Security\Model\UserExpiration\Validator $validator
     */
    public function __construct(\Magento\Security\Model\UserExpiration\Validator $validator)
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
