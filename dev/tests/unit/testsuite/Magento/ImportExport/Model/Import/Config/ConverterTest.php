<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\ImportExport\Model\Import\Config;

class ConverterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\ImportExport\Model\Import\Config\Converter
     */
    protected $_model;

    /**
     * @var string
     */
    protected $_filePath;

    public function setUp()
    {
        $this->_filePath = realpath(__DIR__) . '/_files/';
        $this->_model = new \Magento\ImportExport\Model\Import\Config\Converter();
    }

    public function testConvert()
    {
        $testDom = $this->_filePath . 'import.xml';
        $dom = new \DOMDocument();
        $dom->load($testDom);
        $expectedArray = include $this->_filePath . 'import.php';
        $this->assertEquals($expectedArray, $this->_model->convert($dom));
    }
}
