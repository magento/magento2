<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Email\Test\Unit\Block\Adminhtml\Template\Edit;

/**
 * @covers \Magento\Email\Block\Adminhtml\Template\Edit\Form
 */
class FormTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\Email\Block\Adminhtml\Template\Edit\Form */
    protected $form;

    /** @var \Magento\Variable\Model\Source\Variables|\PHPUnit_Framework_MockObject_MockObject */
    protected $variablesMock;

    /** @var \Magento\Variable\Model\VariableFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $variableFactoryMock;

    /** @var \Magento\Variable\Model\Variable|\PHPUnit_Framework_MockObject_MockObject */
    protected $variableMock;

    /** @var \Magento\Email\Model\Template|\PHPUnit_Framework_MockObject_MockObject */
    protected $templateMock;

    protected function setUp()
    {
        $this->variablesMock = $this->getMockBuilder(\Magento\Variable\Model\Source\Variables::class)
            ->disableOriginalConstructor()
            ->setMethods(['toOptionArray'])
            ->getMock();
        $this->variableFactoryMock = $this->getMockBuilder(\Magento\Variable\Model\VariableFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->variableMock = $this->getMockBuilder(\Magento\Variable\Model\Variable::class)
            ->disableOriginalConstructor()
            ->setMethods(['getVariablesOptionArray'])
            ->getMock();
        $this->templateMock = $this->getMockBuilder(\Magento\Email\Model\Template::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId', 'getVariablesOptionArray'])
            ->getMock();
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->form = $objectManager->getObject(
            \Magento\Email\Block\Adminhtml\Template\Edit\Form::class,
            [
                'variableFactory' => $this->variableFactoryMock,
                'variables' => $this->variablesMock
            ]
        );
    }

    /**
     * @covers \Magento\Email\Block\Adminhtml\Template\Edit\Form::getVariables
     */
    public function testGetVariables()
    {
        $this->variablesMock->expects($this->once())
            ->method('toOptionArray')
            ->willReturn(['var1', 'var2', 'var3']);
        $this->variableFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->variableMock);
        $this->variableMock->expects($this->once())
            ->method('getVariablesOptionArray')
            ->willReturn(['custom var 1', 'custom var 2']);
        $this->form->setEmailTemplate($this->templateMock);
        $this->templateMock->expects($this->once())
            ->method('getId')
            ->willReturn(1);
        $this->templateMock->expects($this->once())
            ->method('getVariablesOptionArray')
            ->willReturn(['template var 1', 'template var 2']);
        $this->assertEquals(
            ['var1', 'var2', 'var3', 'custom var 1', 'custom var 2', 'template var 1', 'template var 2'],
            $this->form->getVariables()
        );
    }

    /**
     * @covers \Magento\Email\Block\Adminhtml\Template\Edit\Form::getEmailTemplate
     */
    public function testGetEmailTemplate()
    {
        $this->form->setEmailTemplate($this->templateMock);
        $this->assertEquals($this->templateMock, $this->form->getEmailTemplate());
    }
}
