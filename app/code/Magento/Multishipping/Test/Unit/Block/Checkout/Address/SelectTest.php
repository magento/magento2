<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Multishipping\Test\Unit\Block\Checkout\Address;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SelectTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Multishipping\Block\Checkout\Address\Select
     */
    protected $block;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $addressMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $multishippingMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $filterBuilderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $searchCriteriaBuilderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $addressRepositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $filterMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $searchCriteriaMock;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);
        $this->multishippingMock =
            $this->createMock(\Magento\Multishipping\Model\Checkout\Type\Multishipping::class);
        $this->addressMock = $this->createMock(\Magento\Customer\Api\Data\AddressInterface::class);
        $this->customerMock = $this->createMock(\Magento\Customer\Api\Data\CustomerInterface::class);
        $this->filterBuilderMock = $this->createMock(\Magento\Framework\Api\FilterBuilder::class);
        $this->searchCriteriaBuilderMock =
            $this->createMock(\Magento\Framework\Api\SearchCriteriaBuilder::class);
        $this->addressRepositoryMock = $this->createMock(\Magento\Customer\Api\AddressRepositoryInterface::class);
        $this->filterMock = $this->createMock(\Magento\Framework\Api\Filter::class);
        $this->searchCriteriaMock = $this->createMock(\Magento\Framework\Api\SearchCriteria::class);
        $this->block = $this->objectManager->getObject(
            \Magento\Multishipping\Block\Checkout\Address\Select::class,
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

    public function isDefaultAddressDataProvider()
    {
        return [
            'yes' => [1, true],
            'no' => [2, false],
        ];
    }

    public function testGetAddress()
    {
        $searchResultMock = $this->createMock(\Magento\Customer\Api\Data\AddressSearchResultsInterface::class);
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
            \Magento\Multishipping\Block\Checkout\Address\Select::class,
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
        $searchResultMock = $this->createMock(\Magento\Customer\Api\Data\AddressSearchResultsInterface::class);
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
