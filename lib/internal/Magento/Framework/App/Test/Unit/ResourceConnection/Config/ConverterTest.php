<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Test\Unit\ResourceConnection\Config;

class ConverterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\App\ResourceConnection\Config\Converter
     */
    protected $_model;

    /**
     * @var string
     */
    protected $_filePath;

    /**
     * @var \DOMDocument
     */
    protected $_source;

    protected function setUp()
    {
        $this->_filePath = __DIR__ . '/_files/';
        $this->_source = new \DOMDocument();
        $this->_model = new \Magento\Framework\App\ResourceConnection\Config\Converter();
    }

    public function testConvert()
    {
        $this->_source->loadXML(file_get_contents($this->_filePath . 'resources.xml'));
        $convertedFile = include $this->_filePath . 'resources.php';
        $this->assertEquals($convertedFile, $this->_model->convert($this->_source));
    }
}
