<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
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
     * Initialize parameters
     */
    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->converter = $objectManager->getObject(
            'Magento\Framework\MessageQueue\Config\Reader\Xml\Converter\TopicConfig'
        );
    }

    /**
     * Test converting valid configuration
     */
    public function testConvert()
    {
        $xmlFile = __DIR__ . '/_files/topic_config.xml';
        $expectedData = include(__DIR__ . '/_files/expected_topic_config.php');
        $dom = new \DOMDocument();
        $dom->load($xmlFile);
        $result = $this->converter->convert($dom);
        $this->assertEquals($expectedData, $result);
    }
}
