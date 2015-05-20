<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Directory\Test\Unit\Model\Country\Postcode\Config;

class ConverterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Customer\Model\Address\Config\Converter
     */
    protected $model;

    public function setUp()
    {
        $this->model = new \Magento\Directory\Model\Country\Postcode\Config\Converter();
    }

    public function testConvert()
    {
        $inputData = new \DOMDocument();
        $inputData->load(__DIR__ . '/../../../../_files/zip_codes.xml');
        $expectedResult = require __DIR__ . '/../../../../_files/zip_codes.php';
        $this->assertEquals($expectedResult, $this->model->convert($inputData));
    }
}
