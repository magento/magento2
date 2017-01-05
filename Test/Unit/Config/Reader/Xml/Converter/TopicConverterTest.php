<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Test\Unit\Config\Reader\Xml\Converter;

/**
 * Class TopicConverterTest to test <topic> root node type definition of MQ
 */
class TopicConverterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\MessageQueue\Config\Reader\Xml\Converter\TopicConfig
     */
    private $converter;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $methodMapMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $validatorMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $communicationConfigMock;

    /**
     * Initialize parameters
     */
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
        $this->communicationConfigMock = $this->getMock(\Magento\Framework\Communication\ConfigInterface::class);
        $wildcardPatternMap = include(__DIR__ . '/../../../../_files/wildcard_pattern_map.php');
        $topicsMap = include(__DIR__ . '/../../../../_files/topic_definitions_map.php');
        $this->validatorMock->expects($this->any())
            ->method('buildWildcardPattern')
            ->willReturnMap($wildcardPatternMap);

        $topicsDefinitions = [
            'user.created.remote' => [],
            'product.created.local' => [],
        ];
        $this->communicationConfigMock->expects($this->once())->method('getTopics')->willReturn($topicsDefinitions);

        $this->communicationConfigMock->expects($this->any())->method('getTopic')->willReturnMap($topicsMap);

        $this->converter = new \Magento\Framework\MessageQueue\Config\Reader\Xml\Converter\TopicConfig(
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
        $expectedData = include(__DIR__ . '/../../../../_files/expected_topic_config.php');
        $dom = new \DOMDocument();
        $dom->load($xmlFile);
        $result = $this->converter->convert($dom);
        $this->assertEquals($expectedData, $result);
    }
}
