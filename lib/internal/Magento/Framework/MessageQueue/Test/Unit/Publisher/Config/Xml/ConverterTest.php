<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Test\Unit\Publisher\Config\Xml;

use Magento\Framework\MessageQueue\Publisher\Config\Xml\Converter;
use Magento\Framework\Stdlib\BooleanUtils;

class ConverterTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Converter
     */
    private $converter;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $defaultConfigProviderMock;

    /**
     * Initialize parameters
     */
    protected function setUp(): void
    {
        $this->defaultConfigProviderMock =
            $this->createMock(\Magento\Framework\MessageQueue\DefaultValueProvider::class);
        $this->converter = new Converter(new BooleanUtils(), $this->defaultConfigProviderMock);
    }

    public function testConvert()
    {
        $fixtureDir = __DIR__ . '/../../../_files/queue_publisher';
        $xmlFile = $fixtureDir . '/valid.xml';
        $dom = new \DOMDocument();
        $dom->load($xmlFile);
        $this->defaultConfigProviderMock->expects($this->any())->method('getExchange')->willReturn('magento');
        $result = $this->converter->convert($dom);

        $expectedData = include($fixtureDir . '/valid.php');
        foreach ($expectedData as $key => $value) {
            $this->assertEquals($value, $result[$key], 'Invalid data for ' . $key);
        }
    }

    /**
     */
    public function testConvertWithException()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Connection name is missing');

        $fixtureDir = __DIR__ . '/../../../_files/queue_publisher';
        $xmlFile = $fixtureDir . '/invalid.xml';
        $dom = new \DOMDocument();
        $dom->load($xmlFile);
        $this->converter->convert($dom);
    }
}
