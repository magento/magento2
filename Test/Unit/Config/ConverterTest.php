<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Amqp\Test\Unit\Config;

class ConverterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Amqp\Config\Converter
     */
    protected $converter;

    /**
     * Initialize parameters
     */
    protected function setUp()
    {
        $this->converter = new \Magento\Framework\Amqp\Config\Converter();
    }

    /**
     * Testing converting valid cron configuration
     */
    public function testConvert()
    {
        $expected = [
            'test-queue' => [
                'name' => 'test-queue',
                'connection' => 'rabbitmq',
                'exchange' => 'magento'
            ]
        ];

        $xmlFile = __DIR__ . '/../_files/queue_valid.xml';
        $dom = new \DOMDocument();
        $dom->loadXML(file_get_contents($xmlFile));
        $result = $this->converter->convert($dom);
        $this->assertEquals($expected, $result);
    }
}
