<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Model\Session\Validators;

use Magento\Customer\Model\ResourceModel\Customer as ResourceCustomer;
use Magento\Customer\Model\ResourceModel\Visitor as ResourceVisitor;
use Magento\Customer\Model\Session\Validators\CutoffValidator;
use Magento\Framework\Exception\SessionExpiredException;
use Magento\Framework\Session\Generic;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for \Magento\Customer\Model\Session\Validators\CutoffValidator class
 */
class CutoffValidatorTest extends TestCase
{
    use MockCreationTrait;

    /**
     * @var ResourceCustomer|MockObject
     */
    private $customerResourceMock;

    /**
     * @var ResourceVisitor|MockObject
     */
    private $visitorResourceMock;

    /**
     * @var Generic|MockObject
     */
    private $visitorSessionMock;

    /**
     * @var SessionManagerInterface|MockObject
     */
    private $sessionMock;

    /**
     * @var CutoffValidator
     */
    private $validator;

    protected function setUp(): void
    {
        $this->customerResourceMock = $this->createMock(ResourceCustomer::class);
        $this->visitorResourceMock = $this->createMock(ResourceVisitor::class);
        $this->visitorSessionMock = $this->createPartialMockWithReflection(Generic::class, ['getVisitorData']);
        $this->sessionMock = $this->createMock(SessionManagerInterface::class);

        $this->validator = new CutoffValidator(
            $this->customerResourceMock,
            $this->visitorResourceMock,
            $this->visitorSessionMock
        );
    }

    public function testValidateThrowsSessionExpiredExceptionWhenCutoffIsAfterSessionCreation(): void
    {
        $this->visitorSessionMock->method('getVisitorData')
            ->willReturn(['customer_id' => 1, 'visitor_id' => 2]);
        $this->customerResourceMock->method('findSessionCutOff')
            ->with(1)
            ->willReturn(200);
        $this->visitorResourceMock->method('fetchCreatedAt')
            ->with(2)
            ->willReturn(100);
        $this->sessionMock->expects($this->once())
            ->method('destroy')
            ->with(['clear_storage' => false]);

        $this->expectException(SessionExpiredException::class);
        $this->expectExceptionMessage('The session has expired, please login again.');

        $this->validator->validate($this->sessionMock);
    }

    /**
     * @param array|null $visitorData
     * @param int|null $cutoff
     * @param int|null $createdAt
     * @return void
     */
    #[DataProvider('validSessionDataProvider')]
    public function testValidateKeepsValidSession(?array $visitorData, ?int $cutoff, ?int $createdAt): void
    {
        $this->visitorSessionMock->method('getVisitorData')
            ->willReturn($visitorData);
        $this->customerResourceMock->method('findSessionCutOff')
            ->willReturn($cutoff);
        $this->visitorResourceMock->method('fetchCreatedAt')
            ->willReturn($createdAt);
        $this->sessionMock->expects($this->never())
            ->method('destroy');

        $this->validator->validate($this->sessionMock);
    }

    /**
     * @return array
     */
    public static function validSessionDataProvider(): array
    {
        return [
            'no visitor data' => [null, 200, 100],
            'guest visitor' => [['visitor_id' => 2], 200, 100],
            'session created after cutoff' => [['customer_id' => 1, 'visitor_id' => 2], 100, 200],
            'no cutoff' => [['customer_id' => 1, 'visitor_id' => 2], null, 100],
        ];
    }
}
