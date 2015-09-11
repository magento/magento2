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
        $this->_schemaFile = BP . "/lib/internal/Magento/Framework/Amqp/etc/queue_merged.xsd";
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
                    <publisher name="test-publisher" connection="rabbitmq" exchange="magento"/>
                    <publisher name="test-publisher-2" connection="db" exchange="magento"/>
                    <topic name="customer.created" schema="Data\Type" publisher="test-publisher"/>
                    <topic name="customer.updated" schema="Data\Type" publisher="test-publisher-2"/>
                    <topic name="customer.deleted" schema="Data\Type" publisher="test-publisher-2"/>
                    <consumer name="customerCreatedListener" queue="test-queue-1" connection="rabbitmq" class="Data\Type" method="processMessage"/>
                    <consumer name="customerDeletedListener" queue="test-queue-2" connection="db" class="Other\Type" method="processMessage2" max_messages="98765"/>
                </config>',
                [],
            ],
            /** Uniqueness restriction violation */
            'non unique topics' => [
                '<config>
                    <publisher name="test-publisher" connection="rabbitmq" exchange="magento"/>
                    <publisher name="test-publisher-2" connection="db" exchange="magento"/>
                    <topic name="customer.created" schema="Data\Type" publisher="test-publisher"/>
                    <topic name="customer.created" schema="Data\TypeTwo" publisher="test-publisher"/>
                    <topic name="customer.updated" schema="Data\Type" publisher="test-publisher-2"/>
                    <topic name="customer.deleted" schema="Data\Type" publisher="test-publisher-2"/>
                </config>',
                [
                    "Element 'topic': Duplicate key-sequence ['customer.created'] in key identity-constraint 'topic-name'."
                ],
            ],
            'non unique publishers' => [
                '<config>
                    <publisher name="test-publisher" connection="rabbitmq" exchange="magento"/>
                    <publisher name="test-publisher-2" connection="db" exchange="magento"/>
                    <publisher name="test-publisher-2" connection="db" exchange="magento"/>
                    <topic name="customer.created" schema="Data\Type" publisher="test-publisher"/>
                    <topic name="customer.updated" schema="Data\Type" publisher="test-publisher-2"/>
                    <topic name="customer.deleted" schema="Data\Type" publisher="test-publisher-2"/>
                </config>',
                [
                    "Element 'publisher': Duplicate key-sequence ['test-publisher-2'] in key identity-constraint 'publisher-name'."
                ],
            ],
            'broken reference from topic to publisher' => [
                '<config>
                    <publisher name="test-publisher" connection="rabbitmq" exchange="magento"/>
                    <publisher name="test-publisher-2" connection="db" exchange="magento"/>
                    <topic name="customer.created" schema="Data\Type" publisher="test-publisher"/>
                    <topic name="customer.updated" schema="Data\Type" publisher="test-publisher-2"/>
                    <topic name="customer.deleted" schema="Data\Type" publisher="test-publisher-3"/>
                </config>',
                ["Element 'topic': No match found for key-sequence ['test-publisher-3'] of keyref 'publisher-ref'."],
            ],
            /** Excessive attributes */
            'invalid attribute in topic' => [
                '<config>
                    <publisher name="test-publisher" connection="rabbitmq" exchange="magento"/>
                    <publisher name="test-publisher-2" connection="db" exchange="magento"/>
                    <topic invalid="value" name="customer.created" schema="Data\Type" publisher="test-publisher"/>
                    <topic name="customer.updated" schema="Data\Type" publisher="test-publisher-2"/>
                    <topic name="customer.deleted" schema="Data\Type" publisher="test-publisher-2"/>
                </config>',
                ["Element 'topic', attribute 'invalid': The attribute 'invalid' is not allowed."],
            ],
            'invalid attribute in publisher' => [
                '<config>
                    <publisher name="test-publisher" connection="rabbitmq" exchange="magento"/>
                    <publisher invalid="value" name="test-publisher-2" connection="db" exchange="magento"/>
                    <topic name="customer.created" schema="Data\Type" publisher="test-publisher"/>
                    <topic name="customer.updated" schema="Data\Type" publisher="test-publisher-2"/>
                    <topic name="customer.deleted" schema="Data\Type" publisher="test-publisher-2"/>
                </config>',
                ["Element 'publisher', attribute 'invalid': The attribute 'invalid' is not allowed."],
            ],
            /** Missing or empty required attributes */
            'publisher without name' => [
                '<config>
                    <publisher connection="rabbitmq" exchange="magento"/>
                    <publisher name="test-publisher-2" connection="db" exchange="magento"/>
                    <topic name="customer.created" schema="Data\Type" publisher="test-publisher"/>
                    <topic name="customer.updated" schema="Data\Type" publisher="test-publisher-2"/>
                    <topic name="customer.deleted" schema="Data\Type" publisher="test-publisher-2"/>
                </config>',
                [
                    "Element 'publisher': The attribute 'name' is required but missing.",
                    "Element 'publisher': Not all fields of key identity-constraint 'publisher-name' evaluate to a node.",
                    "Element 'topic': No match found for key-sequence ['test-publisher'] of keyref 'publisher-ref'."
                ],
            ],
            'publisher without connection' => [
                '<config>
                    <publisher name="test-publisher" exchange="magento"/>
                    <publisher name="test-publisher-2" connection="db" exchange="magento"/>
                    <topic name="customer.created" schema="Data\Type" publisher="test-publisher"/>
                    <topic name="customer.updated" schema="Data\Type" publisher="test-publisher-2"/>
                    <topic name="customer.deleted" schema="Data\Type" publisher="test-publisher-2"/>
                </config>',
                ["Element 'publisher': The attribute 'connection' is required but missing."],
            ],
            'publisher without exchange' => [
                '<config>
                    <publisher name="test-publisher" connection="rabbitmq"/>
                    <publisher name="test-publisher-2" connection="db" exchange="magento"/>
                    <topic name="customer.created" schema="Data\Type" publisher="test-publisher"/>
                    <topic name="customer.updated" schema="Data\Type" publisher="test-publisher-2"/>
                    <topic name="customer.deleted" schema="Data\Type" publisher="test-publisher-2"/>
                </config>',
                ["Element 'publisher': The attribute 'exchange' is required but missing."],
            ],
            'topic without name' => [
                '<config>
                    <publisher name="test-publisher" connection="rabbitmq" exchange="magento"/>
                    <publisher name="test-publisher-2" connection="db" exchange="magento"/>
                    <topic schema="Data\Type" publisher="test-publisher"/>
                    <topic name="customer.updated" schema="Data\Type" publisher="test-publisher-2"/>
                    <topic name="customer.deleted" schema="Data\Type" publisher="test-publisher-2"/>
                </config>',
                [
                    "Element 'topic': The attribute 'name' is required but missing.",
                    "Element 'topic': Not all fields of key identity-constraint 'topic-name' evaluate to a node."
                ],
            ],
            'topic without schema' => [
                '<config>
                    <publisher name="test-publisher" connection="rabbitmq" exchange="magento"/>
                    <publisher name="test-publisher-2" connection="db" exchange="magento"/>
                    <topic name="customer.created" publisher="test-publisher"/>
                    <topic name="customer.updated" schema="Data\Type" publisher="test-publisher-2"/>
                    <topic name="customer.deleted" schema="Data\Type" publisher="test-publisher-2"/>
                </config>',
                ["Element 'topic': The attribute 'schema' is required but missing."],
            ],
            'topic without publisher' => [
                '<config>
                    <publisher name="test-publisher" connection="rabbitmq" exchange="magento"/>
                    <publisher name="test-publisher-2" connection="db" exchange="magento"/>
                    <topic name="customer.created" schema="Data\Type"/>
                    <topic name="customer.updated" schema="Data\Type" publisher="test-publisher-2"/>
                    <topic name="customer.deleted" schema="Data\Type" publisher="test-publisher-2"/>
                </config>',
                ["Element 'topic': The attribute 'publisher' is required but missing."],
            ],
            'consumer without name' => [
                '<config>
                    <consumer queue="test-queue" connection="rabbitmq" class="Data\Type" method="processMessage"/>
                </config>',
                [
                    "Element 'consumer': The attribute 'name' is required but missing.",
                ],
            ],
            'consumer without queue' => [
                '<config>
                    <consumer name="customerCreatedListener" connection="rabbitmq" class="Data\Type" method="processMessage"/>
                </config>',
                ["Element 'consumer': The attribute 'queue' is required but missing."],
            ],
            'consumer without connection' => [
                '<config>
                    <consumer name="customerCreatedListener" queue="test-queue" class="Data\Type" method="processMessage"/>
                </config>',
                ["Element 'consumer': The attribute 'connection' is required but missing."],
            ],
            'consumer without class' => [
                '<config>
                    <consumer name="customerCreatedListener" connection="rabbitmq" queue="test-queue" method="processMessage"/>
                </config>',
                ["Element 'consumer': The attribute 'class' is required but missing."],
            ],
            'consumer without method' => [
                '<config>
                    <consumer name="customerCreatedListener" connection="rabbitmq" queue="test-queue" class="Data\Type"/>
                </config>',
                ["Element 'consumer': The attribute 'method' is required but missing."],
            ],
            'consumer with same name' => [
                '<config>
                    <consumer name="customerCreatedListener" connection="rabbitmq" queue="test-queue" class="Data\Type" method="processMessage"/>
                    <consumer name="customerCreatedListener" connection="rabbitmq" queue="test-queue-2" class="Data\Type" method="processMessage"/>
                </config>',
                ["Element 'consumer': Duplicate key-sequence ['customerCreatedListener'] in unique identity-constraint 'consumer-unique-name'."],
            ],
            'consumer with invalid max messages' => [
                '<config>
                    <consumer name="customerCreatedListener" connection="rabbitmq" queue="test-queue" class="Data\Type" method="processMessage" max_messages="not_int"/>
                </config>',
                ["Element 'consumer', attribute 'max_messages': 'not_int' is not a valid value of the atomic type 'xs:integer'."],
            ],
            'consumer name invalid' => [
                '<config>
                    <consumer name="customer_created_listener" connection="rabbitmq" queue="test-queue" class="Data\Type" method="processMessage"/>
                </config>',
                [
                    "Element 'consumer', attribute 'name': [facet 'pattern'] The value 'customer_created_listener' is not accepted by the pattern '[a-z]([a-zA-Z])+'.",
                    "Element 'consumer', attribute 'name': 'customer_created_listener' is not a valid value of the atomic type 'consumerNameType'.",
                    "Element 'consumer', attribute 'name': Warning: No precomputed value available, the value was either invalid or something strange happend."
                ],
            ],
            'bind without queue' => [
                '<config>
                    <bind exchange="magento" topic="customer.created"/>
                </config>',
                [
                    "Element 'bind': The attribute 'queue' is required but missing.",
//                    "Element 'bind': No match found for key-sequence ['customer.created'] of keyref 'topic-ref'."
                ],
            ],
            'bind without exchange' => [
                '<config>
                    <bind queue="test-queue" topic="customer.created"/>
                </config>',
                [
                    "Element 'bind': The attribute 'exchange' is required but missing.",
//                    "Element 'bind': No match found for key-sequence ['customer.created'] of keyref 'topic-ref'."
                ],
            ],
            'bind without topic' => [
                '<config>
                    <bind queue="test-queue" exchange="magento"/>
                </config>',
                ["Element 'bind': The attribute 'topic' is required but missing."],
            ],
        ];
    }
}
