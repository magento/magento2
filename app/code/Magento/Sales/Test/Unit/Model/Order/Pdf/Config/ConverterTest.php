<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model\Order\Pdf\Config;

use Magento\Sales\Model\Order\Pdf\Config\Converter;
use PHPUnit\Framework\TestCase;

class ConverterTest extends TestCase
{
    /**
     * @var Converter
     */
    protected $_model;

    protected function setUp(): void
    {
        $this->_model = new Converter();
    }

    public function testConvert()
    {
        $inputData = new \DOMDocument();
        $inputData->load(__DIR__ . '/_files/pdf_merged.xml');
        $expectedResult = require __DIR__ . '/_files/pdf_merged.php';
        $this->assertEquals($expectedResult, $this->_model->convert($inputData));
    }
}
