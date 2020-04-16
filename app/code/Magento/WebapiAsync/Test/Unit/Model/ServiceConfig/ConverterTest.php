<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\WebapiAsync\Test\Unit\Model\ServiceConfig;

class ConverterTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\WebapiAsync\Model\ServiceConfig\Converter
     */
    private $model;

    protected function setUp(): void
    {
        $this->model = new \Magento\WebapiAsync\Model\ServiceConfig\Converter();
    }

    /**
     * @covers \Magento\WebapiAsync\Model\ServiceConfig\Converter::convert()
     */
    public function testConvert()
    {
        $inputData = new \DOMDocument();
        $inputData->load(__DIR__ . '/_files/Converter/webapi_async.xml');
        $expectedResult = require __DIR__ . '/_files/Converter/webapi_async.php';
        $this->assertEquals($expectedResult, $this->model->convert($inputData));
    }
}
