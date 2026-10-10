<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Ui\Test\Unit\Component\Form\Element;

use Magento\Framework\Data\Form;
use Magento\Framework\Data\Form\Element\Editor;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\DataObject;
use Magento\Ui\Component\Form\Element\AbstractElement;
use Magento\Ui\Component\Form\Element\Wysiwyg;
use Magento\Ui\Component\Wysiwyg\ConfigInterface;
use PHPUnit\Framework\MockObject\MockObject;

class WysiwygTest extends AbstractElementTestCase
{
    /**
     * @var FormFactory|MockObject
     */
    protected $formFactoryMock;

    /**
     * @var Form|MockObject
     */
    protected $formMock;

    /**
     * @var Editor|MockObject
     */
    protected $editorMock;

    /**
     * @var ConfigInterface|MockObject
     */
    protected $wysiwygConfig;

    protected function setUp(): void
    {
        parent::setUp();
        $this->formFactoryMock = $this->createPartialMock(FormFactory::class, ['create']);
        $this->formMock = $this->createMock(Form::class);
        $this->wysiwygConfig = $this->createMock(ConfigInterface::class);
        $dataObject = new DataObject();
        $this->wysiwygConfig
            ->expects($this->once())
            ->method('getConfig')
            ->willReturn($dataObject);

        $this->editorMock = $this->createMock(Editor::class);

        $this->formFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->formMock);
        $this->formMock->expects($this->once())
            ->method('addField')
            ->willReturn($this->editorMock);
        $this->editorMock->expects($this->once())
            ->method('getElementHtml');
    }

    /**
     * @return AbstractElement|object
     */
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
     * @inheritdoc
     */
    protected function getModelName()
    {
        return Wysiwyg::class;
    }

    /**
     * @inheritdoc
     */
    public function testGetComponentName()
    {
        $this->assertSame(Wysiwyg::NAME, $this->getModel()->getComponentName());
    }
}
