<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Fixtures\AttributeSet;

use Magento\Setup\Fixtures\AttributeSet\SwatchesGenerator;
use Magento\Setup\Fixtures\ImagesGenerator\ImagesGenerator;
use Magento\Setup\Fixtures\ImagesGenerator\ImagesGeneratorFactory;
use Magento\Swatches\Helper\Media;
use Magento\Swatches\Model\Swatch;

class SwatchesGeneratorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var SwatchesGenerator
     */
    private $swatchesGeneratorMock;

    /**
     * @var array
     */
    private $imagePathFixture = [
        'option_1' => '/<-o->',
        'option_2' => '/>o<',
        'option_3' => '/|o|'
    ];

    public function setUp()
    {
        // Mock Swatch Media Helper
        $swatchHelperMock = $this->getMockBuilder(Media::class)
            ->disableOriginalConstructor()
            ->getMock();

        $swatchHelperMock
            ->expects($this->any())
            ->method('moveImageFromTmp')
            ->willReturnOnConsecutiveCalls(...array_values($this->imagePathFixture));

        // Mock image generator
        $imageGeneratorMock = $this->getMockBuilder(ImagesGenerator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $imageGeneratorMock
            ->expects($this->any())
            ->method('generate');

        // Mock image generator factory
        $imageGeneratorFactoryMock = $this->getMockBuilder(ImagesGeneratorFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $imageGeneratorFactoryMock
            ->expects($this->once())
            ->method('create')
            ->willReturn($imageGeneratorMock);

        $this->swatchesGeneratorMock = new SwatchesGenerator(
            $swatchHelperMock,
            $imageGeneratorFactoryMock
        );
    }

    public function testGenerateSwatchData()
    {
        $attributeColorType['swatch_input_type'] = Swatch::SWATCH_INPUT_TYPE_VISUAL;
        $attributeColorType['swatchvisual']['value'] = array_reduce(
            range(1, 3),
            function ($values, $index) {
                $values['option_' . $index] = '#' . str_repeat(dechex(255 * $index / 3), 3);
                return $values;
            },
            []
        );

        $attributeColorType['optionvisual']['value'] = array_reduce(
            range(1, 3),
            function ($values, $index) {
                $values['option_' . $index] = ['option ' . $index];
                return $values;
            },
            []
        );

        $attributeImageType = $attributeColorType;
        $attributeImageType['swatchvisual']['value'] = array_map(
            function ($item) {
                return ltrim($item, '/');
            },
            $this->imagePathFixture
        );

        $this->assertEquals(
            $attributeColorType,
            $this->swatchesGeneratorMock->generateSwatchData(3, 'test', 'color')
        );

        $this->assertEquals(
            $attributeImageType,
            $this->swatchesGeneratorMock->generateSwatchData(3, 'test', 'image')
        );
    }
}
