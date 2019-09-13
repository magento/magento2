<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Payment\Test\Unit\Block\Info;

use Magento\Payment\Model\MethodInterface;
use Magento\Sales\Model\Order;
use PHPUnit\Framework\MockObject\MockObject;

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
        $this->_info = $this->getMockBuilder(\Magento\Payment\Model\Info::class)
            ->setMethods(
                [
                    'getOrder',
                    'getAdditionalInformation',
                    'getMethodInstance'
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
        $this->_instructions->setData('info', $this->_info);
    }

    /**
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function testGetTitleFromPaymentAdditionalData()
    {
        $this->_info->method('getAdditionalInformation')
            ->with('method_title')
            ->willReturn('payment_method_title');

        $this->getMethod()->expects($this->never())
            ->method('getConfigData');

        $this->assertEquals($this->_instructions->getTitle(), 'payment_method_title');
    }

    /**
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function testGetTitleFromPaymentMethodConfig()
    {
        $this->_info->method('getAdditionalInformation')
            ->with('method_title')
            ->willReturn(null);

        $this->getMethod()->expects($this->once())
            ->method('getConfigData')
            ->with('title', null)
            ->willReturn('payment_method_title');

        $order = $this->getOrder();
        $this->_info->method('getOrder')->willReturn($order);

        $this->assertEquals($this->_instructions->getTitle(), 'payment_method_title');
    }

    /**
     * @return void
     */
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

    /**
     * @return void
     */
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

    /**
     * @return MethodInterface|MockObject
     */
    private function getMethod()
    {
        $method = $this->getMockBuilder(MethodInterface::class)
            ->getMockForAbstractClass();
        $this->_info->method('getMethodInstance')
            ->willReturn($method);

        return $method;
    }

    /**
     * @return Order|MockObject
     */
    private function getOrder()
    {
        return $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->getMock();
    }
}
