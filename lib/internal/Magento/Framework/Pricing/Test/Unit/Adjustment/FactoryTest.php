<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Pricing\Test\Unit\Adjustment;

use Magento\Framework\DataObject;
use Magento\Framework\Pricing\Adjustment\AdjustmentInterface;
use Magento\Framework\Pricing\Adjustment\Factory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test class for \Magento\Framework\Pricing\Adjustment\Factory
 */
class FactoryTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
    }

    public function testCreate()
    {
        $adjustmentInterface = AdjustmentInterface::class;
        $adjustmentFactory = $this->prepareAdjustmentFactory($adjustmentInterface);

        $this->assertInstanceOf(
            $adjustmentInterface,
            $adjustmentFactory->create($adjustmentInterface)
        );
    }

    /**
     * @param string $adjustmentInterface
     * @return object
     */
    protected function prepareAdjustmentFactory($adjustmentInterface)
    {
        return $this->objectManager->getObject(
            Factory::class,
            ['objectManager' => $this->prepareObjectManager($adjustmentInterface)]
        );
    }

    /**
     * @param string $adjustmentInterface
     * @return MockObject|\Magento\Framework\ObjectManager\ObjectManager
     */
    protected function prepareObjectManager($adjustmentInterface)
    {
        $objectManager = $this->createPartialMock(\Magento\Framework\ObjectManager\ObjectManager::class, ['create']);
        $objectManager->expects($this->any())
            ->method('create')
            ->willReturn($this->getMockForAbstractClass($adjustmentInterface));
        return $objectManager;
    }

    public function testCreateWithException()
    {
        $this->expectException('InvalidArgumentException');
        $invalidAdjustmentInterface = DataObject::class;
        $adjustmentFactory = $this->prepareAdjustmentFactory($invalidAdjustmentInterface);
        $adjustmentFactory->create($invalidAdjustmentInterface);
    }
}
