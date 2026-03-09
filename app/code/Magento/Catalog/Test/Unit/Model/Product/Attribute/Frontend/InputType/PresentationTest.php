<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Product\Attribute\Frontend\InputType;

use PHPUnit\Framework\Attributes\DataProvider;
use Magento\Catalog\Model\Product\Attribute\Frontend\Inputtype\Presentation;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PresentationTest extends TestCase
{
    /**
     * @var Presentation
     */
    private $presentation;

    /**
     * @var Attribute|MockObject
     */
    private $attributeMock;

    protected function setUp(): void
    {
        $this->presentation = new Presentation();
        $this->attributeMock = $this->createMock(Attribute::class);
    }

    /**
     * @param string $inputType
     * @param boolean $isWysiwygEnabled
     * @param string $expectedResult
     */
    #[DataProvider('getPresentationInputTypeDataProvider')]
    public function testGetPresentationInputType(string $inputType, bool $isWysiwygEnabled, string $expectedResult)
    {
        $this->attributeMock->expects($this->once())->method('getFrontendInput')->willReturn($inputType);
        $this->attributeMock->method('getIsWysiwygEnabled')->willReturn($isWysiwygEnabled);
        $this->assertEquals($expectedResult, $this->presentation->getPresentationInputType($this->attributeMock));
    }

    /**
     * @return array
     */
    public static function getPresentationInputTypeDataProvider()
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
     */
    #[DataProvider('convertPresentationDataToInputTypeDataProvider')]
    public function testConvertPresentationDataToInputType(array $data, array $expectedResult)
    {
        $this->assertEquals($expectedResult, $this->presentation->convertPresentationDataToInputType($data));
    }

    /**
     * @return array
     */
    public static function convertPresentationDataToInputTypeDataProvider()
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
