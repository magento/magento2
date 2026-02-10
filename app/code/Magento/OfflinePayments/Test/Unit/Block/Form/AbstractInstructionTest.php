<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\OfflinePayments\Test\Unit\Block\Form;

use Magento\OfflinePayments\Block\Form\AbstractInstruction;
use Magento\Payment\Model\MethodInterface;
use PHPUnit\Framework\TestCase;

class AbstractInstructionTest extends TestCase
{
    /**
     * @var AbstractInstruction
     */
    protected $model;

    protected function setUp(): void
    {
        $this->model = $this->getMockBuilder(AbstractInstruction::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();
    }

    public function testGetInstructions()
    {
        $method = $this->createMock(MethodInterface::class);

        $method->expects($this->once())
            ->method('getConfigData')
            ->willReturn('instructions');
        $this->model->setData('method', $method);

        $this->assertEquals('instructions', $this->model->getInstructions());
    }
}
