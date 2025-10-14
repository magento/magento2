<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerGraphQl\Model\Resolver;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\AuthenticationInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Validator\EmailAddress as EmailValidator;

/**
 * Class Resolver for ResetPassword
 */
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
     * @var AuthenticationInterface
     */
    private $authentication;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * ResetPassword constructor.
     *
     * @param AuthenticationInterface     $authentication
     * @param CustomerRepositoryInterface $customerRepository
     * @param AccountManagementInterface  $customerAccountManagement
     * @param EmailValidator              $emailValidator
     */
    public function __construct(
        AuthenticationInterface $authentication,
        CustomerRepositoryInterface $customerRepository,
        AccountManagementInterface $customerAccountManagement,
        EmailValidator $emailValidator
    ) {
        $this->authentication = $authentication;
        $this->customerRepository = $customerRepository;
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
        ?array $value = null,
        ?array $args = null
    ) {
        if (empty($args['email'])) {
            throw new GraphQlInputException(__('You must specify an email address.'));
        }

        if (!$this->emailValidator->isValid($args['email'])) {
            throw new GraphQlInputException(__('The email address has an invalid format.'));
        }

        if (empty($args['resetPasswordToken'])) {
            throw new GraphQlInputException(__('resetPasswordToken must be specified'));
        }

        if (empty($args['newPassword'])) {
            throw new GraphQlInputException(__('newPassword must be specified'));
        }

        try {
            $customer = $this->customerRepository->get($args['email']);
        } catch (LocalizedException $e) {
            throw new GraphQlInputException(__('Cannot set the customer\'s password'), $e);
        }

        if (true === $this->authentication->isLocked($customer->getId())) {
            throw new GraphQlInputException(__('The account is locked'));
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
