<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Test\Unit\Topology;

class XsdTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var string
     */
    private $schemaFile;

    protected function setUp()
    {
        if (!function_exists('libxml_set_external_entity_loader')) {
            $this->markTestSkipped('Skipped on HHVM. Will be fixed in MAGETWO-45033');
        }
        $urnResolver = new \Magento\Framework\Config\Dom\UrnResolver();
        $this->schemaFile = $urnResolver->getRealPath('urn:magento:framework-message-queue:etc/topology.xsd');
    }

    /**
     * @param string $fixtureXml
     * @param array $expectedErrors
     * @dataProvider exemplarXmlDataProvider
     */
    public function testExemplarXml($fixtureXml, array $expectedErrors)
    {
        $validationState = $this->createMock(\Magento\Framework\Config\ValidationStateInterface::class);
        $validationState->expects($this->any())
            ->method('isValidationRequired')
            ->willReturn(true);
        $messageFormat = '%message%';
        $dom = new \Magento\Framework\Config\Dom($fixtureXml, $validationState, [], null, null, $messageFormat);
        $actualErrors = [];
        $actualResult = $dom->validate($this->schemaFile, $actualErrors);
        $this->assertEquals(empty($expectedErrors), $actualResult, "Validation result is invalid.");
        $this->assertEquals($expectedErrors, $actualErrors, "Validation errors does not match.");
    }

    /**
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function exemplarXmlDataProvider()
    {
        // @codingStandardsIgnoreStart
        return [
            /** Valid configurations */
            'valid' => [
                '<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:framework-message-queue:etc/topology.xsd">
                        <exchange name="ex01" type="topic" connection="amqp"/>
                        <exchange name="ex02" type="topic" connection="amqp-02" />
                        <exchange name="ex03" autoDelete="true" durable="false" internal="true" type="topic" connection="db">
                            <arguments>
                                <argument name="arg1" xsi:type="string">10</argument>
                            </arguments>
                        </exchange>
                        <exchange name="ex04" connection="amqp-03">
                            <binding id="bind01" destinationType="queue" destination="queue01" topic="top01" disabled="true" />
                            <binding id="bind02" destinationType="queue" destination="queue01" topic="top01">
                                <arguments>
                                    <argument name="arg01" xsi:type="string">10</argument>
                                </arguments>
                            </binding>
                        </exchange>
                </config>',
                [],
            ],
            'non-unique-exchange' => [
                '<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:framework-message-queue:etc/topology.xsd">
                        <exchange name="ex01" type="topic" connection="amqp"/>
                        <exchange name="ex01" type="topic" connection="amqp" />
                </config>',
                [
                    "Element 'exchange': Duplicate key-sequence ['ex01', 'amqp'] in unique identity-constraint 'unique-exchange-name-connection'."
                ],
            ],
            'non-unique-exchange-binding' => [
                '<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:framework-message-queue:etc/topology.xsd">
                        <exchange name="ex01" connection="amqp">
                            <binding id="bind01" destinationType="queue" destination="queue01" topic="top01" disabled="true" />
                            <binding id="bind01" destinationType="queue" destination="queue01" topic="top01" />
                        </exchange>
                </config>',
                [
                    "Element 'binding': Duplicate key-sequence ['bind01'] in unique identity-constraint 'unique-binding-id'."
                ],
            ],
            'invalid-destination-type-binding' => [
                '<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:framework-message-queue:etc/topology.xsd">
                    <exchange name="ex01" type="topic" connection="amqp">
                        <binding id="bind01" destinationType="topic" destination="queue01" topic="top01" />
                    </exchange>
                </config>',
                [
                    "Element 'binding', attribute 'destinationType': [facet 'enumeration'] The value 'topic' is not an element of the set {'queue'}.",
                    "Element 'binding', attribute 'destinationType': 'topic' is not a valid value of the atomic type 'destinationType'."
                ],
            ],
            'invalid-exchange-type-binding' => [
                '<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:framework-message-queue:etc/topology.xsd">
                    <exchange name="ex01" type="exchange" connection="amqp">
                        <binding id="bind01" destinationType="queue" destination="queue01" topic="top01" />
                    </exchange>
                </config>',
                [
                    "Element 'exchange', attribute 'type': [facet 'enumeration'] The value 'exchange' is not an element of the set {'topic'}.",
                    "Element 'exchange', attribute 'type': 'exchange' is not a valid value of the atomic type 'exchangeType'."
                ],
            ],
        ];
        // @codingStandardsIgnoreEnd
    }
}
