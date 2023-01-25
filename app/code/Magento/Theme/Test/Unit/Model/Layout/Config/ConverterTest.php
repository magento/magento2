<?php declare(strict_types=1);
/**
 * \Magento\Theme\Model\Layout\Config\Converter
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Test\Unit\Model\Layout\Config;

use Magento\Theme\Model\Layout\Config\Converter;
use PHPUnit\Framework\TestCase;

class ConverterTest extends TestCase
{
    /**
     * @var Converter
     */
    protected $_model;

    /** @var  array */
    protected $_targetArray;

    protected function setUp(): void
    {
        $this->_model = new Converter();
    }

    public function testConvert()
    {
        $dom = new \DOMDocument();
        $xmlFile = __DIR__ . '/_files/page_layouts.xml';
        $dom->loadXML(file_get_contents($xmlFile));

        $expectedResult = [
            'empty' => [
                'label' => 'Empty',
                'code' => 'empty',
            ],
            '1column' => [
                'label' => '1 column',
                'code' => '1column',
            ],
        ];
        $this->assertEquals($expectedResult, $this->_model->convert($dom));
    }
}
