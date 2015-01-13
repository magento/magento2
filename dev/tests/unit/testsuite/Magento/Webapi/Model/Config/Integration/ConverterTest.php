<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Webapi\Model\Config\Integration;


/**
 * Test for conversion of integration API XML config into array representation.
 */
class ConverterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Converter
     */
    protected $_model;

    public function setUp()
    {
        $this->_model = new Converter();
    }

    public function testConvert()
    {
        $inputData = new \DOMDocument();
        $inputData->load(__DIR__ . '/_files/api.xml');
        $expectedResult = require __DIR__ . '/_files/api.php';
        $this->assertEquals($expectedResult, $this->_model->convert($inputData));
    }
}
