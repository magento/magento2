<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Integration\Test\Unit\Model\Config;

use \Magento\Integration\Model\Config\Converter;

/**
 * Test for conversion of integration XML config into array representation.
 */
class ConverterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Converter
     */
    protected $model;

    protected function setUp()
    {
        $this->model = new Converter();
    }

    public function testConvert()
    {
        $inputData = new \DOMDocument();
        $inputData->load(__DIR__ . '/_files/config.xml');
        $expectedResult = require __DIR__ . '/_files/integration.php';
        $this->assertEquals($expectedResult, $this->model->convert($inputData));
    }
}
