<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Test\Unit\Publisher\Config\Xml;

use Magento\Framework\MessageQueue\DefaultValueProvider;
use Magento\Framework\MessageQueue\Publisher\Config\Xml\Converter;
use Magento\Framework\Stdlib\BooleanUtils;
use PHPUnit\Framework\MockObject\MockObject;

class ConverterTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var string
     */
    private $fixtureDir;

    /**
     * @var Converter
     */
    private $converter;

    /**
     * @var MockObject|DefaultValueProvider
     */
    private $defaultConfigProviderMock;

    /**
     * Initialize parameters
     */
    protected function setUp()
    {
        $this->fixtureDir = __DIR__ . '/../../../_files/queue_publisher';
        $this->defaultConfigProviderMock = $this->createMock(DefaultValueProvider::class);
        $this->converter = new Converter(new BooleanUtils(), $this->defaultConfigProviderMock);
    }

    public function testConvert()
    {
        $xmlFile = $this->fixtureDir . '/valid.xml';
        $dom = new \DOMDocument();
        $dom->load($xmlFile);
        $this->defaultConfigProviderMock->expects($this->any())
            ->method('getExchange')
            ->willReturn('magento');
        $result = $this->converter->convert($dom);

        $expectedData = include($this->fixtureDir . '/valid.php');
        foreach ($expectedData as $key => $value) {
            $this->assertEquals($value, $result[$key], 'Invalid data for ' . $key);
        }
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Connection name is missing
     */
    public function testConvertWithException()
    {
        $xmlFile = $this->fixtureDir . '/invalid.xml';
        $dom = new \DOMDocument();
        $dom->load($xmlFile);
        $this->converter->convert($dom);
    }
}
