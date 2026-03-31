<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Integration\Test\Unit\Model\Config;

use Magento\Integration\Model\Config\Converter;
use PHPUnit\Framework\TestCase;

/**
 * Test for conversion of integration XML config into array representation.
 */
class ConverterTest extends TestCase
{
    /**
     * @var Converter
     */
    protected $model;

    protected function setUp(): void
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
