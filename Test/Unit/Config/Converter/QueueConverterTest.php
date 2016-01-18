<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Test\Unit\Config\Converter;

use Magento\Framework\MessageQueue\Config\Reader\Env;

/**
 * Class ConverterTest to test <queue> root node type definition of MQ
 *
 */
class QueueConverterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\MessageQueue\Config\Reader\Xml\Converter\QueueConfig
     */
    private $converter;

    /**
     * Initialize parameters
     */
    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->converter = $objectManager->getObject(
            'Magento\Framework\MessageQueue\Config\Reader\Xml\Converter\QueueConfig'
        );
    }

    /**
     * Test converting valid configuration
     */
    public function testConvert()
    {
        $expected = $this->getConvertedQueueConfig();
        $xmlFile = __DIR__ . '/_files/queue_config.xml';
        $dom = new \DOMDocument();
        $dom->loadXML(file_get_contents($xmlFile));
        $result = $this->converter->convert($dom);
        $this->assertEquals($expected, $result);
    }

    /**
     * Returns expected output
     *
     * @return array
     */
    protected function getConvertedQueueConfig()
    {
        return include(__DIR__ . '/_files/expected_queue_config.php');
    }
}
