<?php
/**
 * \Magento\Theme\Model\Layout\Config\Converter
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Model\Layout\Config;

class ConverterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Theme\Model\Layout\Config\Converter
     */
    protected $_model;

    /** @var  array */
    protected $_targetArray;

    public function setUp()
    {
        $this->_model = new \Magento\Theme\Model\Layout\Config\Converter();
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
        $this->assertEquals($expectedResult, $this->_model->convert($dom), '', 0, 20);
    }
}
