<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Email\Test\Unit\Block\Adminhtml\Template\Edit;

use Magento\Email\Block\Adminhtml\Template\Edit\Form;
use Magento\Email\Model\Template;
use Magento\Variable\Model\Source\Variables;
use Magento\Variable\Model\Variable;
use Magento\Variable\Model\VariableFactory;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Magento\Email\Block\Adminhtml\Template\Edit\Form
 */
class FormTest extends TestCase
{
    use MockCreationTrait;

    /** @var Form */
    protected $form;

    /** @var Variables|MockObject */
    protected $variablesMock;

    /** @var VariableFactory|MockObject */
    protected $variableFactoryMock;

    /** @var Variable|MockObject */
    protected $variableMock;

    /** @var Template|MockObject */
    protected $templateMock;

    protected function setUp(): void
    {
        $this->variablesMock = $this->getMockBuilder(Variables::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['toOptionArray'])
            ->getMock();
        $this->variableFactoryMock = $this->getMockBuilder(VariableFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();
        $this->variableMock = $this->getMockBuilder(Variable::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getVariablesOptionArray'])
            ->getMock();
        $this->templateMock = $this->getMockBuilder(Template::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getId', 'getVariablesOptionArray'])
            ->getMock();

        $this->form = $this->getMockBuilder(Form::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $this->addPropertyValue($this->form, [
            '_variableFactory' => $this->variableFactoryMock,
            '_variables' => $this->variablesMock,
        ], Form::class);
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
            ['var1', 'var2', 'var3', 'custom var 1', 'custom var 2', ['template var 1', 'template var 2']],
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
