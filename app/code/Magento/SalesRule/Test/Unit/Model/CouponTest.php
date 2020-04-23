<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Test\Unit\Model;

use Magento\Framework\Event\Manager;
use Magento\Framework\Model\Context;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\SalesRule\Model\ResourceModel\Coupon;
use Magento\SalesRule\Model\Rule;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CouponTest extends TestCase
{
    /**
     * @var Coupon|MockObject
     */
    protected $resourceMock;

    /**
     * @var Manager|MockObject
     */
    protected $eventManager;

    /**
     * @var \Magento\SalesRule\Model\Coupon
     */
    protected $couponModel;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->resourceMock = $this->createPartialMock(
            Coupon::class,
            ['loadPrimaryByRule', 'load', 'getIdFieldName']
        );
        $this->eventManager = $this->createPartialMock(Manager::class, ['dispatch']);

        $context = $this->createPartialMock(Context::class, ['getEventDispatcher']);

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
        /** @var Rule|MockObject $ruleMock */
        $ruleMock = $this->createPartialMock(Rule::class, ['getId']);
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
