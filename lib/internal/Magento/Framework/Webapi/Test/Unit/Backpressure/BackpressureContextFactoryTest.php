<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\Webapi\Test\Unit\Backpressure;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\App\Backpressure\ContextInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Magento\Framework\Webapi\Backpressure\BackpressureContextFactory;
use Magento\Framework\Webapi\Backpressure\BackpressureRequestTypeExtractorInterface;
use Magento\Framework\Webapi\Backpressure\RestContext;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class BackpressureContextFactoryTest extends TestCase
{
    /**
     * @var RequestInterface|MockObject
     */
    private $request;

    /**
     * @var UserContextInterface|MockObject
     */
    private $userContext;

    /**
     * @var RemoteAddress|MockObject
     */
    private $remoteAddress;

    /**
     * @var BackpressureRequestTypeExtractorInterface|MockObject
     */
    private $requestTypeExtractor;

    /**
     * @var BackpressureContextFactory
     */
    private $model;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->request = $this->createMock(RequestInterface::class);
        $this->userContext = $this->createMock(UserContextInterface::class);
        $this->remoteAddress = $this->createMock(RemoteAddress::class);
        $this->requestTypeExtractor = $this->createMock(BackpressureRequestTypeExtractorInterface::class);

        $this->model = new BackpressureContextFactory(
            $this->request,
            $this->userContext,
            $this->remoteAddress,
            $this->requestTypeExtractor
        );
    }

    /**
     * Verify that no context is available for empty request type.
     *
     * @return void
     */
    public function testCreateForEmptyTypeReturnNull(): void
    {
        $this->requestTypeExtractor->method('extract')->willReturn(null);

        $this->assertNull($this->model->create('SomeService', 'method', '/api/route'));
    }

    /**
     * Different identities.
     *
     * @return array
     */
    public function getIdentityCases(): array
    {
        return [
            'guest' => [
                UserContextInterface::USER_TYPE_GUEST,
                null,
                '127.0.0.1',
                ContextInterface::IDENTITY_TYPE_IP,
                '127.0.0.1'
            ],
            'guest2' => [
                null,
                null,
                '127.0.0.1',
                ContextInterface::IDENTITY_TYPE_IP,
                '127.0.0.1'
            ],
            'customer' => [
                UserContextInterface::USER_TYPE_CUSTOMER,
                42,
                '127.0.0.1',
                ContextInterface::IDENTITY_TYPE_CUSTOMER,
                '42'
            ],
            'admin' => [
                UserContextInterface::USER_TYPE_ADMIN,
                42,
                '127.0.0.1',
                ContextInterface::IDENTITY_TYPE_ADMIN,
                '42'
            ]
        ];
    }

    /**
     * Verify that identity is created for customers.
     *
     * @return void
     * @dataProvider getIdentityCases
     */
    public function testCreateForIdentity(
        ?int $userType,
        ?int $userId,
        string $address,
        int $identityType,
        string $identity
    ): void {
        $this->requestTypeExtractor->method('extract')->willReturn($typeId = 'test');
        $this->userContext->method('getUserType')->willReturn($userType);
        $this->userContext->method('getUserId')->willReturn($userId);
        $this->remoteAddress->method('getRemoteAddress')->willReturn($address);

        /** @var RestContext $context */
        $context = $this->model->create($service ='SomeService', $method = 'method', $path = '/api/route');
        $this->assertNotNull($context);
        $this->assertEquals($identityType, $context->getIdentityType());
        $this->assertEquals($identity, $context->getIdentity());
        $this->assertEquals($typeId, $context->getTypeId());
        $this->assertEquals($service, $context->getService());
        $this->assertEquals($method, $context->getMethod());
        $this->assertEquals($path, $context->getEndpoint());
    }
}
