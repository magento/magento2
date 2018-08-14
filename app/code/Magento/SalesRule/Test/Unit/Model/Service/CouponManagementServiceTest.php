<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Test\Unit\Model\Service;

/**
 * Class CouponManagementServiceTest
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CouponManagementServiceTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\SalesRule\Model\Service\CouponManagementService
     */
    protected $model;

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
    protected $couponGenerator;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $resourceModel;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $couponMassDeleteResultFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $couponMassDeleteResult;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * Setup the test
     */
    protected function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $className = \Magento\SalesRule\Model\CouponFactory::class;
        $this->couponFactory = $this->createMock($className);

        $className = \Magento\SalesRule\Model\RuleFactory::class;
        $this->ruleFactory = $this->createPartialMock($className, ['create']);

        $className = \Magento\SalesRule\Model\ResourceModel\Coupon\CollectionFactory::class;
        $this->collectionFactory = $this->createPartialMock($className, ['create']);

        $className = \Magento\SalesRule\Model\Coupon\Massgenerator::class;
        $this->couponGenerator = $this->createMock($className);

        $className = \Magento\SalesRule\Model\Spi\CouponResourceInterface::class;
        $this->resourceModel = $this->createMock($className);

        $className = \Magento\SalesRule\Api\Data\CouponMassDeleteResultInterface::class;
        $this->couponMassDeleteResult = $this->createMock($className);

        $className = \Magento\SalesRule\Api\Data\CouponMassDeleteResultInterfaceFactory::class;
        $this->couponMassDeleteResultFactory = $this->createPartialMock($className, ['create']);
        $this->couponMassDeleteResultFactory
            ->expects($this->any())
            ->method('create')
            ->willReturn($this->couponMassDeleteResult);

        $this->model = $this->objectManager->getObject(
            \Magento\SalesRule\Model\Service\CouponManagementService::class,
            [
                'couponFactory' => $this->couponFactory,
                'ruleFactory' => $this->ruleFactory,
                'collectionFactory' => $this->collectionFactory,
                'couponGenerator' => $this->couponGenerator,
                'resourceModel' => $this->resourceModel,
                'couponMassDeleteResultFactory' => $this->couponMassDeleteResultFactory,
            ]
        );
    }

    /**
     * test Generate
     */
    public function testGenerate()
    {
        $className = \Magento\SalesRule\Model\Data\CouponGenerationSpec::class;
        /**
         * @var \Magento\SalesRule\Api\Data\CouponGenerationSpecInterface $couponSpec
         */
        $couponSpec = $this->createPartialMock(
            $className,
            ['getRuleId', 'getQuantity', 'getFormat', 'getLength', 'setData']
        );

        $couponSpec->expects($this->atLeastOnce())->method('getRuleId')->willReturn(1);
        $couponSpec->expects($this->once())->method('getQuantity')->willReturn(1);
        $couponSpec->expects($this->once())->method('getFormat')->willReturn('num');
        $couponSpec->expects($this->once())->method('getLength')->willReturn(1);

        $this->couponGenerator->expects($this->atLeastOnce())->method('setData');
        $this->couponGenerator->expects($this->once())->method('validateData')->willReturn(true);
        $this->couponGenerator->expects($this->once())->method('generatePool');
        $this->couponGenerator->expects($this->once())->method('getGeneratedCodes')->willReturn([]);

        /**
         * @var \Magento\SalesRule\Model\Rule $rule
         */
        $rule = $this->createPartialMock(
            \Magento\SalesRule\Model\Rule::class,
            ['load', 'getRuleId', 'getToDate', 'getUsesPerCoupon', 'getUsesPerCustomer', 'getUseAutoGeneration']
        );

        $rule->expects($this->any())->method('load')->willReturnSelf();
        $rule->expects($this->any())->method('getRuleId')->willReturn(1);
        $rule->expects($this->any())->method('getToDate')->willReturn('2015-07-31 00:00:00');
        $rule->expects($this->any())->method('getUsesPerCoupon')->willReturn(20);
        $rule->expects($this->any())->method('getUsesPerCustomer')->willReturn(5);
        $rule->expects($this->any())->method('getUseAutoGeneration')->willReturn(true);

        $this->ruleFactory->expects($this->any())->method('create')->willReturn($rule);

        $result =  $this->model->generate($couponSpec);
        $this->assertEquals([], $result);
    }

    /**
     * test Generate with validation Exception
     * @throws \Magento\Framework\Exception\InputException
     */
    public function testGenerateValidationException()
    {
        $className = \Magento\SalesRule\Api\Data\CouponGenerationSpecInterface::class;
        /**
         * @var \Magento\SalesRule\Api\Data\CouponGenerationSpecInterface $couponSpec
         */
        $couponSpec = $this->createMock($className);

        /**
         * @var \Magento\SalesRule\Model\Rule $rule
         */
        $rule = $this->createPartialMock(\Magento\SalesRule\Model\Rule::class, ['load', 'getRuleId']);

        $rule->expects($this->any())->method('load')->willReturnSelf();
        $rule->expects($this->any())->method('getRuleId')->willReturn(1);

        $this->ruleFactory->expects($this->any())->method('create')->willReturn($rule);

        $this->couponGenerator->expects($this->once())->method('validateData')
            ->willThrowException(new \Magento\Framework\Exception\InputException());
        $this->expectException(\Magento\Framework\Exception\InputException::class);

        $this->model->generate($couponSpec);
    }

    /**
     * test Generate with localized Exception
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function testGenerateLocalizedException()
    {
        $className = \Magento\SalesRule\Api\Data\CouponGenerationSpecInterface::class;
        /**
         * @var \Magento\SalesRule\Api\Data\CouponGenerationSpecInterface $couponSpec
         */
        $couponSpec = $this->createMock($className);

        /**
         * @var \Magento\SalesRule\Model\Rule $rule
         */
        $rule = $this->createPartialMock(
            \Magento\SalesRule\Model\Rule::class,
            ['load', 'getRuleId', 'getUseAutoGeneration']
        );
        $rule->expects($this->any())->method('load')->willReturnSelf();
        $rule->expects($this->any())->method('getRuleId')->willReturn(1);
        $rule->expects($this->once())->method('getUseAutoGeneration')
            ->willThrowException(
                new \Magento\Framework\Exception\LocalizedException(
                    __('Error occurred when generating coupons: %1', '1')
                )
            );
        $this->ruleFactory->expects($this->any())->method('create')->willReturn($rule);

        $this->couponGenerator->expects($this->once())->method('validateData')->willReturn(true);

        $this->expectException(\Magento\Framework\Exception\LocalizedException::class);

        $this->model->generate($couponSpec);
    }

    /**
     * test Generate with localized Exception
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function testGenerateNoSuchEntity()
    {
        $className = \Magento\SalesRule\Api\Data\CouponGenerationSpecInterface::class;
        /**
         * @var \Magento\SalesRule\Api\Data\CouponGenerationSpecInterface $couponSpec
         */
        $couponSpec = $this->createMock($className);

        /**
         * @var \Magento\SalesRule\Model\Rule $rule
         */
        $rule = $this->createPartialMock(\Magento\SalesRule\Model\Rule::class, ['load', 'getRuleId']);

        $rule->expects($this->any())->method('load')->willReturnSelf();
        $rule->expects($this->any())->method('getRuleId')->willReturn(false);

        $this->ruleFactory->expects($this->any())->method('create')->willReturn($rule);

        $this->couponGenerator->expects($this->once())->method('validateData')->willReturn(true);

        $this->expectException(\Magento\Framework\Exception\LocalizedException::class);

        $this->model->generate($couponSpec);
    }

    /**
     * test DeleteByIds with Ignore non existing
     */
    public function testDeleteByIdsIgnore()
    {
        $ids = [1, 2, 3];

        $className = \Magento\SalesRule\Model\Coupon::class;
        /**
         * @var   \Magento\SalesRule\Model\Coupon $coupon
         */
        $coupon = $this->createMock($className);
        $coupon->expects($this->exactly(3))->method('delete');

        $className = \Magento\SalesRule\Model\ResourceModel\Coupon\Collection::class;
        /**
         * @var  \Magento\SalesRule\Model\ResourceModel\Coupon\Collection $couponCollection
         */
        $couponCollection = $this->createMock($className);

        $couponCollection->expects($this->once())->method('addFieldToFilter')->willReturnSelf();
        $couponCollection->expects($this->once())->method('getItems')->willReturn([$coupon, $coupon, $coupon]);
        $this->collectionFactory->expects($this->once())->method('create')->willReturn($couponCollection);

        $this->couponMassDeleteResult->expects($this->once())->method('setFailedItems')->willReturnSelf();
        $this->couponMassDeleteResult->expects($this->once())->method('setMissingItems')->willReturnSelf();

        $this->model->deleteByIds($ids, true);
    }

    /**
     * test DeleteByIds with not Ignore non existing
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function testDeleteByAnyNoIgnore()
    {
        $ids = [1, 2, 3];

        $className = \Magento\SalesRule\Model\ResourceModel\Coupon\Collection::class;
        /**
         * @var  \Magento\SalesRule\Model\ResourceModel\Coupon\Collection $couponCollection
         */
        $couponCollection = $this->createMock($className);
        $couponCollection->expects($this->once())->method('addFieldToFilter')->willReturnSelf();
        $this->collectionFactory->expects($this->once())->method('create')->willReturn($couponCollection);

        $this->expectException(\Magento\Framework\Exception\LocalizedException::class);

        $this->model->deleteByIds($ids, false);
    }

    /**
     * test DeleteByIds with not Ignore non existing
     */
    public function testDeleteByAnyExceptionOnDelete()
    {
        $ids = [1, 2, 3];

        /**
         * @var   \Magento\SalesRule\Model\Coupon $coupon
         */
        $className = \Magento\SalesRule\Model\Coupon::class;
        $coupon = $this->createMock($className);
        $coupon->expects($this->any())->method('delete')->willThrowException(new \Exception());

        /**
         * @var  \Magento\SalesRule\Model\ResourceModel\Coupon\Collection $couponCollection
         */
        $className = \Magento\SalesRule\Model\ResourceModel\Coupon\Collection::class;
        $couponCollection = $this->createMock($className);
        $couponCollection->expects($this->once())->method('addFieldToFilter')->willReturnSelf();
        $couponCollection->expects($this->once())->method('getItems')->willReturn([$coupon, $coupon, $coupon]);
        $this->collectionFactory->expects($this->once())->method('create')->willReturn($couponCollection);

        $this->couponMassDeleteResult->expects($this->once())->method('setFailedItems')->willReturnSelf();
        $this->couponMassDeleteResult->expects($this->once())->method('setMissingItems')->willReturnSelf();

        $this->model->deleteByIds($ids, true);
    }

    /**
     * test DeleteByCodes
     */
    public function testDeleteByCodes()
    {
        $ids = [1, 2, 3];

        $className = \Magento\SalesRule\Model\Coupon::class;
        /**
         * @var   \Magento\SalesRule\Model\Coupon $coupon
         */
        $coupon = $this->createMock($className);
        $coupon->expects($this->exactly(3))->method('delete');

        $className = \Magento\SalesRule\Model\ResourceModel\Coupon\Collection::class;
        /**
         * @var  \Magento\SalesRule\Model\ResourceModel\Coupon\Collection $couponCollection
         */
        $couponCollection = $this->createMock($className);

        $couponCollection->expects($this->once())->method('addFieldToFilter')->willReturnSelf();
        $couponCollection->expects($this->once())->method('getItems')->willReturn([$coupon, $coupon, $coupon]);
        $this->collectionFactory->expects($this->once())->method('create')->willReturn($couponCollection);

        $this->couponMassDeleteResult->expects($this->once())->method('setFailedItems')->willReturnSelf();
        $this->couponMassDeleteResult->expects($this->once())->method('setMissingItems')->willReturnSelf();

        $this->model->deleteByCodes($ids, true);
    }
}
