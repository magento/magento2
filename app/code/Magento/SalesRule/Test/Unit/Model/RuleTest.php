<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SalesRule\Test\Unit\Model;

use Magento\Framework\Unserialize\SecureUnserializer;
use Magento\Framework\ObjectManagerInterface;

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
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $objectManager;

    /**
     * @var SecureUnserializer
     */
    private $unserialize;

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

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

        $this->prepareObjectManager();

        $this->model = $this->objectManager->getObject(
            \Magento\SalesRule\Model\Rule::class,
            [
                'couponFactory' => $couponFactory,
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

    /**
     * Prepares ObjectManager mock.
     *
     * @return void
     */
    private function prepareObjectManager()
    {
        $objectManagerMock = $this->getMockBuilder(ObjectManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->unserialize =  $this->getMockBuilder(SecureUnserializer::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManagerMock->expects($this->any())->method('get')->willReturn(
            [SecureUnserializer::class, $this->unserialize]
        );

        \Magento\Framework\App\ObjectManager::setInstance($objectManagerMock);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown()
    {
        $reflectionClass = new \ReflectionClass(\Magento\Framework\App\ObjectManager::class);
        $reflectionProperty = $reflectionClass->getProperty('_instance');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue(null, null);
    }
}
