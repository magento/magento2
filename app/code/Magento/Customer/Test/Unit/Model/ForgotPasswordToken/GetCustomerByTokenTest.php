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
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class GetCustomerByTokenTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var CustomerRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerRepository;

    /**
     * @var SearchCriteriaBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $searchCriteriaBuilder;

    /**
     * @var SearchCriteriaInterface|\PHPUnit_Framework_MockObject_MockObject
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
                'searchCriteriaBuilder' => $this->searchCriteriaBuilder
            ]
        );
    }

    /**
     * Test get non exist customer by token
     *
     * @return void
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     */
    public function testExecute(): void
    {
        $token = 'token';
        $this->customerRepository->method('getList')->willReturn($this->searchCriteria);
        $this->searchCriteria->method('getTotalCount')->willReturn(0);

        $this->getCustomerByToken->execute($token);
    }
}
