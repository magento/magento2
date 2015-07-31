<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Amqp\Test\Unit\Config;

/**
 * @codingStandardsIgnoreFile
 */
class XsdTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    protected $_schemaFile;

    protected function setUp()
    {
        $this->_schemaFile = BP . "/lib/internal/Magento/Framework/Amqp/etc/queue.xsd";
    }

    /**
     * @param string $fixtureXml
     * @param array $expectedErrors
     * @dataProvider exemplarXmlDataProvider
     */
    public function testExemplarXml($fixtureXml, array $expectedErrors)
    {
        $messageFormat = '%message%';
        $dom = new \Magento\Framework\Config\Dom($fixtureXml, [], null, null, $messageFormat);
        $actualResult = $dom->validate($this->_schemaFile, $actualErrors);
        $this->assertEquals(empty($expectedErrors), $actualResult, "Validation result is invalid.");
        $this->assertEquals($expectedErrors, $actualErrors, "Validation errors does not match.");
    }

    /**
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function exemplarXmlDataProvider()
    {
        return [
            /** Valid configurations */
            'valid' => [
                '<config>
                    <publisher name="test-queue" connection="rabbitmq" exchange="magento"/>
                    <publisher name="test-queue-2" connection="db" exchange="magento"/>
                    <topic name="customer.created" schema="Data\Type" publisher="test-queue"/>
                    <topic name="customer.updated" schema="Data\Type" publisher="test-queue-2"/>
                    <topic name="customer.deleted" schema="Data\Type" publisher="test-queue-2"/>
                </config>',
                [],
            ],
            /** Uniqueness restriction violation */
            'non unique topics' => [
                '<config>
                    <publisher name="test-queue" connection="rabbitmq" exchange="magento"/>
                    <publisher name="test-queue-2" connection="db" exchange="magento"/>
                    <topic name="customer.created" schema="Data\Type" publisher="test-queue"/>
                    <topic name="customer.created" schema="Data\TypeTwo" publisher="test-queue"/>
                    <topic name="customer.updated" schema="Data\Type" publisher="test-queue-2"/>
                    <topic name="customer.deleted" schema="Data\Type" publisher="test-queue-2"/>
                </config>',
                [
                    "Element 'topic': Duplicate key-sequence ['customer.created'] in unique "
                    . "identity-constraint 'topic-unique-name'."
                ],
            ],
            'non unique publishers' => [
                '<config>
                    <publisher name="test-queue" connection="rabbitmq" exchange="magento"/>
                    <publisher name="test-queue-2" connection="db" exchange="magento"/>
                    <publisher name="test-queue-2" connection="db" exchange="magento"/>
                    <topic name="customer.created" schema="Data\Type" publisher="test-queue"/>
                    <topic name="customer.updated" schema="Data\Type" publisher="test-queue-2"/>
                    <topic name="customer.deleted" schema="Data\Type" publisher="test-queue-2"/>
                </config>',
                [
                    "Element 'publisher': Duplicate key-sequence ['test-queue-2'] in key "
                    . "identity-constraint 'publisher-name'."
                ],
            ],
            'broken reference from topic to publisher' => [
                '<config>
                    <publisher name="test-queue" connection="rabbitmq" exchange="magento"/>
                    <publisher name="test-queue-2" connection="db" exchange="magento"/>
                    <topic name="customer.created" schema="Data\Type" publisher="test-queue"/>
                    <topic name="customer.updated" schema="Data\Type" publisher="test-queue-2"/>
                    <topic name="customer.deleted" schema="Data\Type" publisher="test-queue-3"/>
                </config>',
                ["Element 'topic': No match found for key-sequence ['test-queue-3'] of keyref 'publisher-ref'."],
            ],
            /** Excessive attributes */
            'invalid attribute in topic' => [
                '<config>
                    <publisher name="test-queue" connection="rabbitmq" exchange="magento"/>
                    <publisher name="test-queue-2" connection="db" exchange="magento"/>
                    <topic invalid="value" name="customer.created" schema="Data\Type" publisher="test-queue"/>
                    <topic name="customer.updated" schema="Data\Type" publisher="test-queue-2"/>
                    <topic name="customer.deleted" schema="Data\Type" publisher="test-queue-2"/>
                </config>',
                ["Element 'topic', attribute 'invalid': The attribute 'invalid' is not allowed."],
            ],
            'invalid attribute in publisher' => [
                '<config>
                    <publisher name="test-queue" connection="rabbitmq" exchange="magento"/>
                    <publisher invalid="value" name="test-queue-2" connection="db" exchange="magento"/>
                    <topic name="customer.created" schema="Data\Type" publisher="test-queue"/>
                    <topic name="customer.updated" schema="Data\Type" publisher="test-queue-2"/>
                    <topic name="customer.deleted" schema="Data\Type" publisher="test-queue-2"/>
                </config>',
                ["Element 'publisher', attribute 'invalid': The attribute 'invalid' is not allowed."],
            ],
            /** Missing or empty required attributes */
            'publisher without name' => [
                '<config>
                    <publisher connection="rabbitmq" exchange="magento"/>
                    <publisher name="test-queue-2" connection="db" exchange="magento"/>
                    <topic name="customer.created" schema="Data\Type" publisher="test-queue"/>
                    <topic name="customer.updated" schema="Data\Type" publisher="test-queue-2"/>
                    <topic name="customer.deleted" schema="Data\Type" publisher="test-queue-2"/>
                </config>',
                [
                    "Element 'publisher': The attribute 'name' is required but missing.",
                    "Element 'publisher': Not all fields of key identity-constraint 'publisher-name' "
                    . "evaluate to a node.",
                    "Element 'topic': No match found for key-sequence ['test-queue'] of keyref 'publisher-ref'."
                ],
            ],
            'publisher without connection' => [
                '<config>
                    <publisher name="test-queue" exchange="magento"/>
                    <publisher name="test-queue-2" connection="db" exchange="magento"/>
                    <topic name="customer.created" schema="Data\Type" publisher="test-queue"/>
                    <topic name="customer.updated" schema="Data\Type" publisher="test-queue-2"/>
                    <topic name="customer.deleted" schema="Data\Type" publisher="test-queue-2"/>
                </config>',
                ["Element 'publisher': The attribute 'connection' is required but missing."],
            ],
            'publisher without exchange' => [
                '<config>
                    <publisher name="test-queue" connection="rabbitmq"/>
                    <publisher name="test-queue-2" connection="db" exchange="magento"/>
                    <topic name="customer.created" schema="Data\Type" publisher="test-queue"/>
                    <topic name="customer.updated" schema="Data\Type" publisher="test-queue-2"/>
                    <topic name="customer.deleted" schema="Data\Type" publisher="test-queue-2"/>
                </config>',
                ["Element 'publisher': The attribute 'exchange' is required but missing."],
            ],
            'topic without name' => [
                '<config>
                    <publisher name="test-queue" connection="rabbitmq" exchange="magento"/>
                    <publisher name="test-queue-2" connection="db" exchange="magento"/>
                    <topic schema="Data\Type" publisher="test-queue"/>
                    <topic name="customer.updated" schema="Data\Type" publisher="test-queue-2"/>
                    <topic name="customer.deleted" schema="Data\Type" publisher="test-queue-2"/>
                </config>',
                ["Element 'topic': The attribute 'name' is required but missing."],
            ],
            'topic without schema' => [
                '<config>
                    <publisher name="test-queue" connection="rabbitmq" exchange="magento"/>
                    <publisher name="test-queue-2" connection="db" exchange="magento"/>
                    <topic name="customer.created" publisher="test-queue"/>
                    <topic name="customer.updated" schema="Data\Type" publisher="test-queue-2"/>
                    <topic name="customer.deleted" schema="Data\Type" publisher="test-queue-2"/>
                </config>',
                ["Element 'topic': The attribute 'schema' is required but missing."],
            ],
            'topic without publisher' => [
                '<config>
                    <publisher name="test-queue" connection="rabbitmq" exchange="magento"/>
                    <publisher name="test-queue-2" connection="db" exchange="magento"/>
                    <topic name="customer.created" schema="Data\Type"/>
                    <topic name="customer.updated" schema="Data\Type" publisher="test-queue-2"/>
                    <topic name="customer.deleted" schema="Data\Type" publisher="test-queue-2"/>
                </config>',
                ["Element 'topic': The attribute 'publisher' is required but missing."],
            ],
            'consumer without name' => [
                '<config>
                    <consumer queue="test-queue" connection="rabbitmq" class="Data\Type" method="processMessage"/>
                </config>',
                [
                    "Element 'consumer': The attribute 'name' is required but missing.",
                    "Element 'consumer': Not all fields of key identity-constraint 'consumer-name' evaluate to a node.",
                ],
            ],
            'consumer without queue' => [
                '<config>
                    <consumer name="customer_created_listener" connection="rabbitmq" class="Data\Type" method="processMessage"/>
                </config>',
                ["Element 'consumer': The attribute 'queue' is required but missing."],
            ],
            'consumer without connection' => [
                '<config>
                    <consumer name="customer_created_listener" queue="test-queue" class="Data\Type" method="processMessage"/>
                </config>',
                ["Element 'consumer': The attribute 'connection' is required but missing."],
            ],
            'consumer without class' => [
                '<config>
                    <consumer name="customer_created_listener" connection="rabbitmq" queue="test-queue" method="processMessage"/>
                </config>',
                ["Element 'consumer': The attribute 'class' is required but missing."],
            ],
            'consumer without method' => [
                '<config>
                    <consumer name="customer_created_listener" connection="rabbitmq" queue="test-queue" class="Data\Type"/>
                </config>',
                ["Element 'consumer': The attribute 'method' is required but missing."],
            ],
            'consumer with same name' => [
                '<config>
                    <consumer name="customer_created_listener" connection="rabbitmq" queue="test-queue" class="Data\Type" method="processMessage"/>
                    <consumer name="customer_created_listener" connection="rabbitmq" queue="test-queue-2" class="Data\Type" method="processMessage"/>
                </config>',
                ["Element 'consumer': Duplicate key-sequence ['customer_created_listener'] in key identity-constraint 'consumer-name'."],
            ],
        ];
    }
}
