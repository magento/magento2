<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

/**
 * Test class for \Magento\Payment\Block\Info\Instructions
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
    protected $_info;

    /**
     * @var Instructions
     */
    protected $_instructions;

    protected function setUp(): void
    {
        $context = $this->createMock(Context::class);
        $this->_instructions = new Instructions($context);
        $this->_info = $this->createMock(Info::class);
        $this->_instructions->setData('info', $this->_info);
    }

    public function testGetInstructionAdditionalInformation()
    {
        $this->_info->expects($this->once())
            ->method('getAdditionalInformation')
            ->with('instructions')
            ->willReturn('get the instruction here');
        $this->assertEquals('get the instruction here', $this->_instructions->getInstructions());

        // And we get the already setted param $this->_instructions
        $this->assertEquals('get the instruction here', $this->_instructions->getInstructions());
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
        $this->_info->expects($this->once())
            ->method('getAdditionalInformation')
            ->with('instructions')
            ->willReturn(false);
        $this->_info->expects($this->once())
            ->method('getMethodInstance')
            ->willReturn($methodInstance);
        $this->assertEquals('get the instruction here', $this->_instructions->getInstructions());
    }
}
