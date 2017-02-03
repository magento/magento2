<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test class for \Magento\Payment\Block\Info\Instructions
 */
namespace Magento\Payment\Test\Unit\Block\Info;

class InstructionsTest extends \PHPUnit_Framework_TestCase
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
        $context = $this->getMock('Magento\Framework\View\Element\Template\Context', [], [], '', false);
        $this->_instructions = new \Magento\Payment\Block\Info\Instructions($context);
        $this->_info = $this->getMock('Magento\Payment\Model\Info', [], [], '', false);
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
            'Magento\Payment\Model\MethodInterface'
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
