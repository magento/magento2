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
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\State\ExpiredException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test retrieving Customer by reset password token.
 */
class GetCustomerByTokenTest extends TestCase
{
    private const EXPECTED_CUSTOMER_ID = 42;
    private const OTHER_CUSTOMER_ID = 64;
    const MOCKED_TOKEN = 'mocked-token-42';

    /**
     * @var CustomerRepositoryInterface|MockObject
     */
    private $customerRepository;

    /**
     * @var SearchCriteriaBuilder|MockObject
     */
    private $searchCriteriaBuilder;

    /**
     * @var GetCustomerByToken
     */
    private $getCustomerByToken;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->customerRepository = $this->getMockBuilder(CustomerRepositoryInterface::class)
            ->getMock();
        $this->searchCriteriaBuilder = $this->getMockBuilder(SearchCriteriaBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->getCustomerByToken = new GetCustomerByToken(
            $this->customerRepository,
            $this->searchCriteriaBuilder
        );
    }

    /**
     * Test whether search results return first Customer that was found by token.
     *
     * @return void
     */
    public function testExecuteReturnsFirstCustomer(): void
    {
        /** @var CustomerInterface|MockObject $expectedCustomer */
        $expectedCustomer = $this->getMockBuilder(CustomerInterface::class)
            ->getMock();
        $expectedCustomer->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn(self::EXPECTED_CUSTOMER_ID);

        /** @var CustomerInterface|MockObject $otherCustomer */
        $otherCustomer = $this->getMockBuilder(CustomerInterface::class)
            ->getMock();
        $otherCustomer->expects($this->never())
            ->method('getId')
            ->willReturn(self::OTHER_CUSTOMER_ID);

        $mockedSearchResults = [
            self::EXPECTED_CUSTOMER_ID => $expectedCustomer,
            self::OTHER_CUSTOMER_ID => $otherCustomer,
        ];

        $searchCriteria = $this->getSearchCriteriaMock();

        $found = $this->getMockBuilder(CustomerSearchResultsInterface::class)
            ->getMock();
        $found->expects($this->atLeastOnce())
            ->method('getTotalCount')
            ->willReturn(1);
        $found->expects($this->once())
            ->method('getItems')
            ->willReturn($mockedSearchResults);

        $this->customerRepository->expects($this->once())
            ->method('getList')
            ->with($searchCriteria)
            ->willReturn($found);

        $result = $this->getCustomerByToken->execute(self::MOCKED_TOKEN);

        $this->assertEquals($expectedCustomer->getId(), $result->getId());
    }

    /**
     * Test throws when more than one request tokens are assigned to the customer.
     *
     * @throws ExpiredException
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function testThrowsWhenFoundMoreThanOneCustomer(): void
    {
        $this->expectException(ExpiredException::class);
        $this->expectExceptionMessage('Reset password token expired.');

        $this->getSearchCriteriaMock();

        $found = $this->getMockBuilder(CustomerSearchResultsInterface::class)
            ->getMock();
        $found->expects($this->atLeastOnce())
            ->method('getTotalCount')
            ->willReturn(2);

        $this->customerRepository->expects($this->once())
            ->method('getList')
            ->willReturn($found);

        $this->getCustomerByToken->execute(self::MOCKED_TOKEN);
    }

    /**
     * Test that exception is thrown when customer was not found for a given token.
     *
     * @throws ExpiredException
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function testThrowsWhenCustomerWasNotFound(): void
    {
        $this->expectException(NoSuchEntityException::class);
        $this->expectExceptionMessage(sprintf('No such entity with rp_token = %s', self::MOCKED_TOKEN));

        $this->getSearchCriteriaMock();

        $found = $this->getMockBuilder(CustomerSearchResultsInterface::class)
            ->getMock();
        $found->expects($this->atLeastOnce())
            ->method('getTotalCount')
            ->willReturn(0);

        $this->customerRepository->expects($this->once())
            ->method('getList')
            ->willReturn($found);

        $this->getCustomerByToken->execute(self::MOCKED_TOKEN);
    }

    /**
     * Prepare search criteria and search criteria builder mocks.
     *
     * @return MockObject|SearchCriteriaInterface
     */
    private function getSearchCriteriaMock(): SearchCriteriaInterface
    {
        $searchCriteria = $this->getMockBuilder(SearchCriteria::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->searchCriteriaBuilder->expects($this->once())
            ->method('addFilter')
            ->with(
                'rp_token',
                self::MOCKED_TOKEN
            );
        $this->searchCriteriaBuilder->expects($this->once())
            ->method('setPageSize')
            ->with(1);
        $this->searchCriteriaBuilder->expects($this->once())
            ->method('create')
            ->willReturn($searchCriteria);
        return $searchCriteria;
    }
}
