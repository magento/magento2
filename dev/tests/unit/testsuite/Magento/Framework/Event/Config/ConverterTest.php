<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Event\Config;

class ConverterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Event\Config\Converter
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
        $this->_model = new \Magento\Framework\Event\Config\Converter();
    }

    public function testConvert()
    {
        $this->_source->loadXML(file_get_contents($this->_filePath . 'event_config.xml'));
        $convertedFile = include $this->_filePath . 'event_config.php';
        $this->assertEquals($convertedFile, $this->_model->convert($this->_source));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Attribute name is missed
     */
    public function testConvertThrowsExceptionWhenDomIsInvalid()
    {
        $this->_source->loadXML(file_get_contents($this->_filePath . 'event_invalid_config.xml'));
        $this->_model->convert($this->_source);
    }
}
