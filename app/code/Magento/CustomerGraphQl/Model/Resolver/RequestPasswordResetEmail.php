<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerGraphQl\Model\Resolver;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Model\AccountManagement;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Validator\EmailAddress as EmailValidator;

/**
 * Class Resolver for RequestPasswordResetEmail
 */
class RequestPasswordResetEmail implements ResolverInterface
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
     * Send password email request
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

        try {
            return $this->customerAccountManagement->initiatePasswordReset(
                $args['email'],
                AccountManagement::EMAIL_RESET
            );
        } catch (LocalizedException $e) {
            throw new GraphQlInputException(__($e->getMessage()), $e);
        }
    }
}
