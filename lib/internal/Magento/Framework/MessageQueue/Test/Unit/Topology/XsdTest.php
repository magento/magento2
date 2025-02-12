<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\MessageQueue\Test\Unit\Topology;

use Magento\Framework\Config\Dom;
use Magento\Framework\Config\Dom\UrnResolver;
use Magento\Framework\Config\ValidationStateInterface;
use PHPUnit\Framework\TestCase;

class XsdTest extends TestCase
{
    /**
     * @var string
     */
    private $schemaFile;

    protected function setUp(): void
    {
        if (!function_exists('libxml_set_external_entity_loader')) {
            $this->markTestSkipped('Skipped on HHVM. Will be fixed in MAGETWO-45033');
        }
        $urnResolver = new UrnResolver();
        $this->schemaFile = $urnResolver->getRealPath('urn:magento:framework-message-queue:etc/topology.xsd');
    }

    /**
     * @param string $fixtureXml
     * @param array $expectedErrors
     * @dataProvider exemplarXmlDataProvider
     */
    public function testExemplarXml($fixtureXml, array $expectedErrors)
    {
        $validationState = $this->getMockForAbstractClass(ValidationStateInterface::class);
        $validationState->expects($this->any())
            ->method('isValidationRequired')
            ->willReturn(true);
        $messageFormat = '%message%';
        $dom = new Dom($fixtureXml, $validationState, [], null, null, $messageFormat);
        $actualErrors = [];
        $actualResult = $dom->validate($this->schemaFile, $actualErrors);
        $this->assertEquals(empty($expectedErrors), $actualResult, "Validation result is invalid.");
        foreach ($expectedErrors as $error) {
            $this->assertContains($error, $actualErrors, "Validation errors does not match.");
        }
    }

    /**
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public static function exemplarXmlDataProvider()
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
                    "Element 'exchange': Duplicate key-sequence ['ex01', 'amqp'] in unique identity-constraint 'unique-exchange-name-connection'.The xml was: \n" .
                    "0:<?xml version=\"1.0\"?>\n1:<config xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xsi:noNamespaceSchemaLocation=\"urn:magento:framework-message-queue:etc/topology.xsd\">\n" .
                    "2:                        <exchange name=\"ex01\" type=\"topic\" connection=\"amqp\"/>\n" .
                    "3:                        <exchange name=\"ex01\" type=\"topic\" connection=\"amqp\"/>\n" .
                    "4:                </config>\n5:\n"
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
                    "Element 'binding': Duplicate key-sequence ['bind01'] in unique identity-constraint 'unique-binding-id'.The xml was: \n" .
                    "0:<?xml version=\"1.0\"?>\n1:<config xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xsi:noNamespaceSchemaLocation=\"urn:magento:framework-message-queue:etc/topology.xsd\">\n" .
                    "2:                        <exchange name=\"ex01\" connection=\"amqp\">\n" .
                    "3:                            <binding id=\"bind01\" destinationType=\"queue\" destination=\"queue01\" topic=\"top01\" disabled=\"true\"/>\n" .
                    "4:                            <binding id=\"bind01\" destinationType=\"queue\" destination=\"queue01\" topic=\"top01\"/>\n" .
                    "5:                        </exchange>\n6:                </config>\n7:\n"
                ],
            ],
            'invalid-destination-type-binding' => [
                '<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:framework-message-queue:etc/topology.xsd">
                    <exchange name="ex01" type="topic" connection="amqp">
                        <binding id="bind01" destinationType="topic" destination="queue01" topic="top01" />
                    </exchange>
                </config>',
                [
                    "Element 'binding', attribute 'destinationType': [facet 'enumeration'] The value 'topic' is not an element of the set {'queue'}.The xml was: \n" .
                    "0:<?xml version=\"1.0\"?>\n1:<config xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xsi:noNamespaceSchemaLocation=\"urn:magento:framework-message-queue:etc/topology.xsd\">\n" .
                    "2:                    <exchange name=\"ex01\" type=\"topic\" connection=\"amqp\">\n" .
                    "3:                        <binding id=\"bind01\" destinationType=\"topic\" destination=\"queue01\" topic=\"top01\"/>\n" .
                    "4:                    </exchange>\n5:                </config>\n6:\n"
                ],
            ],
            'invalid-exchange-type-binding' => [
                '<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:framework-message-queue:etc/topology.xsd">
                    <exchange name="ex01" type="exchange" connection="amqp">
                        <binding id="bind01" destinationType="queue" destination="queue01" topic="top01" />
                    </exchange>
                </config>',
                [
                    "Element 'exchange', attribute 'type': [facet 'enumeration'] The value 'exchange' is not an element of the set {'topic'}.The xml was: \n" .
                    "0:<?xml version=\"1.0\"?>\n1:<config xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xsi:noNamespaceSchemaLocation=\"urn:magento:framework-message-queue:etc/topology.xsd\">\n" .
                    "2:                    <exchange name=\"ex01\" type=\"exchange\" connection=\"amqp\">\n" .
                    "3:                        <binding id=\"bind01\" destinationType=\"queue\" destination=\"queue01\" topic=\"top01\"/>\n" .
                    "4:                    </exchange>\n5:                </config>\n6:\n"
                ],
            ],
        ];
        // @codingStandardsIgnoreEnd
    }
}
