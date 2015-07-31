<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Test\Unit\Model\Service;

/**
 * Class CouponManagementServiceTest
 */
class CouponManagementServiceTest extends \PHPUnit_Framework_TestCase
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
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * Setup the test
     */
    protected function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);


        $className = '\Magento\SalesRule\Model\CouponFactory';
        $this->couponFactory = $this->getMock($className, [], [], '', false);


        $className = '\Magento\SalesRule\Model\RuleFactory';
        $this->ruleFactory = $this->getMock($className, ['create'], [], '', false);


        $className = '\Magento\SalesRule\Model\Resource\Coupon\CollectionFactory';
        $this->collectionFactory = $this->getMock($className, ['create'], [], '', false);


        $className = '\Magento\SalesRule\Model\Coupon\Massgenerator';
        $this->couponGenerator = $this->getMock($className, [], [], '', false);


        $className = '\Magento\SalesRule\Model\Spi\CouponResourceInterface';
        $this->resourceModel = $this->getMock($className, [], [], '', false);


        $this->model = $this->objectManager->getObject(
            'Magento\SalesRule\Model\Service\CouponManagementService',
            [
                'couponFactory' => $this->couponFactory,
                'ruleFactory' => $this->ruleFactory,
                'collectionFactory' => $this->collectionFactory,
                'couponGenerator' => $this->couponGenerator,
                'resourceModel' => $this->resourceModel,
            ]
        );
    }

    /**
     * test Generate
     */
    public function testGenerate()
    {
        $className = 'Magento\SalesRule\Model\Data\CouponGenerationSpec';
        /**
         * @var \Magento\SalesRule\Api\Data\CouponGenerationSpecInterface $couponSpec
         */
        $couponSpec = $this->getMock(
            $className,
            [
            'getRuleId',
            'getQuantity',
            'getFormat',
            'getLength',
            'getExpirationDate',
            'getUsagePerCoupon',
            'getUsagePerCustomer',
            'setData'
            ],
            [],
            '',
            false
        );

        $couponSpec->expects($this->once())->method('getRuleId')->willReturn(1);
        $couponSpec->expects($this->once())->method('getQuantity')->willReturn(1);
        $couponSpec->expects($this->once())->method('getFormat')->willReturn('num');
        $couponSpec->expects($this->once())->method('getLength')->willReturn(1);
        $couponSpec->expects($this->once())->method('getExpirationDate')->willReturn('2015-07-31 00:00:00');
        $couponSpec->expects($this->once())->method('getUsagePerCoupon')->willReturn(1);
        $couponSpec->expects($this->once())->method('getUsagePerCustomer')->willReturn(1);

        $this->couponGenerator->expects($this->once())->method('validateData')->with([
            'rule_id' => 1,
            'qty' => 1,
            'format' => 'num',
            'length' => 1,
            'to_date' => '2015-07-31 00:00:00',
            'uses_per_coupon' => 1,
            'uses_per_customer' => 1
        ])->willReturn(true);

        $this->couponGenerator->expects($this->once())->method('setData');
        $this->couponGenerator->expects($this->once())->method('generatePool');
        $this->couponGenerator->expects($this->once())->method('getGeneratedCodes')->willReturn([]);

        /**
         * @var \Magento\SalesRule\Model\Rule $rule
         */
        $rule = $this->getMock('\Magento\SalesRule\Model\Rule', ['load', 'getRuleId'], [], '', false);

        $rule->expects($this->any())->method('load')->willReturnSelf();
        $rule->expects($this->any())->method('getRuleId')->willReturn(1);

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
        $className = '\Magento\SalesRule\Api\Data\CouponGenerationSpecInterface';
        /**
         * @var \Magento\SalesRule\Api\Data\CouponGenerationSpecInterface $couponSpec
         */
        $couponSpec = $this->getMock($className, [], [], '', false);

        /**
         * @var \Magento\SalesRule\Model\Rule $rule
         */
        $rule = $this->getMock('\Magento\SalesRule\Model\Rule', ['load', 'getRuleId'], [], '', false);

        $rule->expects($this->any())->method('load')->willReturnSelf();
        $rule->expects($this->any())->method('getRuleId')->willReturn(1);

        $this->ruleFactory->expects($this->any())->method('create')->willReturn($rule);

        $this->couponGenerator->expects($this->once())->method('validateData')
            ->willThrowException(new \Magento\Framework\Exception\InputException());
        $this->setExpectedException('\Magento\Framework\Exception\InputException');

        $this->model->generate($couponSpec);
    }

    /**
     * test Generate with localized Exception
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function testGenerateLocalizedException()
    {
        $className = '\Magento\SalesRule\Api\Data\CouponGenerationSpecInterface';
        /**
         * @var \Magento\SalesRule\Api\Data\CouponGenerationSpecInterface $couponSpec
         */
        $couponSpec = $this->getMock($className, [], [], '', false);

        /**
         * @var \Magento\SalesRule\Model\Rule $rule
         */
        $rule = $this->getMock('\Magento\SalesRule\Model\Rule', ['load', 'getRuleId'], [], '', false);

        $rule->expects($this->any())->method('load')->willReturnSelf();
        $rule->expects($this->any())->method('getRuleId')->willReturn(1);

        $this->ruleFactory->expects($this->any())->method('create')->willReturn($rule);

        $this->couponGenerator->expects($this->once())->method('validateData')->willReturn(true);
        $this->couponGenerator->expects($this->once())->method('generatePool')
            ->willThrowException(
                new \Magento\Framework\Exception\LocalizedException(
                    __('Error occurred when generating coupons: %1', '1')
                )
            );
        $this->setExpectedException('\Magento\Framework\Exception\LocalizedException');

        $this->model->generate($couponSpec);
    }

    /**
     * test Generate with localized Exception
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function testGenerateNoSuchEntity()
    {
        $className = '\Magento\SalesRule\Api\Data\CouponGenerationSpecInterface';
        /**
         * @var \Magento\SalesRule\Api\Data\CouponGenerationSpecInterface $couponSpec
         */
        $couponSpec = $this->getMock($className, [], [], '', false);

        /**
         * @var \Magento\SalesRule\Model\Rule $rule
         */
        $rule = $this->getMock('\Magento\SalesRule\Model\Rule', ['load', 'getRuleId'], [], '', false);

        $rule->expects($this->any())->method('load')->willReturnSelf();
        $rule->expects($this->any())->method('getRuleId')->willReturn(false);

        $this->ruleFactory->expects($this->any())->method('create')->willReturn($rule);

        $this->couponGenerator->expects($this->once())->method('validateData')->willReturn(true);

        $this->setExpectedException('\Magento\Framework\Exception\LocalizedException');

        $this->model->generate($couponSpec);
    }

    /**
     * test DeleteByIds with Ignore non existing
     */
    public function testDeleteByIdsIgnore()
    {
        $ids = [1, 2, 3];

        $className = '\Magento\SalesRule\Model\Coupon';
        /**
         * @var   \Magento\SalesRule\Model\Coupon $coupon
         */
        $coupon = $this->getMock($className, [], [], '', false);
        $coupon->expects($this->exactly(3))->method('delete');

        $className = '\Magento\SalesRule\Model\Resource\Coupon\Collection';
        /**
         * @var  \Magento\SalesRule\Model\Resource\Coupon\Collection $couponCollection
         */
        $couponCollection = $this->getMock($className, [], [], '', false);

        $couponCollection->expects($this->once())->method('addFieldToFilter')->willReturnSelf();
        $couponCollection->expects($this->once())->method('getItems')->willReturn([$coupon, $coupon, $coupon]);
        $this->collectionFactory->expects($this->once())->method('create')->willReturn($couponCollection);

        $this->model->deleteByIds($ids, true);
    }

    /**
     * test DeleteByIds with not Ignore non existing
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function testDeleteByAnyNoIgnore()
    {
        $ids = [1, 2, 3];

        $className = '\Magento\SalesRule\Model\Resource\Coupon\Collection';
        /**
         * @var  \Magento\SalesRule\Model\Resource\Coupon\Collection $couponCollection
         */
        $couponCollection = $this->getMock($className, [], [], '', false);
        $couponCollection->expects($this->once())->method('addFieldToFilter')->willReturnSelf();
        $this->collectionFactory->expects($this->once())->method('create')->willReturn($couponCollection);

        $this->setExpectedException('\Magento\Framework\Exception\LocalizedException');

        $this->model->deleteByIds($ids, false);
    }

    /**
     * test DeleteByIds with not Ignore non existing
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function testDeleteByAnyExceptionOnDelete()
    {
        $ids = [1, 2, 3];

        $className = '\Magento\SalesRule\Model\Coupon';
        /**
         * @var   \Magento\SalesRule\Model\Coupon $coupon
         */
        $coupon = $this->getMock($className, [], [], '', false);
        $coupon->expects($this->any())->method('delete')
            ->willThrowException(
                new \Magento\Framework\Exception\LocalizedException(
                    __('Error occurred when deleting coupons: %1.', '1')
                )
            );
        $className = '\Magento\SalesRule\Model\Resource\Coupon\Collection';
        /**
         * @var  \Magento\SalesRule\Model\Resource\Coupon\Collection $couponCollection
         */
        $couponCollection = $this->getMock($className, [], [], '', false);

        $couponCollection->expects($this->once())->method('addFieldToFilter')->willReturnSelf();
        $couponCollection->expects($this->once())->method('getItems')->willReturn([$coupon, $coupon, $coupon]);
        $this->collectionFactory->expects($this->once())->method('create')->willReturn($couponCollection);
        $this->setExpectedException('\Magento\Framework\Exception\LocalizedException');


        $this->model->deleteByIds($ids, true);
    }

    /**
     * test DeleteByCodes
     */
    public function testDeleteByCodes()
    {
        $ids = [1, 2, 3];

        $className = '\Magento\SalesRule\Model\Coupon';
        /**
         * @var   \Magento\SalesRule\Model\Coupon $coupon
         */
        $coupon = $this->getMock($className, [], [], '', false);
        $coupon->expects($this->exactly(3))->method('delete');

        $className = '\Magento\SalesRule\Model\Resource\Coupon\Collection';
        /**
         * @var  \Magento\SalesRule\Model\Resource\Coupon\Collection $couponCollection
         */
        $couponCollection = $this->getMock($className, [], [], '', false);

        $couponCollection->expects($this->once())->method('addFieldToFilter')->willReturnSelf();
        $couponCollection->expects($this->once())->method('getItems')->willReturn([$coupon, $coupon, $coupon]);
        $this->collectionFactory->expects($this->once())->method('create')->willReturn($couponCollection);

        $this->model->deleteByCodes($ids, true);
    }
}
