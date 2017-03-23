<?php
/**
 * \Magento\Framework\DataObject\Copy\Config\Converter
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DataObject\Test\Unit\Copy\Config;

class ConverterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\DataObject\Copy\Config\Converter
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = new \Magento\Framework\DataObject\Copy\Config\Converter();
    }

    public function testConvert()
    {
        $dom = new \DOMDocument();
        $xmlFile = __DIR__ . '/_files/fieldset.xml';
        $dom->loadXML(file_get_contents($xmlFile));

        $convertedFile = __DIR__ . '/_files/fieldset_config.php';
        $expectedResult = include $convertedFile;
        $this->assertEquals($expectedResult, $this->_model->convert($dom));
    }
}
