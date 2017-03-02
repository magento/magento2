<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\OfflinePayments\Test\Unit\Block\Form;

class AbstractInstructionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\OfflinePayments\Block\Form\AbstractInstruction
     */
    protected $_model;

    protected function setUp()
    {
        $context = $this->getMock(\Magento\Framework\View\Element\Template\Context::class, [], [], '', false);
        $this->_model = $this->getMockForAbstractClass(
            \Magento\OfflinePayments\Block\Form\AbstractInstruction::class,
            ['context' => $context]
        );
    }

    public function testGetInstructions()
    {
        $method = $this->getMockBuilder(\Magento\Payment\Model\MethodInterface::class)
            ->getMockForAbstractClass();

        $method->expects($this->once())
            ->method('getConfigData')
            ->willReturn('instructions');
        $this->_model->setData('method', $method);

        $this->assertEquals('instructions', $this->_model->getInstructions());
    }
}
