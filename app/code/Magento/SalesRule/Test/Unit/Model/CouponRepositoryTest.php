<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Test\Unit\Model;

use Magento\Framework\Api\SortOrder;
use Magento\SalesRule\Api\Data\CouponInterface;

class CouponRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\SalesRule\Model\CouponRepository
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $searchResultFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $searchResultsMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $couponFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $ruleFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $collectionFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $resource;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $extensionAttributesJoinProcessorMock;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    protected function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->searchResultFactory = $this->getMock(
            '\Magento\SalesRule\Api\Data\CouponSearchResultInterfaceFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->searchResultsMock = $this->getMock(
            '\Magento\SalesRule\Api\Data\CouponSearchResultInterface',
            [],
            [],
            '',
            false
        );
        $this->couponFactory = $this->getMock('\Magento\SalesRule\Model\CouponFactory', ['create'], [], '', false);
        $this->ruleFactory = $this->getMock('\Magento\SalesRule\Model\RuleFactory', ['create'], [], '', false);
        $this->collectionFactory = $this->getMock(
            '\Magento\SalesRule\Model\ResourceModel\Coupon\CollectionFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->resource = $this->getMock('\Magento\SalesRule\Model\ResourceModel\Coupon', [], [], '', false);
        $this->extensionAttributesJoinProcessorMock = $this->getMock(
            '\Magento\Framework\Api\ExtensionAttribute\JoinProcessor',
            ['process'],
            [],
            '',
            false
        );

        $this->model = $this->objectManager->getObject(
            'Magento\SalesRule\Model\CouponRepository',
            [
                'couponFactory' => $this->couponFactory,
                'ruleFactory' => $this->ruleFactory,
                'searchResultFactory' => $this->searchResultFactory,
                'collectionFactory' => $this->collectionFactory,
                'resourceModel' => $this->resource,
                'extensionAttributesJoinProcessor' => $this->extensionAttributesJoinProcessorMock
            ]
        );
    }

    public function testSave()
    {
        $id = 1;
        $coupon = $this->getMock('\Magento\SalesRule\Model\Coupon', ['load', 'getCouponId', 'getById'], [], '', false);
        $coupon->expects($this->any())->method('load')->with($id)->willReturnSelf();
        $coupon->expects($this->any())->method('getCouponId')->willReturn($id);
        $this->couponFactory->expects($this->once())->method('create')->willReturn($coupon);

        /**
         * @var \Magento\SalesRule\Model\Rule $rule
         */
        $rule = $this->getMock('\Magento\SalesRule\Model\Rule', ['load', 'getRuleId'], [], '', false);

        $rule->expects($this->any())->method('load')->willReturnSelf();
        $rule->expects($this->any())->method('getRuleId')->willReturn($id);

        $this->ruleFactory->expects($this->any())->method('create')->willReturn($rule);

        $this->resource->expects($this->once())->method('save')->with($coupon);
        $this->assertEquals($coupon, $this->model->save($coupon));
    }

    /**
     * @dataProvider saveExceptionsDataProvider
     * @param $exceptionObject
     * @param $exceptionName
     * @param $exceptionMessage
     * @param $id
     * @throws \Exception
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function testSaveWithExceptions($exceptionObject, $exceptionName, $exceptionMessage, $id)
    {
        /**
         * @var \Magento\SalesRule\Model\Coupon $coupon
         */
        $coupon = $this->getMock('\Magento\SalesRule\Model\Coupon', [], [], '', false);

        /**
         * @var \Magento\SalesRule\Model\Rule $rule
         */
        $rule = $this->getMock('\Magento\SalesRule\Model\Rule', ['load', 'getRuleId'], [], '', false);

        $rule->expects($this->any())->method('load')->willReturnSelf();
        $rule->expects($this->any())->method('getRuleId')->willReturn($id);

        $this->ruleFactory->expects($this->any())->method('create')->willReturn($rule);

        if ($id) {
            $this->resource->expects($this->once())->method('save')->with($coupon)
                ->willThrowException($exceptionObject);
        }
        $this->setExpectedException($exceptionName, $exceptionMessage);
        $this->model->save($coupon);
    }

    /**
     * @return array
     */
    public function saveExceptionsDataProvider()
    {
        $msg = 'kiwis';
        $phrase = new \Magento\Framework\Phrase($msg);

        return [
            [
                new \Magento\Framework\Exception\LocalizedException($phrase),
                '\Magento\Framework\Exception\LocalizedException',
                $msg,
                1
            ],
            [
                null,
                '\Magento\Framework\Exception\LocalizedException',
                'Error occurred when saving coupon: No such entity with rule_id = ',
                false
            ]
        ];
    }

    public function testGetById()
    {
        $id = 10;
        /**
         * @var \Magento\SalesRule\Model\Coupon $coupon
         */
        $coupon = $this->getMock('\Magento\SalesRule\Model\Coupon', ['load', 'getCouponId'], [], '', false);
        $coupon->expects($this->any())->method('load')->with($id)->willReturnSelf();
        $coupon->expects($this->any())->method('getCouponId')->willReturn($id);
        $this->couponFactory->expects($this->once())->method('create')->willReturn($coupon);
        $this->assertEquals($coupon, $this->model->getById($id));
    }

    public function testDeleteById()
    {
        $id = 10;
        /**
         * @var \Magento\SalesRule\Model\Coupon $coupon
         */
        $coupon = $this->getMock('\Magento\SalesRule\Model\Coupon', ['load', 'getCouponId'], [], '', false);
        $coupon->expects($this->any())->method('load')->with($id)->willReturnSelf();
        $coupon->expects($this->any())->method('getCouponId')->willReturn($id);
        $this->couponFactory->expects($this->any())->method('create')->willReturn($coupon);
        $this->assertEquals($coupon, $this->model->getById($id));

        $this->resource->expects($this->once())->method('delete')->with($coupon);
        $this->assertTrue($this->model->deleteById($id));
    }

    public function testGetList()
    {
        $collectionSize = 1;
        $currentPage = 42;
        $pageSize = 4;

        /** @var CouponInterface|\PHPUnit_Framework_MockObject_MockObject $couponMock */
        $couponMock = $this->getMockBuilder(CouponInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        /**
         * @var \Magento\Framework\Api\SearchCriteriaInterface $searchCriteriaMock
         */
        $searchCriteriaMock = $this->getMock('\Magento\Framework\Api\SearchCriteria', [], [], '', false);
        $collectionMock = $this->getMock('Magento\SalesRule\Model\ResourceModel\Coupon\Collection', [], [], '', false);
        $filterGroupMock = $this->getMock('\Magento\Framework\Api\Search\FilterGroup', [], [], '', false);
        $filterMock = $this->getMock('\Magento\Framework\Api\Filter', [], [], '', false);
        $sortOrderMock = $this->getMock('\Magento\Framework\Api\SortOrder', [], [], '', false);

        $this->extensionAttributesJoinProcessorMock->expects($this->once())
            ->method('process')
            ->with($collectionMock, 'Magento\SalesRule\Api\Data\CouponInterface');

        $this->searchResultsMock->expects($this->once())->method('setSearchCriteria')->with($searchCriteriaMock);
        $this->collectionFactory->expects($this->once())->method('create')->willReturn($collectionMock);
        $searchCriteriaMock->expects($this->once())->method('getFilterGroups')->willReturn([$filterGroupMock]);
        $filterGroupMock->expects($this->once())->method('getFilters')->willReturn([$filterMock]);
        $filterMock->expects($this->exactly(2))->method('getConditionType')->willReturn('eq');
        $filterMock->expects($this->once())->method('getField')->willReturn(
            'coupon_id'
        );
        $filterMock->expects($this->once())->method('getValue')->willReturn('value');
        $collectionMock->expects($this->once())->method('addFieldToFilter')
            ->with([0 => 'coupon_id'], [0 => ['eq' => 'value']]);
        $collectionMock->expects($this->once())->method('getSize')->willReturn($collectionSize);
        $this->searchResultsMock->expects($this->once())->method('setTotalCount')->with($collectionSize);
        $searchCriteriaMock->expects($this->once())->method('getSortOrders')->willReturn([$sortOrderMock]);
        $sortOrderMock->expects($this->once())->method('getField')->willReturn('sort_order');
        $sortOrderMock->expects($this->once())->method('getDirection')->willReturn(SortOrder::SORT_ASC);
        $collectionMock->expects($this->once())->method('addOrder')->with('sort_order', 'ASC');
        $searchCriteriaMock->expects($this->once())->method('getCurrentPage')->willReturn($currentPage);
        $collectionMock->expects($this->once())->method('setCurPage')->with($currentPage);
        $searchCriteriaMock->expects($this->once())->method('getPageSize')->willReturn($pageSize);
        $collectionMock->expects($this->once())->method('setPageSize')->with($pageSize);
        $collectionMock->expects($this->once())->method('getItems')->willReturn([$couponMock]);
        $this->searchResultsMock->expects($this->once())->method('setItems')->with([$couponMock]);
        $this->searchResultFactory->expects($this->once())->method('create')->willReturn($this->searchResultsMock);

        $this->assertEquals($this->searchResultsMock, $this->model->getList($searchCriteriaMock));
    }
}
