<?php
/**
 * Copyright 2024 Adobe
 * All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Backend\Test\Unit\Block\Widget\Grid\Column\Renderer\Radio;

use Magento\Backend\Block\Context;
use Magento\Backend\Block\Widget\Grid\Column;
use Magento\Backend\Block\Widget\Grid\Column\Renderer\Options\Converter;
use Magento\Backend\Block\Widget\Grid\Column\Renderer\Radio\Extended;
use Magento\Framework\DataObject;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ExtendedTest extends TestCase
{
    /**
     * @var Extended
     */
    protected $_object;

    /**
     * @var MockObject
     */
    protected $_converter;

    /**
     * @var MockObject
     */
    protected $_column;

    protected function setUp(): void
    {
        $context = $this->createMock(Context::class);
        $this->_converter = $this->createPartialMock(
            Converter::class,
            ['toFlatArray']
        );
        $this->_column = $this->getMockBuilder(Column::class)
            ->addMethods(['getValues', 'getIndex', 'getHtmlName'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->_object = new Extended($context, $this->_converter);
        $this->_object->setColumn($this->_column);
    }

    /**
     * @param array $rowData
     * @param string $expectedResult
     * @dataProvider renderDataProvider
     */
    public function testRender(array $rowData, $expectedResult)
    {
        $selectedFlatArray = [1 => 'One'];
        $this->_column->expects($this->once())->method('getValues')->willReturn($selectedFlatArray);
        $this->_column->expects($this->once())->method('getIndex')->willReturn('label');
        $this->_column->expects($this->once())->method('getHtmlName')->willReturn('test[]');
        $this->_converter->expects($this->never())->method('toFlatArray');
        $this->assertEquals($expectedResult, $this->_object->render(new DataObject($rowData)));
    }

    /**
     * @return array
     */
    public static function renderDataProvider()
    {
        return [
            'checked' => [
                ['id' => 1, 'label' => 'One'],
                '<input type="radio" name="test[]" value="1" class="radio" checked="checked"/>',
            ],
            'not checked' => [
                ['id' => 2, 'label' => 'Two'],
                '<input type="radio" name="test[]" value="2" class="radio"/>',
            ]
        ];
    }
}
