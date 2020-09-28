<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomerGraphQl\Model\Resolver;

use Magento\Framework\AuthorizationInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\LoginAsCustomerApi\Api\ConfigInterface as LoginAsCustomerConfig;
use Magento\LoginAsCustomerGraphQl\Model\LoginAsCustomer\CreateCustomerToken;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;

/**
 * Gets customer token
 */
class RequestCustomerToken implements ResolverInterface
{
    /**
     * @var LoginAsCustomerConfig
     */
    private LoginAsCustomerConfig $config;

    /**
     * @var AuthorizationInterface
     */
    private AuthorizationInterface $authorization;

    /**
     * @var CreateCustomerToken
     */
    private CreateCustomerToken $createCustomerToken;

    /**
     * RequestCustomerToken constructor.
     * @param AuthorizationInterface $authorization
     * @param LoginAsCustomerConfig $config
     * @param CreateCustomerToken $createCustomerToken
     */
    public function __construct(
        AuthorizationInterface $authorization,
        LoginAsCustomerConfig $config,
        CreateCustomerToken $createCustomerToken
    ) {
        $this->authorization = $authorization;
        $this->config = $config;
        $this->createCustomerToken = $createCustomerToken;
    }

    /**
     * Get Customer Token using email
     *
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return Value|mixed|void
     * @throws GraphQlAuthorizationException|GraphQlNoSuchEntityException|LocalizedException
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $isAllowedLogin = $this->authorization->isAllowed('Magento_LoginAsCustomer::login');
        $isEnabled = $this->config->isEnabled();

        /* Get input params */
        try {
            $args = $args['input'] ?: [];
        } catch (NoSuchEntityException $e) {
            throw new GraphQlNoSuchEntityException(__('Check input params.'));
        }

        if (empty(trim($args['customer_email'], " "))) {
            throw new GraphQlInputException(__('Specify the "customer email" value.'));
        }

        $this->validateUser($context);

        if (!$isAllowedLogin || !$isEnabled) {
            throw new GraphQlAuthorizationException(
                __('Login as Customer is disabled.')
            );
        }

        return $this->createCustomerToken->execute($args['customer_email']);
    }

    /**
     * Check if its an admin user
     *
     * @param ContextInterface $context
     * @throws GraphQlAuthorizationException
     */
    private function validateUser(ContextInterface $context): void
    {
        if ($context->getUserType() !== 2 || $context->getUserId() === 0) {
            throw new GraphQlAuthorizationException(__('The current customer isn\'t authorized.'));
        }
    }
}
