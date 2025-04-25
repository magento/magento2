<?php
/**
 * Copyright 2024 Adobe
 * All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Authorization\Test\Unit\Model;

use Magento\Authorization\Model\IdentityProvider;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\App\Backpressure\ContextInterface;
use Magento\Framework\Exception\RuntimeException;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Tests the IdentityProvider class
 */
class IdentityProviderTest extends TestCase
{
    /**
     * @var UserContextInterface|MockObject
     */
    private $userContext;

    /**
     * @var RemoteAddress|MockObject
     */
    private $remoteAddress;

    /**
     * @var IdentityProvider
     */
    private $model;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->userContext = $this->createMock(UserContextInterface::class);
        $this->remoteAddress = $this->createMock(RemoteAddress::class);
        $this->model = new IdentityProvider($this->userContext, $this->remoteAddress);
    }

    /**
     * Cases for identity provider.
     *
     * @return array
     */
    public static function getIdentityCases(): array
    {
        return [
            'empty-user-context' => [null, null, '127.0.0.1', ContextInterface::IDENTITY_TYPE_IP, '127.0.0.1'],
            'guest-user-context' => [
                UserContextInterface::USER_TYPE_GUEST,
                null,
                '127.0.0.1',
                ContextInterface::IDENTITY_TYPE_IP,
                '127.0.0.1'
            ],
            'admin-user-context' => [
                UserContextInterface::USER_TYPE_ADMIN,
                42,
                '127.0.0.1',
                ContextInterface::IDENTITY_TYPE_ADMIN,
                '42'
            ],
            'customer-user-context' => [
                UserContextInterface::USER_TYPE_CUSTOMER,
                42,
                '127.0.0.1',
                ContextInterface::IDENTITY_TYPE_CUSTOMER,
                '42'
            ],
        ];
    }

    /**
     * Verify identity provider.
     *
     * @param int|null $userType
     * @param int|null $userId
     * @param string $remoteAddr
     * @param int $expectedType
     * @param string $expectedIdentity
     * @return void
     * @dataProvider getIdentityCases
     */
    public function testFetchIdentity(
        ?int $userType,
        ?int $userId,
        string $remoteAddr,
        int $expectedType,
        string $expectedIdentity
    ): void {
        $this->userContext->method('getUserType')->willReturn($userType);
        $this->userContext->method('getUserId')->willReturn($userId);
        $this->remoteAddress->method('getRemoteAddress')->willReturn($remoteAddr);

        $this->assertEquals($expectedType, $this->model->fetchIdentityType());
        $this->assertEquals($expectedIdentity, $this->model->fetchIdentity());
    }

    /**
     * Tests fetching an identity type when user type can't be defined
     */
    public function testFetchIdentityTypeUserTypeNotDefined()
    {
        $this->userContext->method('getUserId')->willReturn(2);
        $this->userContext->method('getUserType')->willReturn(null);
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(__('User type not defined')->getText());
        $this->model->fetchIdentityType();
    }

    /**
     * Tests fetching an identity when user address can't be extracted
     */
    public function testFetchIdentityFailedToExtractRemoteAddress()
    {
        $this->userContext->method('getUserId')->willReturn(null);
        $this->remoteAddress->method('getRemoteAddress')->willReturn(false);
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(__('Failed to extract remote address')->getText());
        $this->model->fetchIdentity();
    }
}
