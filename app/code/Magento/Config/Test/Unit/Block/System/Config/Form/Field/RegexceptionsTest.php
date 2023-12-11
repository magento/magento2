<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

/**
 * Tests for \Magento\Framework\Data\Form\Field\Regexceptions
 */
namespace Magento\Config\Test\Unit\Block\System\Config\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\Regexceptions;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\Factory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Design\Theme\Label;
use Magento\Framework\View\Design\Theme\LabelFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RegexceptionsTest extends TestCase
{
    /**
     * @var array
     */
    protected $cellParameters;

    /**
     * @var MockObject
     */
    protected $labelFactoryMock;

    /**
     * @var MockObject
     */
    protected $labelMock;

    /**
     * @var MockObject
     */
    protected $elementFactoryMock;

    /**
     * @var MockObject
     */
    protected $elementMock;

    /**
     * @var Regexceptions
     */
    protected $object;

    protected function setUp(): void
    {
        $this->cellParameters = [
            'size'  => 'testSize',
            'style' => 'testStyle',
            'class' => 'testClass',
        ];

        $objectManager = new ObjectManager($this);

        $this->labelFactoryMock = $this->getMockBuilder(LabelFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->labelMock = $this->getMockBuilder(Label::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->elementFactoryMock = $this->getMockBuilder(Factory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->elementMock = $this->getMockBuilder(AbstractElement::class)
            ->disableOriginalConstructor()
            ->setMethods(
                ['setForm', 'setName', 'setHtmlId', 'setValues', 'getName', 'getHtmlId', 'getValues', 'getElementHtml']
            )
            ->getMock();

        $data = [
            'elementFactory' => $this->elementFactoryMock,
            'labelFactory'   => $this->labelFactoryMock,
            'data'           => [
                'element' => $this->elementMock
            ],
        ];
        $this->object = $objectManager->getObject(
            Regexceptions::class,
            $data
        );
    }

    public function testRenderCellTemplateValueColumn()
    {
        $columnName     = 'value';
        $expectedResult = 'testValueElementHtml';

        $this->elementFactoryMock->expects($this->once())->method('create')->willReturn($this->elementMock);
        $this->elementMock->expects($this->once())->method('setForm')->willReturnSelf();
        $this->elementMock->expects($this->once())->method('setName')->willReturnSelf();
        $this->elementMock->expects($this->once())->method('setHtmlId')->willReturnSelf();
        $this->elementMock->expects($this->once())->method('setValues')->willReturnSelf();
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

        $this->object->addColumn(
            $columnName,
            $this->cellParameters
        );

        $actual = $this->object->renderCellTemplate($columnName);
        foreach ($this->cellParameters as $parameter) {
            $this->assertStringContainsString(
                $parameter,
                $actual,
                'Parameter \'' . $parameter . '\' missing in render output.'
            );
        }
    }

    public function testRenderCellTemplateWrongColumnName()
    {
        $columnName      = 'testCellName';
        $wrongColumnName = 'wrongTestCellName';

        $this->object->addColumn($wrongColumnName, $this->cellParameters);

        $this->expectException('\Exception');
        $this->expectExceptionMessage('Wrong column name specified.');

        $this->object->renderCellTemplate($columnName);
    }
}
