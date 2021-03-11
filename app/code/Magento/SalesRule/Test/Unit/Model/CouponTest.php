<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Test\Unit\Model;

/**
 * Class CouponTest
 */
class CouponTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\SalesRule\Model\ResourceModel\Coupon|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $resourceMock;

    /**
     * @var \Magento\Framework\Event\Manager|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $eventManager;

    /**
     * @var \Magento\SalesRule\Model\Coupon
     */
    protected $couponModel;

    protected function setUp(): void
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->resourceMock = $this->createPartialMock(
            \Magento\SalesRule\Model\ResourceModel\Coupon::class,
            ['loadPrimaryByRule', 'load', '__wakeup', 'getIdFieldName']
        );
        $this->eventManager = $this->createPartialMock(\Magento\Framework\Event\Manager::class, ['dispatch']);

        $context = $this->createPartialMock(\Magento\Framework\Model\Context::class, ['getEventDispatcher']);

        $context->expects($this->once())->method('getEventDispatcher')->willReturn($this->eventManager);

        $this->couponModel = $objectManager->getObject(
            \Magento\SalesRule\Model\Coupon::class,
            [
                'resource' => $this->resourceMock,
                'context' => $context
            ]
        );
    }

    /**
     * Run test setRule method
     */
    public function testSetRule()
    {
        /** @var \Magento\SalesRule\Model\Rule|\PHPUnit\Framework\MockObject\MockObject $ruleMock */
        $ruleMock = $this->createPartialMock(\Magento\SalesRule\Model\Rule::class, ['getId', '__wakeup']);
        $ruleMock->expects($this->once())->method('getId');

        $this->assertEquals($this->couponModel, $this->couponModel->setRule($ruleMock));
    }

    /**
     * Run test loadPrimaryByRule method
     */
    public function testLoadPrimaryByRule()
    {
        $this->resourceMock->expects($this->once())->method('loadPrimaryByRule');

        $this->assertEquals($this->couponModel, $this->couponModel->loadPrimaryByRule(1));
    }

    /**
     * Run test loadByCode method
     */
    public function testLoadByCode()
    {
        $this->eventManager->expects($this->any())->method('dispatch');
        $this->resourceMock->expects($this->once())->method('load');

        $this->assertEquals($this->couponModel, $this->couponModel->loadByCode('code-value'));
    }
}
