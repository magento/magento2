<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\GraphQl\Test\Unit\Model\Backpressure;

use Magento\Framework\App\Backpressure\ContextInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Magento\GraphQl\Model\Backpressure\BackpressureContextFactory;
use Magento\GraphQl\Model\Backpressure\GraphQlContext;
use Magento\GraphQl\Model\Backpressure\RequestTypeExtractorInterface;
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
     * @var RequestTypeExtractorInterface|MockObject
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
        $this->requestTypeExtractor = $this->createMock(RequestTypeExtractorInterface::class);

        $this->model = new BackpressureContextFactory(
            $this->requestTypeExtractor,
            $this->userContext,
            $this->remoteAddress,
            $this->request
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

        $this->assertNull($this->model->create($this->createField('test')));
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

        /** @var GraphQlContext $context */
        $context = $this->model->create($this->createField($resolver = 'TestResolver'));
        $this->assertNotNull($context);
        $this->assertEquals($identityType, $context->getIdentityType());
        $this->assertEquals($identity, $context->getIdentity());
        $this->assertEquals($typeId, $context->getTypeId());
        $this->assertEquals($resolver, $context->getResolverClass());
    }

    /**
     * Create Field instance.
     *
     * @param string $resolver
     * @return Field
     */
    private function createField(string $resolver): Field
    {
        $mock = $this->createMock(Field::class);
        $mock->method('getResolver')->willReturn($resolver);

        return $mock;
    }
}
