<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Webapi\Model\Config;

class ConverterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Webapi\Model\Config\Converter
     */
    protected $_model;

    public function setUp()
    {
        $this->_model = new \Magento\Webapi\Model\Config\Converter();
    }

    public function testConvert()
    {
        $inputData = new \DOMDocument();
        $inputData->load(__DIR__ . '/_files/webapi.xml');
        $expectedResult = require __DIR__ . '/_files/webapi.php';
        $this->assertEquals($expectedResult, $this->_model->convert($inputData));
    }
}
