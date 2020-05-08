<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test class for \Magento\Payment\Block\Info\Instructions
 */
namespace Magento\Payment\Test\Unit\Block\Info;

class InstructionsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Payment\Model\Info|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_info;

    /**
     * @var \Magento\Payment\Block\Info\Instructions
     */
    protected $_instructions;

    protected function setUp()
    {
        $context = $this->createMock(\Magento\Framework\View\Element\Template\Context::class);
        $this->_instructions = new \Magento\Payment\Block\Info\Instructions($context);
        $this->_info = $this->createMock(\Magento\Payment\Model\Info::class);
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
            \Magento\Payment\Model\MethodInterface::class
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
