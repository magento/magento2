<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\MessageQueue\Test\Unit\Topology\Config\Xml;

use Magento\Framework\Data\Argument\InterpreterInterface;
use Magento\Framework\MessageQueue\DefaultValueProvider;
use Magento\Framework\MessageQueue\Topology\Config\Xml\Converter;
use Magento\Framework\Stdlib\BooleanUtils;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ConverterTest extends TestCase
{
    /**
     * @var Converter
     */
    private $converter;

    /**
     * @var MockObject
     */
    private $interpreter;

    /**
     * @var MockObject
     */
    protected $defaultConfigProviderMock;

    /**
     * Initialize parameters
     */
    protected function setUp(): void
    {
        $this->defaultConfigProviderMock =
            $this->createMock(DefaultValueProvider::class);
        $this->interpreter = $this->getMockForAbstractClass(InterpreterInterface::class);
        $this->converter = new Converter(new BooleanUtils(), $this->interpreter, $this->defaultConfigProviderMock);
        $this->defaultConfigProviderMock->expects($this->any())->method('getConnection')->willReturn('amqp');
    }

    public function testConvert()
    {
        $fixtureDir = __DIR__ . '/../../../_files/queue_topology';
        $xmlFile = $fixtureDir . '/valid.xml';
        $dom = new \DOMDocument();
        $dom->load($xmlFile);

        $this->interpreter->expects($this->any())->method('evaluate')->willReturn(10);
        $result = $this->converter->convert($dom);

        $expectedData = include $fixtureDir . '/valid.php';
        foreach ($expectedData as $key => $value) {
            $this->assertEquals($value, $result[$key], 'Invalid data for ' . $key);
        }
    }
}
