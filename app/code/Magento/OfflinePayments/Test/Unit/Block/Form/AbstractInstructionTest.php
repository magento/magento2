<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
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
        $method = $this->getMock(
            'Magento\Payment\Model\MethodInterface',
            ['getInstructions', 'getCode', 'getFormBlockType', 'getTitle'],
            [],
            '',
            false
        );
        $method->expects($this->once())
            ->method('getInstructions')
            ->willReturn('instructions');
        $this->_model->setData('method', $method);

        $this->assertEquals('instructions', $this->_model->getInstructions());
    }
}
