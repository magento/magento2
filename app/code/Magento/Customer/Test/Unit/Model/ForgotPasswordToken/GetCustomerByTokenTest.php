<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Model\ForgotPasswordToken;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\State\ExpiredException;
use PHPUnit\Framework\MockObject\MockObject;
use Magento\Customer\Model\ForgotPasswordToken\GetCustomerByToken;
use Magento\Framework\Api\SearchCriteriaBuilder;
use PHPUnit\Framework\TestCase;

/**
 * Test for \Magento\Customer\Model\ForgotPasswordToken\GetCustomerByToken.
 */
class GetCustomerByTokenTest extends TestCase
{
    private const STUB_TOKEN = 'token777';

    /**
     * @var GetCustomerByToken
     */
    private $model;

    /**
     * @var CustomerRepositoryInterface|MockObject
     */
    private $customerRepositoryMock;

    /**
     * @var SearchCriteria|MockObject
     */
    private $searchCriteriaMock;

    /**
     * @var SearchCriteriaBuilder|MockObject
     */
    private $searchCriteriaBuilderMock;

    /**
     * @var SearchResultsInterface|MockObject
     */
    private $searchResultsMock;

    /**
     * @inheridoc
     */
    protected function setUp(): void
    {
        $this->customerRepositoryMock = $this->createMock(CustomerRepositoryInterface::class);
        $this->searchCriteriaMock = $this->createMock(SearchCriteria::class);
        $this->searchCriteriaBuilderMock = $this->createMock(SearchCriteriaBuilder::class);
        $this->searchCriteriaBuilderMock->expects($this->any())
            ->method('create')
            ->willReturn($this->searchCriteriaMock);
        $this->searchResultsMock = $this->getMockForAbstractClass(SearchResultsInterface::class);

        $this->model = new GetCustomerByToken(
            $this->customerRepositoryMock,
            $this->searchCriteriaBuilderMock
        );
    }

    /**
     * Test for execute
     *
     * @return void
     */
    public function testExecute(): void
    {
        $customerMock = $this->createMock(CustomerInterface::class);

        $this->searchCriteriaBuilderMock->expects($this->once())
            ->method('addFilter')
            ->with('rp_token', self::STUB_TOKEN);
        $this->searchCriteriaBuilderMock->expects($this->once())
            ->method('setPageSize')
            ->with(1);

        $this->customerRepositoryMock->expects($this->once())
            ->method('getList')
            ->with($this->searchCriteriaMock)
            ->willReturn($this->searchResultsMock);
        $this->searchResultsMock->expects($this->atMost(2))
            ->method('getTotalCount')
            ->willReturn(1);
        $this->searchResultsMock->expects($this->once())
            ->method('getItems')
            ->willReturn([$customerMock]);

        $this->assertEquals($customerMock, $this->model->execute(self::STUB_TOKEN));
    }

    /**
     * Execute expired token
     *
     * @return void
     */
    public function testExecuteExpiredToken(): void
    {
        $this->searchCriteriaBuilderMock->expects($this->once())
            ->method('addFilter')
            ->with('rp_token', self::STUB_TOKEN);
        $this->searchCriteriaBuilderMock->expects($this->once())
            ->method('setPageSize')
            ->with(1);

        $this->customerRepositoryMock->expects($this->once())
            ->method('getList')
            ->with($this->searchCriteriaMock)
            ->willReturn($this->searchResultsMock);
        $this->searchResultsMock->expects($this->once())
            ->method('getTotalCount')
            ->willReturn(2);

        $this->expectException(ExpiredException::class);
        $this->expectExceptionMessage('Reset password token expired.');

        $this->model->execute(self::STUB_TOKEN);
    }

    /**
     * Execute with a not existed token
     *
     * @return void
     */
    public function testExecuteNotExistedToken(): void
    {
        $this->searchCriteriaBuilderMock->expects($this->once())
            ->method('addFilter')
            ->with('rp_token', self::STUB_TOKEN);
        $this->searchCriteriaBuilderMock->expects($this->once())
            ->method('setPageSize')
            ->with(1);

        $this->customerRepositoryMock->expects($this->once())
            ->method('getList')
            ->with($this->searchCriteriaMock)
            ->willReturn($this->searchResultsMock);
        $this->searchResultsMock->expects($this->atLeastOnce())
            ->method('getTotalCount')
            ->willReturn(0);

        $this->expectException(NoSuchEntityException::class);
        $this->expectExceptionMessage('No such entity with rp_token = ' . self::STUB_TOKEN);

        $this->model->execute(self::STUB_TOKEN);
    }
}
