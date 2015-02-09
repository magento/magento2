<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Mview\Config;

class ConverterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Mview\Config\Converter|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = new \Magento\Framework\Mview\Config\Converter();
    }

    public function testConvert()
    {
        $data = include __DIR__ . '/../_files/mview_config.php';
        $dom = new \DOMDocument();
        $dom->loadXML($data['inputXML']);

        $this->assertEquals($data['expected'], $this->_model->convert($dom));
    }

    /**
     * @param string $xmlData
     * @dataProvider wrongXmlDataProvider
     * @expectedException \Exception
     */
    public function testMapThrowsExceptionWhenXmlHasWrongFormat($xmlData)
    {
        $dom = new \DOMDocument();
        $dom->loadXML($xmlData);
        $this->_model->convert($dom);
    }

    /**
     * @return array
     */
    public function wrongXmlDataProvider()
    {
        return [['<?xml version="1.0"?><config>']];
    }
}
