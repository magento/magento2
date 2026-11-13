<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomer\Model;

use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Integration\Api\TokenManager;
use Magento\Integration\Model\CustomUserContext;
use Magento\LoginAsCustomerApi\Api\ConfigInterface as LoginAsCustomerConfig;
use Magento\LoginAsCustomerApi\Api\GetAuthenticationDataBySecretInterface;
use Magento\LoginAsCustomerApi\Api\GenerateLoginCustomerTokenInterface;

/**
 * @inheritdoc
 */
class GenerateLoginCustomerToken implements GenerateLoginCustomerTokenInterface
{
    public function __construct(
        private readonly GetAuthenticationDataBySecretInterface $getAuthenticationDataBySecret,
        private readonly LoginAsCustomerConfig $loginAsCustomerConfig,
        private readonly TokenManager $tokenManager,
    ) {
    }

    /**
     * {@inheritdoc}
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function createCustomerAccessToken(string $secret): string
    {
        if (!$this->loginAsCustomerConfig->isEnabled()) {
            throw new LocalizedException(__('Login As Customer module is disabled.'));
        }
        try {
            $authenticationData = $this->getAuthenticationDataBySecret->execute($secret);
            $customerId = $authenticationData->getCustomerId();
        } catch (\Exception $e) {
            throw new AuthenticationException(__('Invalid or expired secret.'));
        }
        $context = new CustomUserContext(
            (int)$customerId,
            CustomUserContext::USER_TYPE_CUSTOMER
        );
        $params = $this->tokenManager->createUserTokenParameters();
        return $this->tokenManager->create($context, $params);
    }
}
