<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Fixtures\AttributeSet;

use Magento\Catalog\Model\Product\Media\Config;
use Magento\Framework\Filesystem;
use Magento\Setup\Fixtures\AttributeSet\SwatchesGenerator;
use Magento\Swatches\Helper\Media;
use Magento\Swatches\Model\Swatch;

class SwatchesGeneratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SwatchesGenerator
     */
    private $swatchesGeneratorMock;

    public function setUp()
    {
        // Mock Filesystem
        $filesystemMock = $this->getMockBuilder(Filesystem::class)
            ->disableOriginalConstructor()
            ->getMock();

        // Mock Media Config
        $mediaConfigMock = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();

        // Mock Swatch Media Helper
        $swatchHelperMock = $this->getMockBuilder(Media::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->swatchesGeneratorMock = new SwatchesGenerator(
            $filesystemMock,
            $mediaConfigMock,
            $swatchHelperMock
        );
    }

    public function testGenerateSwatchData()
    {

        $attribute['swatch_input_type'] = Swatch::SWATCH_INPUT_TYPE_VISUAL;
        $attribute['swatchvisual']['value'] = array_reduce(
            range(1, 3),
            function ($values, $index) {
                $values['option_' . $index] = '#' . str_repeat(dechex(255 * $index / 3), 3);
                return $values;
            },
            []
        );

        $attribute['optionvisual']['value'] = array_reduce(
            range(1, 3),
            function ($values, $index) {
                $values['option_' . $index] = ['option ' . $index];
                return $values;
            },
            []
        );

        $this->assertEquals(
            $attribute,
            $this->swatchesGeneratorMock->generateSwatchData(3, 'test', 'color')
        );
    }
}
