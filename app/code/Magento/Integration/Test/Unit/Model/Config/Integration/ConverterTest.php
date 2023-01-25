<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Integration\Test\Unit\Model\Config\Integration;

use Magento\Integration\Model\Config\Integration\Converter;
use PHPUnit\Framework\TestCase;

/**
 * Test for conversion of integration API XML config into array representation.
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
        $inputData->load(__DIR__ . '/_files/api.xml');
        $expectedResult = require __DIR__ . '/_files/api.php';
        $this->assertEquals($expectedResult, $this->model->convert($inputData));
    }
}
