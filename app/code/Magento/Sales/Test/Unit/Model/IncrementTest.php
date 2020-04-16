<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Model;

/**
 * Class IncrementTest
 */
class IncrementTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Sales\Model\Increment
     */
    protected $model;

    /**
     * @var \Magento\Eav\Model\Config|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $eavConfig;

    /**
     * @var \Magento\Eav\Model\Entity\Type|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $type;

    protected function setUp(): void
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->eavConfig = $this->createPartialMock(\Magento\Eav\Model\Config::class, ['getEntityType']);
        $this->model = $objectManager->getObject(
            \Magento\Sales\Model\Increment::class,
            ['eavConfig' => $this->eavConfig]
        );
        $this->type = $this->createPartialMock(\Magento\Eav\Model\Entity\Type::class, ['fetchNewIncrementId']);
    }

    public function testGetCurrentValue()
    {
        $this->type->expects($this->once())
            ->method('fetchNewIncrementId')
            ->with(1)
            ->willReturn(2);
        $this->eavConfig->expects($this->once())
            ->method('getEntityType')
            ->with('order')
            ->willReturn($this->type);
        $this->model->getNextValue(1);
        $this->assertEquals(2, $this->model->getCurrentValue());
    }

    public function testNextValue()
    {
        $this->type->expects($this->once())
            ->method('fetchNewIncrementId')
            ->with(1)
            ->willReturn(2);
        $this->eavConfig->expects($this->once())
            ->method('getEntityType')
            ->with('order')
            ->willReturn($this->type);
        $this->assertEquals(2, $this->model->getNextValue(1));
    }
}
