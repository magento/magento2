<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SalesRule\Test\Unit\Model;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
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

        $this->prepareObjectManager([
            [
                \Magento\Framework\Api\ExtensionAttributesFactory::class,
                $this->getMock(\Magento\Framework\Api\ExtensionAttributesFactory::class, [], [], '', false)
            ],
            [
                \Magento\Framework\Api\AttributeValueFactory::class,
                $this->getMock(\Magento\Framework\Api\AttributeValueFactory::class, [], [], '', false)
            ],
            [
                \Magento\Framework\Unserialize\SecureUnserializer::class,
                $this->getMock(\Magento\Framework\Unserialize\SecureUnserializer::class, [], [], '', false),
            ],
        ]);

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

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
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

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
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

    /**
     * @param $map
     */
    private function prepareObjectManager($map)
    {
        $objectManagerMock = $this->getMock(\Magento\Framework\ObjectManagerInterface::class);
        $objectManagerMock->expects($this->any())->method('getInstance')->willReturnSelf();
        $objectManagerMock->expects($this->any())
            ->method('get')
            ->will($this->returnValueMap($map));
        $reflectionClass = new \ReflectionClass(\Magento\Framework\App\ObjectManager::class);
        $reflectionProperty = $reflectionClass->getProperty('_instance');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($objectManagerMock);
    }
}
