<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Model\ForgotPasswordToken;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\Data\CustomerSearchResultsInterface;
use Magento\Customer\Model\ForgotPasswordToken\GetCustomerByToken;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\State\ExpiredException;
use Magento\Framework\Phrase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test GetCustomerByToken class
 */
class GetCustomerByTokenTest extends TestCase
{
    protected const RESET_PASSWORD = 'resetPassword';

    /**
     * @var SearchCriteriaBuilder|MockObject
     */
    protected $searchCriteriaBuilderMock;

    /**
     * @var SearchCriteria|MockObject
     */
    protected $searchCriteriaMock;

    /**
     * @var CustomerRepositoryInterface|MockObject
     */
    protected $customerRepositoryMock;

    /**
     * @var CustomerSearchResultsInterface|MockObject
     */
    protected $searchResultMock;

    /**
     * @var CustomerInterface|MockObject
     */
    protected $customerMock;

    /**
     * @var GetCustomerByToken;
     */
    protected $model;

    /**
     * @inheritDoc
     */
    public function setUp()
    {
        $this->searchCriteriaBuilderMock = $this->createMock(SearchCriteriaBuilder::class);
        $this->searchCriteriaMock = $this->createMock(SearchCriteria::class);
        $this->searchResultMock = $this->createMock(CustomerSearchResultsInterface::class);
        $this->customerRepositoryMock = $this->createMock(CustomerRepositoryInterface::class);
        $this->customerMock = $this->getMockForAbstractClass(CustomerInterface::class);

        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->model = $objectManagerHelper->getObject(
            GetCustomerByToken::class,
            [
                'searchCriteriaBuilder' => $this->searchCriteriaBuilderMock,
                'customerRepository' => $this->customerRepositoryMock
            ]
        );

        $this->searchCriteriaBuilderMock->expects($this->once())
            ->method('create')
            ->willReturn($this->searchCriteriaMock);
        $this->customerRepositoryMock->expects($this->once())
            ->method('getList')
            ->with($this->searchCriteriaMock)
            ->willReturn($this->searchResultMock);
    }

    /**
     * @return void
     */
    public function testExecute(): void
    {
        $totalCount = 1;
        $this->searchResultMock->expects($this->any())
            ->method('getTotalCount')
            ->willReturn($totalCount);
        $this->searchResultMock->expects($this->once())
            ->method('getItems')
            ->willReturn([$this->customerMock]);

        $this->assertInstanceOf(
            CustomerInterface::class,
            $this->model->execute(self::RESET_PASSWORD)
        );
    }

    /**
     * @return void
     */
    public function testExecuteWithNoSuchEntityException(): void
    {
        $totalCount = 0;
        $this->searchResultMock->expects($this->any())
            ->method('getTotalCount')
            ->willReturn($totalCount);
        $this->expectExceptionObject(new NoSuchEntityException(
            new Phrase(
                'No such entity with rp_token = %value',
                ['value' => self::RESET_PASSWORD]
            )
        ));

        $this->model->execute(self::RESET_PASSWORD);
    }

    /**
     * @return void
     */
    public function testExecuteWithExpireException(): void
    {
        $totalCount = 2;
        $this->searchResultMock->expects($this->any())
            ->method('getTotalCount')
            ->willReturn($totalCount);

        $this->expectExceptionObject(new ExpiredException(
            new Phrase(
                'Reset password token expired.'
            )
        ));

        $this->model->execute(self::RESET_PASSWORD);
    }
}
