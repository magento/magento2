<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Test\Unit\Model;

class RuleTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\SalesRule\Model\Rule
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $coupon;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\SalesRule\Model\Rule\Condition\CombineFactory
     */
    protected $conditionCombineFactoryMock;

    /**
     * @var \Magento\SalesRule\Model\Rule\Condition\Product\CombineFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $condProdCombineFactoryMock;

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->coupon = $this->getMockBuilder(\Magento\SalesRule\Model\Coupon::class)
            ->disableOriginalConstructor()
            ->setMethods(['loadPrimaryByRule', 'setRule', 'setIsPrimary', 'getCode', 'getUsageLimit'])
            ->getMock();

        $couponFactory = $this->getMockBuilder(\Magento\SalesRule\Model\CouponFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $couponFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->coupon);

        $this->conditionCombineFactoryMock = $this->getMockBuilder(
            \Magento\SalesRule\Model\Rule\Condition\CombineFactory::class
        )->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->condProdCombineFactoryMock = $this->getMockBuilder(
            \Magento\SalesRule\Model\Rule\Condition\Product\CombineFactory::class
        )->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->model = $objectManager->getObject(
            \Magento\SalesRule\Model\Rule::class,
            [
                'couponFactory' => $couponFactory,
                'condCombineFactory' => $this->conditionCombineFactoryMock,
                'condProdCombineF' => $this->condProdCombineFactoryMock,
            ]
        );
    }

    public function testLoadCouponCode()
    {
        $this->coupon->expects($this->once())
            ->method('loadPrimaryByRule')
            ->with(1);
        $this->coupon->expects($this->once())
            ->method('setRule')
            ->with($this->model)
            ->willReturnSelf();
        $this->coupon->expects($this->once())
            ->method('setIsPrimary')
            ->with(true)
            ->willReturnSelf();
        $this->coupon->expects($this->once())
            ->method('getCode')
            ->willReturn('test_code');
        $this->coupon->expects($this->once())
            ->method('getUsageLimit')
            ->willReturn(1);

        $this->model->setId(1);
        $this->model->setUsesPerCoupon(null);
        $this->model->setUseAutoGeneration(false);

        $this->model->loadCouponCode();
        $this->assertEquals(1, $this->model->getUsesPerCoupon());
    }

    public function testBeforeSaveResetConditionToNull()
    {
        $conditionMock = $this->setupConditionMock();

        //Make sure that we reset _condition in beforeSave method
        $this->conditionCombineFactoryMock->expects($this->exactly(2))
            ->method('create')
            ->willReturn($conditionMock);

        $prodConditionMock = $this->setupProdConditionMock();
        $this->condProdCombineFactoryMock->expects($this->exactly(2))
            ->method('create')
            ->willReturn($prodConditionMock);

        $this->model->beforeSave();
        $this->model->getConditions();
        $this->model->getActions();
    }

    protected function setupProdConditionMock()
    {
        $prodConditionMock = $this->getMockBuilder(\Magento\SalesRule\Model\Rule\Condition\Product\Combine::class)
            ->disableOriginalConstructor()
            ->setMethods(['setRule', 'setId', 'loadArray', 'getConditions'])
            ->getMock();

        $prodConditionMock->expects($this->any())
            ->method('setRule')
            ->willReturnSelf();
        $prodConditionMock->expects($this->any())
            ->method('setId')
            ->willReturnSelf();
        $prodConditionMock->expects($this->any())
            ->method('getConditions')
            ->willReturn([]);

        return $prodConditionMock;
    }

    protected function setupConditionMock()
    {
        $conditionMock = $this->getMockBuilder(\Magento\SalesRule\Model\Rule\Condition\Combine::class)
            ->disableOriginalConstructor()
            ->setMethods(['setRule', 'setId', 'loadArray', 'getConditions'])
            ->getMock();
        $conditionMock->expects($this->any())
            ->method('setRule')
            ->willReturnSelf();
        $conditionMock->expects($this->any())
            ->method('setId')
            ->willReturnSelf();
        $conditionMock->expects($this->any())
            ->method('getConditions')
            ->willReturn([]);

        return $conditionMock;
    }

    public function testGetConditionsFieldSetId()
    {
        $formName = 'form_name';
        $this->model->setId(100);
        $expectedResult = 'form_namerule_conditions_fieldset_100';
        $this->assertEquals($expectedResult, $this->model->getConditionsFieldSetId($formName));
    }

    public function testGetActionsFieldSetId()
    {
        $formName = 'form_name';
        $this->model->setId(100);
        $expectedResult = 'form_namerule_actions_fieldset_100';
        $this->assertEquals($expectedResult, $this->model->getActionsFieldSetId($formName));
    }
}
