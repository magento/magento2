<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\WebapiAsync\Test\Unit\Model\ServiceConfig;

class ConverterTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\WebapiAsync\Model\ServiceConfig\Converter
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = new \Magento\WebapiAsync\Model\ServiceConfig\Converter();
    }

    /**
     * @covers \Magento\WebapiAsync\Model\ServiceConfig\Converter::convert()
     */
    public function testConvert()
    {
        $inputData = new \DOMDocument();
        $inputData->load(__DIR__ . '/_files/Converter/webapi_async.xml');
        $expectedResult = require __DIR__ . '/_files/Converter/webapi_async.php';
        $this->assertEquals($expectedResult, $this->_model->convert($inputData));
    }
}
