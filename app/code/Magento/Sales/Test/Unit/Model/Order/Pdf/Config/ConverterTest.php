<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Model\Order\Pdf\Config;

class ConverterTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Sales\Model\Order\Pdf\Config\Converter
     */
    protected $_model;

    protected function setUp(): void
    {
        $this->_model = new \Magento\Sales\Model\Order\Pdf\Config\Converter();
    }

    public function testConvert()
    {
        $inputData = new \DOMDocument();
        $inputData->load(__DIR__ . '/_files/pdf_merged.xml');
        $expectedResult = require __DIR__ . '/_files/pdf_merged.php';
        $this->assertEquals($expectedResult, $this->_model->convert($inputData));
    }
}
