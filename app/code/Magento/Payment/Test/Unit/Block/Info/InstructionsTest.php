<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Payment\Test\Unit\Block\Info;

use Magento\Framework\View\Element\Template\Context;
use Magento\Payment\Block\Info\Instructions;
use Magento\Payment\Model\Info;
use Magento\Payment\Model\MethodInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class InstructionsTest extends TestCase
{
    /**
     * @var Info|MockObject
     */
    private $infoMock;

    /**
     * @var Instructions
     */
    private $instructions;

    protected function setUp()
    {
        $context = $this->createMock(Context::class);
        $this->instructions = new Instructions($context);
        $this->infoMock = $this->createMock(Info::class);
        $this->instructions->setData('info', $this->infoMock);
    }

    public function testGetInstructionAdditionalInformation()
    {
        $this->infoMock->expects($this->once())
            ->method('getAdditionalInformation')
            ->with('instructions')
            ->willReturn('get the instruction here');
        $this->assertEquals('get the instruction here', $this->instructions->getInstructions());

        // And we get the already setted param $this->_instructions
        $this->assertEquals('get the instruction here', $this->instructions->getInstructions());
    }

    public function testGetInstruction()
    {
        $methodInstance = $this->getMockBuilder(
            MethodInterface::class
        )->getMockForAbstractClass();
        $methodInstance->expects($this->once())
            ->method('getConfigData')
            ->with('instructions')
            ->willReturn('get the instruction here');
        $this->infoMock->expects($this->once())
            ->method('getAdditionalInformation')
            ->with('instructions')
            ->willReturn(false);
        $this->infoMock->expects($this->once())
            ->method('getMethodInstance')
            ->willReturn($methodInstance);
        $this->assertEquals('get the instruction here', $this->instructions->getInstructions());
    }
}
