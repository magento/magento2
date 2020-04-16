<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerGraphQl\Model\Resolver;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Validator\EmailAddress as EmailValidator;

class ResetPassword implements ResolverInterface
{
    /**
     * @var AccountManagementInterface
     */
    private $customerAccountManagement;

    /**
     * @var EmailValidator
     */
    private $emailValidator;

    /**
     * RequestPasswordResetEmail constructor.
     *
     * @param AccountManagementInterface $customerAccountManagement
     * @param EmailValidator             $emailValidator
     */
    public function __construct(
        AccountManagementInterface $customerAccountManagement,
        EmailValidator $emailValidator
    ) {
        $this->customerAccountManagement = $customerAccountManagement;
        $this->emailValidator = $emailValidator;
    }

    /**
     * Reset old password and set new
     *
     * @param Field             $field
     * @param ContextInterface  $context
     * @param ResolveInfo       $info
     * @param array|null        $value
     * @param array|null        $args
     *
     * @return bool|Value|mixed
     *
     * @throws GraphQlInputException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if (empty($args['email'])) {
            throw new GraphQlInputException(__('Email must be specified'));
        }

        if (!$this->emailValidator->isValid($args['email'])) {
            throw new GraphQlInputException(__('Email is invalid'));
        }

        if (empty($args['resetPasswordToken'])) {
            throw new GraphQlInputException(__('resetPasswordToken must be specified'));
        }

        if (empty($args['newPassword'])) {
            throw new GraphQlInputException(__('newPassword must be specified'));
        }

        try {
            return $this->customerAccountManagement->resetPassword(
                $args['email'],
                $args['resetPasswordToken'],
                $args['newPassword']
            );
        } catch (LocalizedException $e) {
            throw new GraphQlInputException(__($e->getMessage()), $e);
        }
    }
}
