<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Test\Unit\Model;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CouponRepositoryTest extends \PHPUnit\Framework\TestCase
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

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $collectionProcessor;

    protected function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->searchResultFactory = $this->createPartialMock(
            \Magento\SalesRule\Api\Data\CouponSearchResultInterfaceFactory::class,
            ['create']
        );
        $this->searchResultsMock = $this->createMock(\Magento\SalesRule\Api\Data\CouponSearchResultInterface::class);
        $this->couponFactory = $this->createPartialMock(\Magento\SalesRule\Model\CouponFactory::class, ['create']);
        $this->ruleFactory = $this->createPartialMock(\Magento\SalesRule\Model\RuleFactory::class, ['create']);
        $this->collectionFactory = $this->createPartialMock(
            \Magento\SalesRule\Model\ResourceModel\Coupon\CollectionFactory::class,
            ['create']
        );
        $this->resource = $this->createMock(\Magento\SalesRule\Model\ResourceModel\Coupon::class);
        $this->extensionAttributesJoinProcessorMock = $this->createPartialMock(
            \Magento\Framework\Api\ExtensionAttribute\JoinProcessor::class,
            ['process']
        );

        $this->collectionProcessor = $this->createMock(
            \Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface::class
        );

        $this->model = $this->objectManager->getObject(
            \Magento\SalesRule\Model\CouponRepository::class,
            [
                'couponFactory' => $this->couponFactory,
                'ruleFactory' => $this->ruleFactory,
                'searchResultFactory' => $this->searchResultFactory,
                'collectionFactory' => $this->collectionFactory,
                'resourceModel' => $this->resource,
                'extensionAttributesJoinProcessor' => $this->extensionAttributesJoinProcessorMock,
                'collectionProcessor' => $this->collectionProcessor,
            ]
        );
    }

    public function testSave()
    {
        $id = 1;
        $coupon = $this->createPartialMock(\Magento\SalesRule\Model\Coupon::class, ['load', 'getCouponId', 'getById']);
        $coupon->expects($this->any())->method('load')->with($id)->willReturnSelf();
        $coupon->expects($this->any())->method('getCouponId')->willReturn($id);
        $this->couponFactory->expects($this->once())->method('create')->willReturn($coupon);

        /**
         * @var \Magento\SalesRule\Model\Rule $rule
         */
        $rule = $this->createPartialMock(\Magento\SalesRule\Model\Rule::class, ['load', 'getRuleId']);

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
        $coupon = $this->createMock(\Magento\SalesRule\Model\Coupon::class);

        /**
         * @var \Magento\SalesRule\Model\Rule $rule
         */
        $rule = $this->createPartialMock(\Magento\SalesRule\Model\Rule::class, ['load', 'getRuleId']);

        $rule->expects($this->any())->method('load')->willReturnSelf();
        $rule->expects($this->any())->method('getRuleId')->willReturn($id);

        $this->ruleFactory->expects($this->any())->method('create')->willReturn($rule);

        if ($id) {
            $this->resource->expects($this->once())->method('save')->with($coupon)
                ->willThrowException($exceptionObject);
        }
        $this->expectException($exceptionName, $exceptionMessage);
        $this->model->save($coupon);
    }

    public function saveExceptionsDataProvider()
    {
        $msg = 'kiwis';
        $phrase = new \Magento\Framework\Phrase($msg);

        return [
            [
                new \Magento\Framework\Exception\LocalizedException($phrase),
                \Magento\Framework\Exception\LocalizedException::class,
                $msg,
                1
            ],
            [
                null, \Magento\Framework\Exception\LocalizedException::class,
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
        $coupon = $this->createPartialMock(\Magento\SalesRule\Model\Coupon::class, ['load', 'getCouponId']);
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
        $coupon = $this->createPartialMock(\Magento\SalesRule\Model\Coupon::class, ['load', 'getCouponId']);
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
        $couponMock = $this->createMock(\Magento\SalesRule\Api\Data\CouponInterface::class);
        /**
         * @var \Magento\Framework\Api\SearchCriteriaInterface $searchCriteriaMock
         */
        $searchCriteriaMock = $this->createMock(\Magento\Framework\Api\SearchCriteria::class);
        $collectionMock = $this->createMock(\Magento\SalesRule\Model\ResourceModel\Coupon\Collection::class);
        $this->extensionAttributesJoinProcessorMock->expects($this->once())
            ->method('process')
            ->with($collectionMock, \Magento\SalesRule\Api\Data\CouponInterface::class);
        $this->collectionProcessor->expects($this->once())
            ->method('process')
            ->with($searchCriteriaMock, $collectionMock);
        $this->searchResultsMock->expects($this->once())->method('setSearchCriteria')->with($searchCriteriaMock);
        $this->collectionFactory->expects($this->once())->method('create')->willReturn($collectionMock);
        $collectionMock->expects($this->once())->method('getSize')->willReturn($collectionSize);
        $this->searchResultsMock->expects($this->once())->method('setTotalCount')->with($collectionSize);
        $collectionMock->expects($this->once())->method('getItems')->willReturn([$couponMock]);
        $this->searchResultsMock->expects($this->once())->method('setItems')->with([$couponMock]);
        $this->searchResultFactory->expects($this->once())->method('create')->willReturn($this->searchResultsMock);

        $this->assertEquals($this->searchResultsMock, $this->model->getList($searchCriteriaMock));
    }
}
