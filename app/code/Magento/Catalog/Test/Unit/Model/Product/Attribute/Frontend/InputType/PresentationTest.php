<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Product\Attribute\Frontend\InputType;

class PresentationTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Catalog\Model\Product\Attribute\Frontend\Inputtype\Presentation
     */
    private $presentation;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Eav\Attribute| \PHPUnit_Framework_MockObject_MockObject
     */
    private $attributeMock;

    protected function setUp()
    {
        $this->presentation = new \Magento\Catalog\Model\Product\Attribute\Frontend\Inputtype\Presentation();
        $this->attributeMock = $this->getMockBuilder(\Magento\Catalog\Model\ResourceModel\Eav\Attribute::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @param string $inputType
     * @param boolean $isWysiwygEnabled
     * @param string $expectedResult
     * @dataProvider getPresentationInputTypeDataProvider
     */
    public function testGetPresentationInputType(string $inputType, bool $isWysiwygEnabled, string $expectedResult)
    {
        $this->attributeMock->expects($this->once())->method('getFrontendInput')->willReturn($inputType);
        $this->attributeMock->expects($this->any())->method('getIsWysiwygEnabled')->willReturn($isWysiwygEnabled);
        $this->assertEquals($expectedResult, $this->presentation->getPresentationInputType($this->attributeMock));
    }

    /**
     * @return array
     */
    public function getPresentationInputTypeDataProvider()
    {
        return [
            'attribute_is_textarea_and_wysiwyg_enabled' => ['textarea', true, 'texteditor'],
            'attribute_is_input_and_wysiwyg_enabled' => ['input', true, 'input'],
            'attribute_is_textarea_and_wysiwyg_disabled' => ['textarea', false, 'textarea'],
        ];
    }

    /**
     * @param array $data
     * @param array $expectedResult
     * @dataProvider convertPresentationDataToInputTypeDataProvider
     */
    public function testConvertPresentationDataToInputType(array $data, array $expectedResult)
    {
        $this->assertEquals($expectedResult, $this->presentation->convertPresentationDataToInputType($data));
    }

    /**
     * @return array
     */
    public function convertPresentationDataToInputTypeDataProvider()
    {
        return [
            [['key' => 'value'], ['key' => 'value']],
            [
                ['frontend_input' => 'texteditor'],
                ['frontend_input' => 'textarea', 'is_wysiwyg_enabled' => 1]
            ],
            [
                ['frontend_input' => 'textarea'],
                ['frontend_input' => 'textarea', 'is_wysiwyg_enabled' => 0]
            ],
            [
                ['frontend_input' => 'input'],
                ['frontend_input' => 'input']
            ]
        ];
    }
}
