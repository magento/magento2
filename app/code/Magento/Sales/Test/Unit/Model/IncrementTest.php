<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model;

use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Type;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Model\Increment;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class IncrementTest extends TestCase
{
    /**
     * @var Increment
     */
    protected $model;

    /**
     * @var Config|MockObject
     */
    protected $eavConfig;

    /**
     * @var Type|MockObject
     */
    protected $type;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->eavConfig = $this->createPartialMock(Config::class, ['getEntityType']);
        $this->model = $objectManager->getObject(
            Increment::class,
            ['eavConfig' => $this->eavConfig]
        );
        $this->type = $this->createPartialMock(Type::class, ['fetchNewIncrementId']);
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
