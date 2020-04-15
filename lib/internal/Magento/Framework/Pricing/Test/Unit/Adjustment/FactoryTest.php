<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Pricing\Test\Unit\Adjustment;

/**
 * Test class for \Magento\Framework\Pricing\Adjustment\Factory
 */
class FactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    protected function setUp(): void
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
    }

    public function testCreate()
    {
        $adjustmentInterface = \Magento\Framework\Pricing\Adjustment\AdjustmentInterface::class;
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
            \Magento\Framework\Pricing\Adjustment\Factory::class,
            ['objectManager' => $this->prepareObjectManager($adjustmentInterface)]
        );
    }

    /**
     * @param string $adjustmentInterface
     * @return \PHPUnit\Framework\MockObject\MockObject|\Magento\Framework\ObjectManager\ObjectManager
     */
    protected function prepareObjectManager($adjustmentInterface)
    {
        $objectManager = $this->createPartialMock(\Magento\Framework\ObjectManager\ObjectManager::class, ['create']);
        $objectManager->expects($this->any())
            ->method('create')
            ->willReturn($this->getMockForAbstractClass($adjustmentInterface));
        return $objectManager;
    }

    /**
     */
    public function testCreateWithException()
    {
        $this->expectException(\InvalidArgumentException::class);

        $invalidAdjustmentInterface = \Magento\Framework\DataObject::class;
        $adjustmentFactory = $this->prepareAdjustmentFactory($invalidAdjustmentInterface);
        $adjustmentFactory->create($invalidAdjustmentInterface);
    }
}
