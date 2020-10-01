<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\MessageQueue\Test\Unit\Config\Reader\Xml\Converter;

use Magento\Framework\Communication\ConfigInterface;
use Magento\Framework\MessageQueue\Config\Reader\Xml\Converter\TopicConfig;
use Magento\Framework\MessageQueue\Config\Validator;
use Magento\Framework\Reflection\MethodsMap;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/** to test <topic> root node type definition of MQ
 */
class TopicConverterTest extends TestCase
{
    /**
     * @var TopicConfig
     */
    private $converter;

    /**
     * @var MockObject
     */
    protected $methodMapMock;

    /**
     * @var MockObject
     */
    protected $validatorMock;

    /**
     * @var MockObject
     */
    protected $communicationConfigMock;

    /**
     * Initialize parameters
     */
    protected function setUp(): void
    {
        $this->methodMapMock = $this->createMock(MethodsMap::class);
        $this->validatorMock = $this->createMock(Validator::class);
        $this->communicationConfigMock = $this->getMockForAbstractClass(ConfigInterface::class);
        $wildcardPatternMap = include __DIR__ . '/../../../../_files/wildcard_pattern_map.php';
        $topicsMap = include __DIR__ . '/../../../../_files/topic_definitions_map.php';
        $this->validatorMock->expects($this->any())
            ->method('buildWildcardPattern')
            ->willReturnMap($wildcardPatternMap);

        $topicsDefinitions = [
            'user.created.remote' => [],
            'product.created.local' => [],
        ];
        $this->communicationConfigMock->expects($this->once())->method('getTopics')->willReturn($topicsDefinitions);

        $this->communicationConfigMock->expects($this->any())->method('getTopic')->willReturnMap($topicsMap);

        $this->converter = new TopicConfig(
            $this->methodMapMock,
            $this->validatorMock,
            $this->communicationConfigMock
        );
    }

    /**
     * Test converting valid configuration
     */
    public function testConvert()
    {
        $xmlFile = __DIR__ . '/../../../../_files/topic_config.xml';
        $expectedData = include __DIR__ . '/../../../../_files/expected_topic_config.php';
        $dom = new \DOMDocument();
        $dom->load($xmlFile);
        $result = $this->converter->convert($dom);
        $this->assertEquals($expectedData, $result);
    }
}
