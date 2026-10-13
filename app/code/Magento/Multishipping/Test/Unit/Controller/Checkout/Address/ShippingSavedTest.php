<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Multishipping\Test\Unit\Controller\Checkout\Address;

use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\AddressSearchResultsInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Api\Filter;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Multishipping\Controller\Checkout\Address\ShippingSaved;
use Magento\Multishipping\Model\Checkout\Type\Multishipping;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.LongVariable)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ShippingSavedTest extends TestCase
{
    /**
     * @var ShippingSaved
     */
    private $controller;

    /**
     * @var MockObject
     */
    private $contextMock;

    /**
     * @var MockObject
     */
    private $addressRepositoryMock;

    /**
     * @var MockObject
     */
    private $filterBuilderMock;

    /**
     * @var MockObject
     */
    private $criteriaBuilderMock;

    /**
     * @var MockObject
     */
    private $objectManagerMock;

    /**
     * @var MockObject
     */
    private $checkoutMock;

    /**
     * @var MockObject
     */
    private $redirectMock;

    protected function setUp(): void
    {
        $this->checkoutMock = $this->createMock(Multishipping::class);
        $this->objectManagerMock = $this->createMock(ObjectManagerInterface::class);
        $this->objectManagerMock->expects($this->any())
            ->method('get')
            ->with(Multishipping::class)
            ->willReturn($this->checkoutMock);
        $this->contextMock = $this->createMock(Context::class);
        $requestMock = $this->createMock(RequestInterface::class);
        $responseMock = $this->createMock(ResponseInterface::class);
        $this->redirectMock = $this->createMock(RedirectInterface::class);
        $this->contextMock->expects($this->any())->method('getObjectManager')->willReturn($this->objectManagerMock);
        $this->contextMock->expects($this->any())->method('getRequest')->willReturn($requestMock);
        $this->contextMock->expects($this->any())->method('getResponse')->willReturn($responseMock);
        $this->contextMock->expects($this->any())->method('getRedirect')->willReturn($this->redirectMock);

        $this->addressRepositoryMock = $this->createMock(AddressRepositoryInterface::class);
        $this->filterBuilderMock = $this->createMock(FilterBuilder::class);
        $this->criteriaBuilderMock = $this->createMock(SearchCriteriaBuilder::class);
        $this->controller = new ShippingSaved(
            $this->contextMock,
            $this->addressRepositoryMock,
            $this->filterBuilderMock,
            $this->criteriaBuilderMock
        );
    }

    public function testExecuteResetsCheckoutIfCustomerHasAddedNewShippingAddressAndItIsTheOnlyAddressHeHas()
    {
        $customerId = 1;
        $customerMock = $this->createMock(CustomerInterface::class);
        $customerMock->expects($this->any())->method('getId')->willReturn($customerId);
        $this->checkoutMock->expects($this->any())->method('getCustomer')->willReturn($customerMock);

        $this->mockCustomerAddressRepository(
            $customerId,
            [$this->createMock(AddressInterface::class)]
        );

        // check that checkout is reset
        $this->checkoutMock->expects($this->once())->method('reset');
        $this->controller->execute();
    }

    public function testExecuteDoesNotResetCheckoutIfCustomerHasMoreThanOneAddress()
    {
        $customerId = 1;
        $customerMock = $this->createMock(CustomerInterface::class);
        $customerMock->expects($this->any())->method('getId')->willReturn($customerId);
        $this->checkoutMock->expects($this->any())->method('getCustomer')->willReturn($customerMock);

        $this->mockCustomerAddressRepository(
            $customerId,
            [
                $this->createMock(AddressInterface::class),
                $this->createMock(AddressInterface::class),
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
        $filterMock = $this->createMock(Filter::class);
        $this->filterBuilderMock->expects($this->once())->method('setField')->with('parent_id')->willReturnSelf();
        $this->filterBuilderMock->expects($this->once())->method('setValue')->with($customerId)->willReturnSelf();
        $this->filterBuilderMock->expects($this->once())->method('setConditionType')->with('eq')->willReturnSelf();
        $this->filterBuilderMock->expects($this->once())->method('create')->willReturn($filterMock);

        $searchCriteriaMock = $this->createMock(SearchCriteria::class);
        $this->criteriaBuilderMock->expects($this->once())->method('addFilters')->with([$filterMock])->willReturnSelf();
        $this->criteriaBuilderMock->expects($this->once())->method('create')->willReturn($searchCriteriaMock);

        $searchResultMock = $this->createMock(AddressSearchResultsInterface::class);
        $this->addressRepositoryMock->expects($this->once())
            ->method('getList')
            ->with($searchCriteriaMock)
            ->willReturn($searchResultMock);

        $searchResultMock->expects($this->once())->method('getItems')->willReturn($addresses);
    }
}
