<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\App\Test\Unit\Request\Backpressure;

use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\Backpressure\ContextInterface;
use Magento\Framework\App\Request\Backpressure\ContextFactory;
use Magento\Framework\App\Request\Backpressure\ControllerContext;
use Magento\Framework\App\Backpressure\IdentityProviderInterface;
use Magento\Framework\App\Request\Backpressure\RequestTypeExtractorInterface;
use Magento\Framework\App\RequestInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ContextFactoryTest extends TestCase
{
    /**
     * @var RequestInterface|MockObject
     */
    private $request;

    /**
     * @var IdentityProviderInterface|MockObject
     */
    private $identityProvider;

    /**
     * @var RequestTypeExtractorInterface|MockObject
     */
    private $requestTypeExtractor;

    /**
     * @var ContextFactory
     */
    private $model;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->request = $this->createMock(RequestInterface::class);
        $this->identityProvider = $this->createMock(IdentityProviderInterface::class);
        $this->requestTypeExtractor = $this->createMock(RequestTypeExtractorInterface::class);

        $this->model = new ContextFactory(
            $this->requestTypeExtractor,
            $this->identityProvider,
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

        $this->assertNull($this->model->create($this->createAction()));
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
                ContextInterface::IDENTITY_TYPE_IP,
                '127.0.0.1',
            ],
            'customer' => [
                ContextInterface::IDENTITY_TYPE_CUSTOMER,
                '42'
            ],
            'admin' => [
                ContextInterface::IDENTITY_TYPE_ADMIN,
                '42'
            ]
        ];
    }

    /**
     * Verify that identity is created for customers.
     *
     * @param int $userType
     * @param string $userId
     * @return void
     * @dataProvider getIdentityCases
     */
    public function testCreateForIdentity(
        int $userType,
        string $userId
    ): void {
        $this->requestTypeExtractor->method('extract')->willReturn($typeId = 'test');
        $this->identityProvider->method('fetchIdentityType')->willReturn($userType);
        $this->identityProvider->method('fetchIdentity')->willReturn($userId);

        /** @var ControllerContext $context */
        $context = $this->model->create($action = $this->createAction());
        $this->assertNotNull($context);
        $this->assertEquals($userType, $context->getIdentityType());
        $this->assertEquals($userId, $context->getIdentity());
        $this->assertEquals($typeId, $context->getTypeId());
        $this->assertEquals($action, $context->getAction());
    }

    /**
     * Create Action instance.
     *
     * @return ActionInterface
     */
    private function createAction(): ActionInterface
    {
        return $this->createMock(ActionInterface::class);
    }
}
