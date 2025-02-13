<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Test\Unit\Model;

use Magento\Framework\Api\ExtensionAttribute\JoinProcessor;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\SalesRule\Api\Data\CouponInterface;
use Magento\SalesRule\Api\Data\CouponSearchResultInterface;
use Magento\SalesRule\Api\Data\CouponSearchResultInterfaceFactory;
use Magento\SalesRule\Model\CouponFactory;
use Magento\SalesRule\Model\CouponRepository;
use Magento\SalesRule\Model\ResourceModel\Coupon;
use Magento\SalesRule\Model\ResourceModel\Coupon\Collection;
use Magento\SalesRule\Model\ResourceModel\Coupon\CollectionFactory;
use Magento\SalesRule\Model\Rule;
use Magento\SalesRule\Model\RuleFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CouponRepositoryTest extends TestCase
{
    /**
     * @var CouponRepository
     */
    protected $model;

    /**
     * @var MockObject
     */
    protected $searchResultFactory;

    /**
     * @var MockObject
     */
    protected $searchResultsMock;

    /**
     * @var MockObject
     */
    protected $couponFactory;

    /**
     * @var MockObject
     */
    protected $ruleFactory;

    /**
     * @var MockObject
     */
    protected $collectionFactory;

    /**
     * @var MockObject
     */
    protected $resource;

    /**
     * @var MockObject
     */
    protected $extensionAttributesJoinProcessorMock;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var MockObject
     */
    private $collectionProcessor;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->searchResultFactory = $this->createPartialMock(
            CouponSearchResultInterfaceFactory::class,
            ['create']
        );
        $this->searchResultsMock = $this->getMockForAbstractClass(CouponSearchResultInterface::class);
        $this->couponFactory = $this->createPartialMock(CouponFactory::class, ['create']);
        $this->ruleFactory = $this->createPartialMock(RuleFactory::class, ['create']);
        $this->collectionFactory = $this->createPartialMock(
            CollectionFactory::class,
            ['create']
        );
        $this->resource = $this->createMock(Coupon::class);
        $this->extensionAttributesJoinProcessorMock = $this->createPartialMock(
            JoinProcessor::class,
            ['process']
        );

        $this->collectionProcessor = $this->createMock(
            CollectionProcessorInterface::class
        );

        $this->model = $this->objectManager->getObject(
            CouponRepository::class,
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
        $coupon = $this->getMockBuilder(\Magento\SalesRule\Model\Coupon::class)->addMethods(['getById'])
            ->onlyMethods(['load', 'getCouponId'])
            ->disableOriginalConstructor()
            ->getMock();
        $coupon->expects($this->any())->method('load')->with($id)->willReturnSelf();
        $coupon->expects($this->any())->method('getCouponId')->willReturn($id);
        $this->couponFactory->expects($this->once())->method('create')->willReturn($coupon);

        /**
         * @var Rule $rule
         */
        $rule = $this->getMockBuilder(Rule::class)
            ->addMethods(['getRuleId'])
            ->onlyMethods(['load'])
            ->disableOriginalConstructor()
            ->getMock();

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
     * @throws LocalizedException
     */
    public function testSaveWithExceptions($exceptionObject, $exceptionName, $exceptionMessage, $id)
    {
        /**
         * @var \Magento\SalesRule\Model\Coupon $coupon
         */
        $coupon = $this->createMock(\Magento\SalesRule\Model\Coupon::class);

        /**
         * @var Rule $rule
         */
        $rule = $this->getMockBuilder(Rule::class)
            ->addMethods(['getRuleId'])
            ->onlyMethods(['load'])
            ->disableOriginalConstructor()
            ->getMock();

        $rule->expects($this->any())->method('load')->willReturnSelf();
        $rule->expects($this->any())->method('getRuleId')->willReturn($id);

        $this->ruleFactory->expects($this->any())->method('create')->willReturn($rule);

        if ($id) {
            $this->resource->expects($this->once())->method('save')->with($coupon)
                ->willThrowException($exceptionObject);
        }
        $this->expectException($exceptionName);
        $this->expectExceptionMessage($exceptionMessage);
        $this->model->save($coupon);
    }

    /**
     * @return array
     */
    public static function saveExceptionsDataProvider()
    {
        $msg = 'kiwis';
        $phrase = new Phrase($msg);

        return [
            [
                new LocalizedException($phrase),
                LocalizedException::class,
                $msg,
                1
            ],
            [
                null, LocalizedException::class,
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
        $couponMock = $this->getMockForAbstractClass(CouponInterface::class);
        /**
         * @var SearchCriteriaInterface $searchCriteriaMock
         */
        $searchCriteriaMock = $this->createMock(SearchCriteria::class);
        $collectionMock = $this->createMock(Collection::class);
        $this->extensionAttributesJoinProcessorMock->expects($this->once())
            ->method('process')
            ->with($collectionMock, CouponInterface::class);
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
