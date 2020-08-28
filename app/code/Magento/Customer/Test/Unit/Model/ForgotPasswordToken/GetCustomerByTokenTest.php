<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Model\ForgotPasswordToken;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\ForgotPasswordToken\GetCustomerByToken;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\SearchCriteriaBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Customer\Api\Data\CustomerSearchResultsInterface;

/**
 * Test retrieving Customer by reset password token.
 */
class GetCustomerByTokenTest extends TestCase
{
    private const EXPECTED_CUSTOMER_ID = 42;
    private const OTHER_CUSTOMER_ID = 64;

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
     * Test to check if search results returns first Customer that was found by token.
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
        $resetPasswordToken = 'mocked-token-42';

        $searchCriteria = $this->getMockBuilder(SearchCriteria::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->searchCriteriaBuilder->expects($this->once())
            ->method('addFilter')
            ->with(
                'rp_token',
                $resetPasswordToken
            );
        $this->searchCriteriaBuilder->expects($this->once())
            ->method('setPageSize')
            ->with(1);
        $this->searchCriteriaBuilder->expects($this->once())
            ->method('create')
            ->willReturn($searchCriteria);

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

        $result = $this->getCustomerByToken->execute($resetPasswordToken);

        $this->assertEquals($expectedCustomer->getId(), $result->getId());
    }
}
