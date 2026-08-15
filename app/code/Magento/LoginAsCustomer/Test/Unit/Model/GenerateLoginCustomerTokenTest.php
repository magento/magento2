<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomer\Test\Unit\Model;

use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Integration\Api\TokenManager;
use Magento\Integration\Model\CustomUserContext;
use Magento\Integration\Model\UserToken\UserTokenParameters;
use Magento\LoginAsCustomerApi\Api\ConfigInterface as LoginAsCustomerConfig;
use Magento\LoginAsCustomerApi\Api\Data\AuthenticationDataInterface;
use Magento\LoginAsCustomerApi\Api\GetAuthenticationDataBySecretInterface;
use Magento\LoginAsCustomerApi\Model\GenerateLoginCustomerToken;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for \Magento\LoginAsCustomerApi\Model\LoginAsCustomerTokenService
 */
class GenerateLoginCustomerTokenTest extends TestCase
{
    /** @var GetAuthenticationDataBySecretInterface|MockObject */
    private $getAuthenticationDataBySecret;

    /** @var LoginAsCustomerConfig|MockObject */
    private $loginAsCustomerConfig;

    /** @var TokenManager|MockObject */
    private $tokenManager;

    /** @var GenerateLoginCustomerToken */
    private $service;

    protected function setUp(): void
    {
        $this->getAuthenticationDataBySecret = $this->createMock(GetAuthenticationDataBySecretInterface::class);
        $this->loginAsCustomerConfig = $this->createMock(LoginAsCustomerConfig::class);
        $this->tokenManager = $this->createMock(TokenManager::class);

        $this->service = new GenerateLoginCustomerToken(
            $this->getAuthenticationDataBySecret,
            $this->loginAsCustomerConfig,
            $this->tokenManager
        );
    }

    public function testModuleDisabledThrowsException(): void
    {
        $this->loginAsCustomerConfig
            ->expects($this->once())
            ->method('isEnabled')
            ->willReturn(false);

        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('Login As Customer module is disabled.');

        $this->service->createCustomerAccessToken('secret123');
    }

    public function testInvalidSecretThrowsAuthenticationException(): void
    {
        $this->loginAsCustomerConfig
            ->expects($this->once())
            ->method('isEnabled')
            ->willReturn(true);

        $this->getAuthenticationDataBySecret
            ->expects($this->once())
            ->method('execute')
            ->willThrowException(new \Exception('Secret invalid.'));

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Invalid or expired secret.');

        $this->service->createCustomerAccessToken('invalid-secret');
    }

    public function testValidSecretReturnsToken(): void
    {
        $secret = 'valid-secret';
        $customerId = 42;
        $expectedToken = 'generated-token-xyz';

        $this->loginAsCustomerConfig
            ->expects($this->once())
            ->method('isEnabled')
            ->willReturn(true);

        $authenticationData = $this->createMock(AuthenticationDataInterface::class);
        $authenticationData
            ->expects($this->once())
            ->method('getCustomerId')
            ->willReturn($customerId);

        $this->getAuthenticationDataBySecret
            ->expects($this->once())
            ->method('execute')
            ->with($secret)
            ->willReturn($authenticationData);

        $tokenParams = $this->createMock(UserTokenParameters::class);
        $this->tokenManager
            ->expects($this->once())
            ->method('createUserTokenParameters')
            ->willReturn($tokenParams);

        $this->tokenManager
            ->expects($this->once())
            ->method('create')
            ->with(
                $this->callback(function ($context) use ($customerId) {
                    return $context instanceof CustomUserContext
                        && $context->getUserId() === $customerId
                        && $context->getUserType() === CustomUserContext::USER_TYPE_CUSTOMER;
                }),
                $tokenParams
            )
            ->willReturn($expectedToken);

        $result = $this->service->createCustomerAccessToken($secret);

        $this->assertSame($expectedToken, $result);
    }
}
