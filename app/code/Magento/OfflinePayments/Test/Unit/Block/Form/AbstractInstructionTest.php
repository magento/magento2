<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\OfflinePayments\Test\Unit\Block\Form;

class AbstractInstructionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\OfflinePayments\Block\Form\AbstractInstruction
     */
    protected $_model;

    protected function setUp(): void
    {
        $context = $this->createMock(\Magento\Framework\View\Element\Template\Context::class);
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
