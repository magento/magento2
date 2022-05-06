<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AdminAdobeIms\Plugin;

use Magento\AdminAdobeIms\Service\ImsConfig;
use Magento\Framework\Validator\DataObject;
use Magento\User\Model\UserValidationRules;

class RemoveUserValidationRulesPlugin
{
    /** @var ImsConfig */
    private ImsConfig $adminImsConfig;

    /**
     * @param ImsConfig $adminImsConfig
     */
    public function __construct(
        ImsConfig $adminImsConfig
    ) {
        $this->adminImsConfig = $adminImsConfig;
    }

    /**
     * Remove password rule for validator
     *
     * @param UserValidationRules $subject
     * @param callable $proceed
     * @param DataObject $validator
     * @return DataObject
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundAddPasswordRules(
        UserValidationRules $subject,
        callable $proceed,
        DataObject $validator
    ): DataObject {
        if ($this->adminImsConfig->enabled() !== true) {
            return $proceed($validator);
        }

        return $validator;
    }

    /**
     * Remove password confirmation rule for validator
     *
     * @param UserValidationRules $subject
     * @param callable $proceed
     * @param DataObject $validator
     * @param string $passwordConfirmation
     * @return DataObject
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundAddPasswordConfirmationRule(
        UserValidationRules $subject,
        callable $proceed,
        DataObject $validator,
        string $passwordConfirmation
    ): DataObject {
        if ($this->adminImsConfig->enabled() !== true) {
            return $proceed($validator, $passwordConfirmation);
        }

        return $validator;
    }
}
