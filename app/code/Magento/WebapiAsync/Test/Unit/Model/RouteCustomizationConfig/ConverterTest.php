<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\WebapiAsync\Test\Unit\Model\RouteCustomizationConfig;

class ConverterTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\WebapiAsync\Model\ServiceConfig\Converter
     */
    private $model;

    protected function setUp()
    {
        $this->model = new \Magento\WebapiAsync\Model\RouteCustomizationConfig\Converter();
    }

    /**
     * @covers \Magento\WebapiAsync\Model\RouteCustomizationConfig\Converter::convert()
     */
    public function testConvert()
    {
        $inputData = new \DOMDocument();
        $inputData->load(__DIR__ . '/_files/Converter/route_customization.xml');
        $expectedResult = require __DIR__ . '/_files/Converter/route_customization.php';
        $this->assertEquals($expectedResult, $this->model->convert($inputData));
    }
}
