<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Multishipping\Test\Unit\Controller\Checkout\Address;

use Magento\Multishipping\Controller\Checkout\Address\ShippingSaved;

/**
 * @SuppressWarnings(PHPMD.LongVariable)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ShippingSavedTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ShippingSaved
     */
    private $controller;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $contextMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $addressRepositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $filterBuilderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $criteriaBuilderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $objectManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $checkoutMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $redirectMock;

    protected function setUp()
    {
        $this->checkoutMock = $this->getMock(
            \Magento\Multishipping\Model\Checkout\Type\Multishipping::class,
            [],
            [],
            '',
            false
        );
        $this->objectManagerMock = $this->getMock(\Magento\Framework\ObjectManagerInterface::class);
        $this->objectManagerMock->expects($this->any())
            ->method('get')
            ->with(\Magento\Multishipping\Model\Checkout\Type\Multishipping::class)
            ->willReturn($this->checkoutMock);
        $this->contextMock = $this->getMock(\Magento\Framework\App\Action\Context::class, [], [], '', false);
        $requestMock = $this->getMock(\Magento\Framework\App\RequestInterface::class);
        $responseMock = $this->getMock(\Magento\Framework\App\ResponseInterface::class);
        $this->redirectMock = $this->getMock(\Magento\Framework\App\Response\RedirectInterface::class);
        $this->contextMock->expects($this->any())->method('getObjectManager')->willReturn($this->objectManagerMock);
        $this->contextMock->expects($this->any())->method('getRequest')->willReturn($requestMock);
        $this->contextMock->expects($this->any())->method('getResponse')->willReturn($responseMock);
        $this->contextMock->expects($this->any())->method('getRedirect')->willReturn($this->redirectMock);

        $this->addressRepositoryMock = $this->getMock(\Magento\Customer\Api\AddressRepositoryInterface::class);
        $this->filterBuilderMock = $this->getMock(\Magento\Framework\Api\FilterBuilder::class, [], [], '', false);
        $this->criteriaBuilderMock = $this->getMock(
            \Magento\Framework\Api\SearchCriteriaBuilder::class,
            [],
            [],
            '',
            false
        );
        $this->controller = new \Magento\Multishipping\Controller\Checkout\Address\ShippingSaved(
            $this->contextMock,
            $this->addressRepositoryMock,
            $this->filterBuilderMock,
            $this->criteriaBuilderMock
        );
    }

    public function testExecuteResetsCheckoutIfCustomerHasAddedNewShippingAddressAndItIsTheOnlyAddressHeHas()
    {
        $customerId = 1;
        $customerMock = $this->getMock(\Magento\Customer\Api\Data\CustomerInterface::class);
        $customerMock->expects($this->any())->method('getId')->willReturn($customerId);
        $this->checkoutMock->expects($this->any())->method('getCustomer')->willReturn($customerMock);

        $this->mockCustomerAddressRepository(
            $customerId,
            [$this->getMock(\Magento\Customer\Api\Data\AddressInterface::class)]
        );

        // check that checkout is reset
        $this->checkoutMock->expects($this->once())->method('reset');
        $this->controller->execute();
    }

    public function testExecuteDoesNotResetCheckoutIfCustomerHasMoreThanOneAddress()
    {
        $customerId = 1;
        $customerMock = $this->getMock(\Magento\Customer\Api\Data\CustomerInterface::class);
        $customerMock->expects($this->any())->method('getId')->willReturn($customerId);
        $this->checkoutMock->expects($this->any())->method('getCustomer')->willReturn($customerMock);

        $this->mockCustomerAddressRepository(
            $customerId,
            [
                $this->getMock(\Magento\Customer\Api\Data\AddressInterface::class),
                $this->getMock(\Magento\Customer\Api\Data\AddressInterface::class),
            ]
        );

        // check that checkout is not reset
        $this->checkoutMock->expects($this->never())->method('reset');
        $this->controller->execute();
    }

    /**
     * Mock customer address repository
     *
     * @param int $customerId
     * @param array $addresses list of customer addresses
     */
    private function mockCustomerAddressRepository($customerId, array $addresses)
    {
        $filterMock = $this->getMock(\Magento\Framework\Api\Filter::class, [], [], '', false);
        $this->filterBuilderMock->expects($this->once())->method('setField')->with('parent_id')->willReturnSelf();
        $this->filterBuilderMock->expects($this->once())->method('setValue')->with($customerId)->willReturnSelf();
        $this->filterBuilderMock->expects($this->once())->method('setConditionType')->with('eq')->willReturnSelf();
        $this->filterBuilderMock->expects($this->once())->method('create')->willReturn($filterMock);

        $searchCriteriaMock = $this->getMock(\Magento\Framework\Api\SearchCriteria::class, [], [], '', false);
        $this->criteriaBuilderMock->expects($this->once())->method('addFilters')->with([$filterMock])->willReturnSelf();
        $this->criteriaBuilderMock->expects($this->once())->method('create')->willReturn($searchCriteriaMock);

        $searchResultMock = $this->getMock(\Magento\Customer\Api\Data\AddressSearchResultsInterface::class);
        $this->addressRepositoryMock->expects($this->once())
            ->method('getList')
            ->with($searchCriteriaMock)
            ->willReturn($searchResultMock);

        $searchResultMock->expects($this->once())->method('getItems')->willReturn($addresses);
    }
}
