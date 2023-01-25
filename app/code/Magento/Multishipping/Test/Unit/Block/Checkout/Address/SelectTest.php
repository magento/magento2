<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Multishipping\Test\Unit\Block\Checkout\Address;

use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\AddressSearchResultsInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Api\Filter;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Multishipping\Block\Checkout\Address\Select;
use Magento\Multishipping\Model\Checkout\Type\Multishipping;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SelectTest extends TestCase
{
    /**
     * @var Select
     */
    protected $block;

    /**
     * @var MockObject
     */
    protected $addressMock;

    /**
     * @var MockObject
     */
    protected $multishippingMock;

    /**
     * @var MockObject
     */
    protected $customerMock;

    /**
     * @var MockObject
     */
    protected $filterBuilderMock;

    /**
     * @var MockObject
     */
    protected $searchCriteriaBuilderMock;

    /**
     * @var MockObject
     */
    protected $addressRepositoryMock;

    /**
     * @var MockObject
     */
    protected $filterMock;

    /**
     * @var MockObject
     */
    protected $searchCriteriaMock;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->multishippingMock =
            $this->createMock(Multishipping::class);
        $this->addressMock = $this->getMockForAbstractClass(AddressInterface::class);
        $this->customerMock = $this->getMockForAbstractClass(CustomerInterface::class);
        $this->filterBuilderMock = $this->createMock(FilterBuilder::class);
        $this->searchCriteriaBuilderMock =
            $this->createMock(SearchCriteriaBuilder::class);
        $this->addressRepositoryMock = $this->getMockForAbstractClass(AddressRepositoryInterface::class);
        $this->filterMock = $this->createMock(Filter::class);
        $this->searchCriteriaMock = $this->createMock(SearchCriteria::class);
        $this->block = $this->objectManager->getObject(
            Select::class,
            [
                'multishipping' => $this->multishippingMock,
                'addressRepository' => $this->addressRepositoryMock,
                'searchCriteriaBuilder' => $this->searchCriteriaBuilderMock,
                'filterBuilder' => $this->filterBuilderMock
            ]
        );
    }

    /**
     * @param string $id
     * @param bool $expectedValue
     * @dataProvider isDefaultAddressDataProvider
     */
    public function testIsAddressDefaultBilling($id, $expectedValue)
    {
        $this->addressMock->expects($this->once())->method('getId')->willReturn(1);
        $this->multishippingMock->expects($this->once())->method('getCustomer')->willReturn($this->customerMock);
        $this->customerMock->expects($this->once())->method('getDefaultBilling')->willReturn($id);
        $this->assertEquals($expectedValue, $this->block->isAddressDefaultBilling($this->addressMock));
    }

    /**
     * @param string $id
     * @param bool $expectedValue
     * @dataProvider isDefaultAddressDataProvider
     */
    public function testIsAddressDefaultShipping($id, $expectedValue)
    {
        $this->addressMock->expects($this->once())->method('getId')->willReturn(1);
        $this->multishippingMock->expects($this->once())->method('getCustomer')->willReturn($this->customerMock);
        $this->customerMock->expects($this->once())->method('getDefaultShipping')->willReturn($id);
        $this->assertEquals($expectedValue, $this->block->isAddressDefaultShipping($this->addressMock));
    }

    /**
     * @return array
     */
    public function isDefaultAddressDataProvider()
    {
        return [
            'yes' => [1, true],
            'no' => [2, false],
        ];
    }

    public function testGetAddress()
    {
        $searchResultMock = $this->getMockForAbstractClass(AddressSearchResultsInterface::class);
        $this->multishippingMock->expects($this->once())->method('getCustomer')->willReturn($this->customerMock);
        $this->customerMock->expects($this->once())->method('getId')->willReturn(1);
        $this->filterBuilderMock->expects($this->once())->method('setField')->with('parent_id')->willReturnSelf();
        $this->filterBuilderMock->expects($this->once())->method('setValue')->with(1)->willReturnSelf();
        $this->filterBuilderMock->expects($this->once())->method('setConditionType')->with('eq')->willReturnSelf();
        $this->filterBuilderMock->expects($this->once())->method('create')->willReturn($this->filterMock);
        $this->searchCriteriaBuilderMock
            ->expects($this->once())
            ->method('addFilters')
            ->with([$this->filterMock])
            ->willReturnSelf();
        $this->searchCriteriaBuilderMock
            ->expects($this->once())
            ->method('create')
            ->willReturn($this->searchCriteriaMock);
        $this->addressRepositoryMock
            ->expects($this->once())
            ->method('getList')
            ->with($this->searchCriteriaMock)
            ->willReturn($searchResultMock);
        $searchResultMock->expects($this->once())->method('getItems')->willReturn([$this->addressMock]);
        $this->assertEquals([$this->addressMock], $this->block->getAddress());
        $this->assertEquals([$this->addressMock], $this->block->getData('address_collection'));
    }

    public function testGetAlreadyExistingAddress()
    {
        $this->block = $this->objectManager->getObject(
            Select::class,
            [
                'addressRepository' => $this->addressRepositoryMock,
                'filterBuilder' => $this->filterBuilderMock,
                'data' => [
                    'address_collection' => [$this->addressMock
                    ]
                ]
            ]
        );
        $this->filterBuilderMock->expects($this->never())->method('setField');
        $this->addressRepositoryMock
            ->expects($this->never())
            ->method('getList');
        $this->assertEquals([$this->addressMock], $this->block->getAddress());
    }

    public function testGetAddressWhenItNotExistInCustomer()
    {
        $searchResultMock = $this->getMockForAbstractClass(AddressSearchResultsInterface::class);
        $this->multishippingMock->expects($this->once())->method('getCustomer')->willReturn($this->customerMock);
        $this->customerMock->expects($this->once())->method('getId')->willReturn(1);
        $this->filterBuilderMock->expects($this->once())->method('setField')->with('parent_id')->willReturnSelf();
        $this->filterBuilderMock->expects($this->once())->method('setValue')->with(1)->willReturnSelf();
        $this->filterBuilderMock->expects($this->once())->method('setConditionType')->with('eq')->willReturnSelf();
        $this->filterBuilderMock->expects($this->once())->method('create')->willReturn($this->filterMock);
        $this->searchCriteriaBuilderMock
            ->expects($this->once())
            ->method('addFilters')
            ->with([$this->filterMock])
            ->willReturnSelf();
        $this->searchCriteriaBuilderMock
            ->expects($this->once())
            ->method('create')
            ->willReturn($this->searchCriteriaMock);
        $this->addressRepositoryMock
            ->expects($this->once())
            ->method('getList')
            ->with($this->searchCriteriaMock)
            ->willReturn($searchResultMock);

        $searchResultMock->expects($this->once())->method('getItems')->willThrowException(new NoSuchEntityException());
        $this->assertEquals([], $this->block->getAddress());
    }
}
