<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CustomerGraphQl\Model\Resolver;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\CustomerGraphQl\Model\Customer\ExtractCustomerData;
use Magento\Framework\Exception\State\InputMismatchException;
use Magento\Framework\Exception\State\InvalidTransitionException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Validator\EmailAddress as EmailValidator;

/**
 * Customer email confirmation, used for GraphQL request processing
 */
class ConfirmEmail implements ResolverInterface
{
    /**
     * @param AccountManagementInterface $accountManagement
     * @param EmailValidator $emailValidator
     * @param ExtractCustomerData $extractCustomerData
     */
    public function __construct(
        private readonly AccountManagementInterface $accountManagement,
        private readonly EmailValidator $emailValidator,
        private readonly ExtractCustomerData $extractCustomerData
    ) {
    }

    /**
     * Confirm customer email mutation
     *
     * @param \Magento\Framework\GraphQl\Config\Element\Field $field
     * @param \Magento\Framework\GraphQl\Query\Resolver\ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return array|Value
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
        if (!$this->emailValidator->isValid($args['input']['email'])) {
            throw new GraphQlInputException(__('Email is invalid'));
        }
        try {
            $customer = $this->accountManagement->activate($args['input']['email'], $args['input']['confirmation_key']);
        } catch (InvalidTransitionException | InputMismatchException $e) {
            throw new GraphQlInputException(__($e->getRawMessage()));
        } catch (StateException) {
            throw new GraphQlInputException(__('This confirmation key is invalid or has expired.'));
        } catch (\Exception) {
            throw new GraphQlInputException(__('There was an error confirming the account'));
        }
        return ['customer' => $this->extractCustomerData->execute($customer)];
    }
}
