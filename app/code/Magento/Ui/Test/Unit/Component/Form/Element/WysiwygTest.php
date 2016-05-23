<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Test\Unit\Component\Form\Element;

use Magento\Ui\Component\Form\Element\Wysiwyg;
use Magento\Framework\Data\Form\Element\Editor;
use Magento\Framework\Data\Form;
use Magento\Framework\Data\FormFactory;
use Magento\Ui\Component\Wysiwyg\ConfigInterface;

/**
 * Class WysiwygTest
 */
class WysiwygTest extends AbstractElementTest
{
    /**
     * @var FormFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $formFactoryMock;

    /**
     * @var Form|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $formMock;

    /**
     * @var Editor|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $editorMock;

    /**
     * @var ConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $wysiwygConfig;

    protected function setUp()
    {
        parent::setUp();
        $this->formFactoryMock = $this->getMockBuilder(FormFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->formMock = $this->getMockBuilder(Form::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->wysiwygConfig = $this->getMockBuilder(ConfigInterface::class)
            ->getMockForAbstractClass();
        $this->editorMock = $this->getMockBuilder(Editor::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->formFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->formMock);
        $this->formMock->expects($this->once())
            ->method('addField')
            ->willReturn($this->editorMock);
        $this->editorMock->expects($this->once())
            ->method('getElementHtml');
    }

    protected function getModel()
    {
        return $this->objectManager->getObject(Wysiwyg::class, [
            'context' => $this->contextMock,
            'formFactory' => $this->formFactoryMock,
            'wysiwygConfig' => $this->wysiwygConfig,
            'data' => [
                'name' => 'testName',
            ],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function getModelName()
    {
        return Wysiwyg::class;
    }

    public function testGetComponentName()
    {
        $this->assertSame(Wysiwyg::NAME, $this->getModel()->getComponentName());
    }
}
