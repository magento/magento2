<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Model\Address\Config;

class ConverterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Customer\Model\Address\Config\Converter
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = new \Magento\Customer\Model\Address\Config\Converter();
    }

    public function testConvert()
    {
        $inputData = new \DOMDocument();
        $inputData->load(__DIR__ . '/_files/formats_merged.xml');
        $expectedResult = require __DIR__ . '/_files/formats_merged.php';
        $this->assertEquals($expectedResult, $this->_model->convert($inputData));
    }
}
