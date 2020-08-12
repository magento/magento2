<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\OfflinePayments\Test\Unit\Block\Form;

use Magento\Framework\View\Element\Template\Context;
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
        $context = $this->createMock(Context::class);
        $this->model = $this->getMockForAbstractClass(
            AbstractInstruction::class,
            ['context' => $context]
        );
    }

    public function testGetInstructions()
    {
        $method = $this->getMockBuilder(MethodInterface::class)
            ->getMockForAbstractClass();

        $method->expects($this->once())
            ->method('getConfigData')
            ->willReturn('instructions');
        $this->model->setData('method', $method);

        $this->assertEquals('instructions', $this->model->getInstructions());
    }
}
