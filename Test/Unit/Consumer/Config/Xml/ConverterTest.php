<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Test\Unit\Consumer\Config\Xml;

use Magento\Framework\Communication\Config\ConfigParser;
use Magento\Framework\MessageQueue\Consumer\Config\Xml\Converter;

class ConverterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Converter
     */
    private $converter;

    /**
     * @var ConfigParser|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $configParserMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $defaultConfigProviderMock;

    /**
     * Initialize parameters
     */
    protected function setUp()
    {
        $this->defaultConfigProviderMock = $this->getMock(
            \Magento\Framework\MessageQueue\DefaultValueProvider::class,
            [],
            [],
            '',
            false,
            false
        );
        $this->configParserMock = $this->getMock(ConfigParser::class, [], [], '', false, false);
        $this->converter = new Converter($this->configParserMock, $this->defaultConfigProviderMock);

    }

    public function testConvert()
    {
        $this->defaultConfigProviderMock->expects($this->any())->method('getConnection')->willReturn('amqp');
        $this->configParserMock->expects($this->any())->method('parseServiceMethod')->willReturnArgument(0);
        $fixtureDir = __DIR__ . '/../../../_files/queue_consumer';
        $xmlFile = $fixtureDir . '/valid.xml';
        $dom = new \DOMDocument();
        $dom->load($xmlFile);
        $result = $this->converter->convert($dom);

        $expectedData = include($fixtureDir . '/valid.php');
        $this->assertEquals($expectedData, $result);
    }
}
