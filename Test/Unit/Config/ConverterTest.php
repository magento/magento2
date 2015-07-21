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
            'publishers' => [
                'test-queue' => [
                    'name' => 'test-queue',
                    'connection' => 'rabbitmq',
                    'exchange' => 'magento',
                ],
                'test-queue-2' => [
                    'name' => 'test-queue-2',
                    'connection' => 'db',
                    'exchange' => 'magento',
                ],
            ],
            'topics' => [
                'customer.created' => [
                    'name' => 'customer.created',
                    'schema' => 'Magento\\Customer\\Api\\Data\\CustomerInterface',
                    'publisher' => 'test-queue',
                ],
                'customer.updated' => [
                    'name' => 'customer.updated',
                    'schema' => 'Magento\\Customer\\Api\\Data\\CustomerInterface',
                    'publisher' => 'test-queue-2',
                ],
                'customer.deleted' => [
                    'name' => 'customer.deleted',
                    'schema' => 'Magento\\Customer\\Api\\Data\\CustomerInterface',
                    'publisher' => 'test-queue-2',
                ],
            ],
        ];

        $xmlFile = __DIR__ . '/../_files/queue_valid.xml';
        $dom = new \DOMDocument();
        $dom->loadXML(file_get_contents($xmlFile));
        $result = $this->converter->convert($dom);
        $this->assertEquals($expected, $result);
    }
}
