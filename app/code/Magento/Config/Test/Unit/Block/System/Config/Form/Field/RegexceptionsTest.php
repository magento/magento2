<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Tests for \Magento\Framework\Data\Form\Field\Regexceptions
 */
namespace Magento\Config\Test\Unit\Block\System\Config\Form\Field;

class RegexceptionsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var array
     */
    protected $cellParameters;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $labelFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $labelMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $elementFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $elementMock;

    /**
     * @var \Magento\Config\Block\System\Config\Form\Field\Regexceptions
     */
    protected $object;

    protected function setUp()
    {
        $this->cellParameters = [
            'label' => 'testLabel',
            'size'  => 'testSize',
            'style' => 'testStyle',
            'class' => 'testClass',
        ];

        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->labelFactoryMock = $this->getMock('Magento\Framework\View\Design\Theme\LabelFactory', [], [], '', false);
        $this->labelMock        = $this->getMock('Magento\Framework\View\Design\Theme\Label', [], [], '', false);

        $this->elementFactoryMock = $this->getMock('Magento\Framework\Data\Form\Element\Factory', [], [], '', false);
        $this->elementMock        = $this->getMock(
            'Magento\Framework\Data\Form\Element\AbstractElement',
            ['setForm', 'setName', 'setHtmlId', 'setValues', 'getName', 'getHtmlId', 'getValues', 'getElementHtml'],
            [],
            '',
            false
        );
        $data = [
            'elementFactory' => $this->elementFactoryMock,
            'labelFactory'   => $this->labelFactoryMock,
            'data'           => [
                'element' => $this->elementMock
            ],
        ];
        $this->object = $helper->getObject('Magento\Config\Block\System\Config\Form\Field\Regexceptions', $data);
    }

    public function testRenderCellTemplateValueColumn()
    {
        $columnName     = 'value';
        $expectedResult = 'testValueElementHtml';

        $this->elementFactoryMock->expects($this->once())->method('create')->willReturn($this->elementMock);
        $this->elementMock->expects($this->once())->method('setForm')->willReturn($this->elementMock);
        $this->elementMock->expects($this->once())->method('setName')->willReturn($this->elementMock);
        $this->elementMock->expects($this->once())->method('setHtmlId')->willReturn($this->elementMock);
        $this->elementMock->expects($this->once())->method('setValues')->willReturn($this->elementMock);
        $this->elementMock->expects($this->once())->method('getElementHtml')->willReturn($expectedResult);

        $this->labelFactoryMock->expects($this->once())->method('create')->willReturn($this->labelMock);
        $this->labelMock->expects($this->once())->method('getLabelsCollection')->willReturn([]);

        $this->object->addColumn(
            $columnName,
            $this->cellParameters
        );

        $this->assertEquals(
            $expectedResult,
            $this->object->renderCellTemplate($columnName)
        );
    }

    public function testRenderCellTemplateOtherColumn()
    {
        $columnName     = 'testCellName';
        $expectedResult = '<input type="text" id="<%- _id %>_testCellName" name="[<%- _id %>][testCellName]"' .
            ' value="<%- testCellName %>" size="testSize" class="testClass" style="testStyle"/>';

        $this->object->addColumn(
            $columnName,
            $this->cellParameters
        );

        $this->assertEquals(
            $expectedResult,
            $this->object->renderCellTemplate($columnName)
        );
    }

    public function testRenderCellTemplateWrongColumnName()
    {
        $columnName     = 'testCellName';

        $this->object->addColumn(
            $columnName . 'Wrong',
            $this->cellParameters
        );

        $this->setExpectedException('\Exception', 'Wrong column name specified.');

        $this->object->renderCellTemplate($columnName);
    }
}
