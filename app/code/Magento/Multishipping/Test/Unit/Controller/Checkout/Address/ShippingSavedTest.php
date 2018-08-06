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
class ShippingSavedTest extends \PHPUnit\Framework\TestCase
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
        $this->checkoutMock = $this->createMock(\Magento\Multishipping\Model\Checkout\Type\Multishipping::class);
        $this->objectManagerMock = $this->createMock(\Magento\Framework\ObjectManagerInterface::class);
        $this->objectManagerMock->expects($this->any())
            ->method('get')
            ->with(\Magento\Multishipping\Model\Checkout\Type\Multishipping::class)
            ->willReturn($this->checkoutMock);
        $this->contextMock = $this->createMock(\Magento\Framework\App\Action\Context::class);
        $requestMock = $this->createMock(\Magento\Framework\App\RequestInterface::class);
        $responseMock = $this->createMock(\Magento\Framework\App\ResponseInterface::class);
        $this->redirectMock = $this->createMock(\Magento\Framework\App\Response\RedirectInterface::class);
        $this->contextMock->expects($this->any())->method('getObjectManager')->willReturn($this->objectManagerMock);
        $this->contextMock->expects($this->any())->method('getRequest')->willReturn($requestMock);
        $this->contextMock->expects($this->any())->method('getResponse')->willReturn($responseMock);
        $this->contextMock->expects($this->any())->method('getRedirect')->willReturn($this->redirectMock);

        $this->addressRepositoryMock = $this->createMock(\Magento\Customer\Api\AddressRepositoryInterface::class);
        $this->filterBuilderMock = $this->createMock(\Magento\Framework\Api\FilterBuilder::class);
        $this->criteriaBuilderMock = $this->createMock(\Magento\Framework\Api\SearchCriteriaBuilder::class);
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
        $customerMock = $this->createMock(\Magento\Customer\Api\Data\CustomerInterface::class);
        $customerMock->expects($this->any())->method('getId')->willReturn($customerId);
        $this->checkoutMock->expects($this->any())->method('getCustomer')->willReturn($customerMock);

        $this->mockCustomerAddressRepository(
            $customerId,
            [$this->createMock(\Magento\Customer\Api\Data\AddressInterface::class)]
        );

        // check that checkout is reset
        $this->checkoutMock->expects($this->once())->method('reset');
        $this->controller->execute();
    }

    public function testExecuteDoesNotResetCheckoutIfCustomerHasMoreThanOneAddress()
    {
        $customerId = 1;
        $customerMock = $this->createMock(\Magento\Customer\Api\Data\CustomerInterface::class);
        $customerMock->expects($this->any())->method('getId')->willReturn($customerId);
        $this->checkoutMock->expects($this->any())->method('getCustomer')->willReturn($customerMock);

        $this->mockCustomerAddressRepository(
            $customerId,
            [
                $this->createMock(\Magento\Customer\Api\Data\AddressInterface::class),
                $this->createMock(\Magento\Customer\Api\Data\AddressInterface::class),
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
        $filterMock = $this->createMock(\Magento\Framework\Api\Filter::class);
        $this->filterBuilderMock->expects($this->once())->method('setField')->with('parent_id')->willReturnSelf();
        $this->filterBuilderMock->expects($this->once())->method('setValue')->with($customerId)->willReturnSelf();
        $this->filterBuilderMock->expects($this->once())->method('setConditionType')->with('eq')->willReturnSelf();
        $this->filterBuilderMock->expects($this->once())->method('create')->willReturn($filterMock);

        $searchCriteriaMock = $this->createMock(\Magento\Framework\Api\SearchCriteria::class);
        $this->criteriaBuilderMock->expects($this->once())->method('addFilters')->with([$filterMock])->willReturnSelf();
        $this->criteriaBuilderMock->expects($this->once())->method('create')->willReturn($searchCriteriaMock);

        $searchResultMock = $this->createMock(\Magento\Customer\Api\Data\AddressSearchResultsInterface::class);
        $this->addressRepositoryMock->expects($this->once())
            ->method('getList')
            ->with($searchCriteriaMock)
            ->willReturn($searchResultMock);

        $searchResultMock->expects($this->once())->method('getItems')->willReturn($addresses);
    }
}
