<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Test\Unit\Config;

use Magento\Framework\MessageQueue\ConfigInterface as QueueConfig;
use Magento\Framework\MessageQueue\Config\Reader\EnvReader;

/**
 * @codingStandardsIgnoreFile
 */
class ConverterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\MessageQueue\Config\Reader\XmlReader\Converter
     */
    private $converter;

    /**
     * @var \Magento\Framework\Communication\ConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $communicationConfigMock;

    /**
     * Initialize parameters
     */
    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->communicationConfigMock = $this->getMockBuilder('Magento\Framework\Communication\ConfigInterface')
            ->getMock();

        $validator = $this->getMock('Magento\Framework\MessageQueue\Config\Validator', [], [], '', false, false);
        $validator->expects($this->atLeastOnce())->method('buildWildcardPattern')->willReturn('/some_regexp/');

        $this->converter = $objectManager->getObject(
            'Magento\Framework\MessageQueue\Config\Reader\XmlReader\Converter',
            [
                'communicationConfig' => $this->communicationConfigMock,
                'xmlValidator' => $validator
            ]
        );
    }

    /**
     * Test converting valid configuration
     */
    public function testConvert()
    {
        $this->markTestIncomplete('MAGETWO-45161');
        $this->communicationConfigMock->expects($this->any())->method('getTopics')->willReturn([]);
        $expected = $this->getConvertedQueueConfig();
        $xmlFile = __DIR__ . '/_files/queue.xml';
        $dom = new \DOMDocument();
        $dom->loadXML(file_get_contents($xmlFile));
        $result = $this->converter->convert($dom);
        $this->assertEquals($expected, $result);
    }

    /**
     * Get content of _files/queue.xml converted into array.
     *
     * @return array
     */
    protected function getConvertedQueueConfig()
    {
        return include(__DIR__ . '/_files/expected_queue.php');
    }
}
