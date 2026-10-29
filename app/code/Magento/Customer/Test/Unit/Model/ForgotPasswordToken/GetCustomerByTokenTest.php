<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Model\ForgotPasswordToken;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\ForgotPasswordToken\GetCustomerByToken;
use Magento\Customer\Model\ResourceModel\Customer\Collection;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\State\ExpiredException;
use Magento\Framework\Phrase;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GetCustomerByTokenTest extends TestCase
{
    private const RESET_PASSWORD = 'resetPassword';

    /**
     * @var CustomerRepositoryInterface|MockObject
     */
    private CustomerRepositoryInterface|MockObject $customerRepositoryMock;

    /**
     * @var SearchCriteriaBuilder|MockObject
     */
    private SearchCriteriaBuilder|MockObject $searchCriteriaBuilderMock;

    /**
     * @var CollectionFactory|MockObject
     */
    private CollectionFactory|MockObject $collectionFactoryMock;

    /**
     * @var Collection|MockObject
     */
    private Collection|MockObject $collectionMock;

    /**
     * @var GetCustomerByToken
     */
    private GetCustomerByToken $model;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->customerRepositoryMock = $this->createMock(CustomerRepositoryInterface::class);
        $this->searchCriteriaBuilderMock = $this->createMock(SearchCriteriaBuilder::class);
        $this->collectionMock = $this->createMock(Collection::class);
        $this->collectionFactoryMock = $this->createMock(CollectionFactory::class);
        $this->collectionFactoryMock->method('create')->willReturn($this->collectionMock);

        $this->model = new GetCustomerByToken(
            $this->customerRepositoryMock,
            $this->searchCriteriaBuilderMock,
            $this->collectionFactoryMock
        );
    }

    /**
     * Verifies that a matching customer is returned when exactly one result is found.
     */
    public function testExecuteReturnWhenOneItemAvailable(): void
    {
        $customerMock = $this->createMock(CustomerInterface::class);

        $this->collectionMock->expects($this->once())
            ->method('addAttributeToFilter')
            ->with('rp_token', self::RESET_PASSWORD);
        $this->collectionMock->method('getSize')->willReturn(1);
        $this->collectionMock->method('getFirstItem')->willReturn($customerMock);

        $this->assertInstanceOf(
            CustomerInterface::class,
            $this->model->execute(self::RESET_PASSWORD)
        );
    }

    /**
     * Verifies that NoSuchEntityException is thrown when no customer matches the token.
     */
    public function testExecuteWithNoSuchEntityException(): void
    {
        $this->collectionMock->method('addAttributeToFilter')
            ->with('rp_token', self::RESET_PASSWORD);
        $this->collectionMock->method('getSize')->willReturn(0);

        $this->expectExceptionObject(new NoSuchEntityException(
            new Phrase(
                'No such entity with rp_token = %value',
                ['value' => self::RESET_PASSWORD]
            )
        ));

        $this->model->execute(self::RESET_PASSWORD);
    }

    /**
     * Verifies that ExpiredException is thrown when more than one customer matches the token.
     */
    public function testExecuteWithExpireException(): void
    {
        $this->collectionMock->method('addAttributeToFilter')
            ->with('rp_token', self::RESET_PASSWORD);
        $this->collectionMock->method('getSize')->willReturn(2);

        $this->expectExceptionObject(new ExpiredException(
            new Phrase('Reset password token expired.')
        ));

        $this->model->execute(self::RESET_PASSWORD);
    }
}
