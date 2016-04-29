<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Test\Unit\Config\Reader\Xml\Converter;

use Magento\Framework\MessageQueue\Config\Reader\Xml\Converter\DeprecatedFormat;

class DeprecatedFormatTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DeprecatedFormat
     */
    protected $converter;

    /**
     * @var \PHPUnit_Framework_MockObject_MockBuilder
     */
    protected $methodMapMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockBuilder
     */
    protected $validatorMock;

    protected function setUp()
    {
        $this->methodMapMock = $this->getMock(
            \Magento\Framework\Reflection\MethodsMap::class,
            [],
            [],
            '',
            false,
            false
        );
        $this->validatorMock = $this->getMock(
            \Magento\Framework\MessageQueue\Config\Validator::class,
            [],
            [],
            '',
            false,
            false
        );
        $wildcardPatternMap =  include(__DIR__ . '/../../../../_files/wildcard_pattern_map.php');
        $this->validatorMock->expects($this->any())
            ->method('buildWildcardPattern')
            ->willReturnMap($wildcardPatternMap);

        $this->converter = new DeprecatedFormat($this->methodMapMock, $this->validatorMock);
    }

    /**
     * @param string $type
     * @dataProvider typeDataProvider
     */
    public function testConvert($type)
    {
        $xmlFile = __DIR__ . '/../../../../_files/queue.xml';
        $expectedData = include(__DIR__ . '/../../../../_files/expected_queue.php');

        $dom = new \DOMDocument();
        $dom->load($xmlFile);
        $data = $this->converter->convert($dom);

        $this->assertArrayHasKey($type, $data, 'Invalid output structure');
        $this->assertEquals($expectedData[$type], $data[$type], 'Invalid configuration of ' . $type);
    }

    /**
     * Configuration type data provider
     *
     * @return array
     */
    public function typeDataProvider()
    {
        return [
            'publishers' => ['publishers'],
            'topics' => ['topics'],
            'consumers' => ['consumers'],
            'binds' => ['binds'],
            'exchange_topic_to_queues_map' => ['exchange_topic_to_queues_map']
        ];
    }
}
