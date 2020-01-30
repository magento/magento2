<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Unit\Model\ForgotPasswordToken;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\ForgotPasswordToken\GetCustomerByToken;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Test for \Magento\Customer\Model\ForgotPasswordToken\GetCustomerByToken
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class GetCustomerByTokenTest extends TestCase
{
    /**
     * Stub token for customer
     */
    private const STUB_CUSTOMER_TOKEN = 'token';

    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var CustomerRepositoryInterface|MockObject
     */
    private $customerRepository;

    /**
     * @var SearchCriteriaBuilder|MockObject
     */
    private $searchCriteriaBuilder;

    /**
     * @var SearchCriteriaInterface|MockObject
     */
    private $searchCriteria;

    /**
     * @var GetCustomerByToken
     */
    private $getCustomerByToken;

    /**
     * Setup environment for test
     */
    protected function setUp()
    {
        $this->customerRepository = $this->createMock(CustomerRepositoryInterface::class);
        $this->searchCriteriaBuilder = $this->createMock(SearchCriteriaBuilder::class);
        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->searchCriteria = $this
            ->getMockBuilder(SearchCriteriaInterface::class)
            ->setMethods(['getTotalCount'])
            ->getMockForAbstractClass();
        $this->searchCriteriaBuilder->method('create')->willReturn($this->searchCriteria);

        $this->getCustomerByToken = $this->objectManagerHelper->getObject(
            GetCustomerByToken::class,
            [
                'customerRepository' => $this->customerRepository,
                'searchCriteriaBuilder' => $this->searchCriteriaBuilder,
            ]
        );
    }

    /**
     * Test get customer by token with `NoSuchEntityException`
     *
     * @return void
     */
    public function testExecuteWithNoSuchEntityException(): void
    {
        $this->customerRepository->method('getList')->willReturn($this->searchCriteria);
        $this->searchCriteria->method('getTotalCount')->willReturn(0);

        $this->expectException(NoSuchEntityException::class);
        $this->expectExceptionMessage('No such entity with rp_token = ' . self::STUB_CUSTOMER_TOKEN);
        $this->getCustomerByToken->execute(self::STUB_CUSTOMER_TOKEN);
    }
}
