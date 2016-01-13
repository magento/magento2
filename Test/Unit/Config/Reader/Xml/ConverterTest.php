<?php

namespace Magento\Framework\MessageQueue\Test\Unit\Config\Reader\Xml;

/**
 * Class ConverterTest
 *
 */
class ConverterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\MessageQueue\Config\Reader\Xml\Converter
     */
    private $converter;

    /**
     * @var \Magento\Framework\MessageQueue\Config\Reader\Xml\Converter\DeprecatedFormat
     */
    protected $deprecatedConfigMock;

    /**
     * @var \Magento\Framework\MessageQueue\Config\Reader\Xml\Converter\TopicConfig
     */
    protected $topicConfigMock;

    /**
     * Initialize parameters
     */
    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->topicConfigMock =
            $this->getMockBuilder('Magento\Framework\MessageQueue\Config\Reader\Xml\Converter\TopicConfig')
                ->disableOriginalConstructor()
                ->setMethods([])
                ->getMock();

        $this->deprecatedConfigMock =
            $this->getMockBuilder('Magento\Framework\MessageQueue\Config\Reader\Xml\Converter\DeprecatedFormat')
                ->disableOriginalConstructor()
                ->setMethods([])
                ->getMock();

        $this->converter = $objectManager->getObject(
            'Magento\Framework\MessageQueue\Config\Reader\Xml\Converter',
            [
                'topicConfig' => $this->topicConfigMock,
                'deprecatedConfig' => $this->deprecatedConfigMock
            ]
        );
    }

    // TODO: Mock CommunicationConfig
    public function testConvert()
    {
        $topicConfigData = include(__DIR__ . '/../../_files/expected_topic_config.php');
        $deprecatedConfigData = include(__DIR__ . '/../../_files/expected_queue.php');
        // $expectedData = include(__DIR__ . '/../../../_files/');
        $dom = new \DOMDocument();
        $this->topicConfigMock->expects($this->once())->method('convert')->willReturn($topicConfigData);
        $this->deprecatedConfigMock->expects($this->once())->method('convert')->willReturn($deprecatedConfigData);
        $result = $this->converter->convert($dom);
        // $this->assertEquals($expectedData, $result);
    }
}
