<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CustomerGraphQl\Model\Resolver;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\State\InvalidTransitionException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Validator\EmailAddress as EmailValidator;

/**
 * Customer resend confirmation email, used for GraphQL request processing
 */
class ResendConfirmationEmail implements ResolverInterface
{
    /**
     * @param AccountManagementInterface $accountManagement
     * @param EmailValidator $emailValidator
     */
    public function __construct(
        private readonly AccountManagementInterface $accountManagement,
        private readonly EmailValidator $emailValidator,
    ) {
    }

    /**
     * Resend confirmation customer email mutation
     *
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return bool
     * @throws \Exception
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        ?array $value = null,
        ?array $args = null
    ) {
        if (!$this->emailValidator->isValid($args['email'])) {
            throw new GraphQlInputException(__('Email address is not valid'));
        }
        try {
            $this->accountManagement->resendConfirmation($args['email']);
        } catch (InvalidTransitionException $e) {
            throw new GraphQlInputException(__($e->getRawMessage()));
        } catch (NoSuchEntityException) {
            throw new GraphQlInputException(__('There is no user registered with that email address.'));
        } catch (\Exception) {
            throw new GraphQlInputException(__('There was an error when sending the confirmation email'));
        }
        return true;
    }
}
