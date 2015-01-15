<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ImportExport\Model\Export\Config;

class ConverterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\ImportExport\Model\Export\Config\Converter
     */
    protected $_model;

    /**
     * @var string
     */
    protected $_filePath;

    public function setUp()
    {
        $this->_filePath = realpath(__DIR__) . '/_files/';
        $this->_model = new \Magento\ImportExport\Model\Export\Config\Converter();
    }

    public function testConvert()
    {
        $testDom = $this->_filePath . 'export.xml';
        $dom = new \DOMDocument();
        $dom->load($testDom);
        $expectedArray = include $this->_filePath . 'export.php';
        $this->assertEquals($expectedArray, $this->_model->convert($dom));
    }
}
