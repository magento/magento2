<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
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
        $context = $this->getMock('Magento\Framework\View\Element\Template\Context', [], [], '', false);
        $this->_model = $this->getMockForAbstractClass(
            'Magento\OfflinePayments\Block\Form\AbstractInstruction',
            ['context' => $context]
        );
    }

    public function testGetInstructions()
    {
        $method = $this->getMockBuilder('Magento\Payment\Model\MethodInterface')
            ->getMockForAbstractClass();

        $method->expects($this->once())
            ->method('getConfigData')
            ->willReturn('instructions');
        $this->_model->setData('method', $method);

        $this->assertEquals('instructions', $this->_model->getInstructions());
    }
}
