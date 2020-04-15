<?php
/**
 * \Magento\Framework\DataObject\Copy\Config\Converter
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DataObject\Test\Unit\Copy\Config;

class ConverterTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Framework\DataObject\Copy\Config\Converter
     */
    protected $_model;

    protected function setUp(): void
    {
        $this->_model = new \Magento\Framework\DataObject\Copy\Config\Converter();
    }

    public function testConvert()
    {
        $dom = new \DOMDocument();
        $xmlFile = __DIR__ . '/_files/fieldset.xml';
        $dom->loadXML(file_get_contents($xmlFile));

        $convertedFile = __DIR__ . '/_files/fieldset_config.php';
        $expectedResult = include $convertedFile;
        $this->assertEquals($expectedResult, $this->_model->convert($dom));
    }
}
