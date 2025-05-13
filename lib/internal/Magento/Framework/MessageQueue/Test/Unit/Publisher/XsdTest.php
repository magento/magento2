<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\MessageQueue\Test\Unit\Publisher;

use Magento\Framework\Config\Dom;
use Magento\Framework\Config\Dom\UrnResolver;
use Magento\Framework\Config\ValidationStateInterface;
use PHPUnit\Framework\TestCase;

class XsdTest extends TestCase
{
    /**
     * @var string
     */
    protected $_schemaFile;

    protected function setUp(): void
    {
        if (!function_exists('libxml_set_external_entity_loader')) {
            $this->markTestSkipped('Skipped on HHVM. Will be fixed in MAGETWO-45033');
        }
        $urnResolver = new UrnResolver();
        $this->_schemaFile = $urnResolver->getRealPath('urn:magento:framework-message-queue:etc/publisher.xsd');
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
        $actualResult = $dom->validate($this->_schemaFile, $actualErrors);
        $this->assertEquals(empty($expectedErrors), $actualResult, "Validation result is invalid.");
        $this->assertEquals($expectedErrors, $actualErrors, "Validation errors does not match.");
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
                '<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:framework-message-queue:etc/publisher.xsd">
                    <publisher topic="topic.message.queue.config.01">
                        <connection name="amqp" exchange="magento2" />
                        <connection name="db" exchange="magento2" disabled="true" />
                    </publisher>
                    <publisher topic="topic.message.queue.config.02">
                        <connection name="amqp" exchange="magento2" disabled="true"/>
                        <connection name="db" exchange="magento2" disabled="true" />
                    </publisher>
                    <publisher topic="topic.message.queue.config.03" disabled="true" />
                </config>',
                [],
            ],
            'non unique publisher topic' => [
                '<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:framework-message-queue:etc/publisher.xsd">
                    <publisher topic="topic.message.queue.config.01">
                        <connection name="amqp" exchange="magento2" />
                    </publisher>
                    <publisher topic="topic.message.queue.config.01">
                        <connection name="amqp" exchange="magento2" disabled="true"/>
                    </publisher>
                </config>',
                [
                    "Element 'publisher': Duplicate key-sequence ['topic.message.queue.config.01'] in unique identity-constraint 'unique-publisher-topic'.The xml was: \n" .
                    "0:<?xml version=\"1.0\"?>\n1:<config xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xsi:noNamespaceSchemaLocation=\"urn:magento:framework-message-queue:etc/publisher.xsd\">\n" .
                    "2:                    <publisher topic=\"topic.message.queue.config.01\">\n" .
                    "3:                        <connection name=\"amqp\" exchange=\"magento2\"/>\n" .
                    "4:                    </publisher>\n5:                    <publisher topic=\"topic.message.queue.config.01\">\n" .
                    "6:                        <connection name=\"amqp\" exchange=\"magento2\" disabled=\"true\"/>\n7:                    </publisher>\n" .
                    "8:                </config>\n9:\n"
                ],
            ],
            'non unique publisher connection name' => [
                '<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:framework-message-queue:etc/publisher.xsd">
                    <publisher topic="topic.message.queue.config.01">
                        <connection name="amqp" exchange="magento2" />
                        <connection name="amqp" exchange="magento2" />
                    </publisher>
                </config>',
                [
                    "Element 'connection': Duplicate key-sequence ['amqp'] in unique identity-constraint 'unique-connection-name'.The xml was: \n" .
                    "0:<?xml version=\"1.0\"?>\n1:<config xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xsi:noNamespaceSchemaLocation=\"urn:magento:framework-message-queue:etc/publisher.xsd\">\n" .
                    "2:                    <publisher topic=\"topic.message.queue.config.01\">\n" .
                    "3:                        <connection name=\"amqp\" exchange=\"magento2\"/>\n" .
                    "4:                        <connection name=\"amqp\" exchange=\"magento2\"/>\n" .
                    "5:                    </publisher>\n6:                </config>\n7:\n"
                ],
            ],
            'missed required publisher attribute' => [
                '<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:framework-message-queue:etc/publisher.xsd">
                    <publisher disabled="false">
                        <connection name="amqp" exchange="magento2" />
                    </publisher>
                </config>',
                [
                    "Element 'publisher': The attribute 'topic' is required but missing.The xml was: \n" .
                    "0:<?xml version=\"1.0\"?>\n1:<config xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xsi:noNamespaceSchemaLocation=\"urn:magento:framework-message-queue:etc/publisher.xsd\">\n" .
                    "2:                    <publisher disabled=\"false\">\n" .
                    "3:                        <connection name=\"amqp\" exchange=\"magento2\"/>\n" .
                    "4:                    </publisher>\n5:                </config>\n6:\n"


                ],
            ],
            'missed required connection attribute' => [
                '<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:framework-message-queue:etc/publisher.xsd">
                    <publisher topic="top01">
                        <connection exchange="magento2" />
                    </publisher>
                </config>',
                [],
            ],
            'unexpected publisher element' => [
                '<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:framework-message-queue:etc/publisher.xsd">
                    <unexpected name="10">20</unexpected>
                    <publisher topic="topic.message.queue.config.03" disabled="true" />
                </config>',
                [
                    "Element 'unexpected': This element is not expected. Expected is ( publisher ).The xml was: \n" .
                    "0:<?xml version=\"1.0\"?>\n1:<config xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xsi:noNamespaceSchemaLocation=\"urn:magento:framework-message-queue:etc/publisher.xsd\">\n" .
                    "2:                    <unexpected name=\"10\">20</unexpected>\n" .
                    "3:                    <publisher topic=\"topic.message.queue.config.03\" disabled=\"true\"/>\n" .
                    "4:                </config>\n5:\n"
                ],
            ],
            'unexpected connection element' => [
                '<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:framework-message-queue:etc/publisher.xsd">
                    <publisher topic="topic.message.queue.config.03" disabled="true">
                        <connection name="amqp" exchange="magento2" />
                        <unexpected name="10">20</unexpected>
                    </publisher>
                </config>',
                [
                    "Element 'unexpected': This element is not expected. Expected is ( connection ).The xml was: \n" .
                    "0:<?xml version=\"1.0\"?>\n1:<config xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xsi:noNamespaceSchemaLocation=\"urn:magento:framework-message-queue:etc/publisher.xsd\">\n" .
                    "2:                    <publisher topic=\"topic.message.queue.config.03\" disabled=\"true\">\n" .
                    "3:                        <connection name=\"amqp\" exchange=\"magento2\"/>\n" .
                    "4:                        <unexpected name=\"10\">20</unexpected>\n" .
                    "5:                    </publisher>\n6:                </config>\n7:\n"
                ],
            ],
            'unexpected publisher attribute' => [
                '<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:framework-message-queue:etc/publisher.xsd">
                    <publisher topic="topic.message.queue.config.03" disabled="true" unexpected="10"/>
                </config>',
                [
                    "Element 'publisher', attribute 'unexpected': The attribute 'unexpected' is not allowed.The xml was: \n" .
                    "0:<?xml version=\"1.0\"?>\n1:<config xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xsi:noNamespaceSchemaLocation=\"urn:magento:framework-message-queue:etc/publisher.xsd\">\n" .
                    "2:                    <publisher topic=\"topic.message.queue.config.03\" disabled=\"true\" unexpected=\"10\"/>\n" .
                    "3:                </config>\n4:\n",
                ],
            ],
            'unexpected connection attribute' => [
                '<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:framework-message-queue:etc/publisher.xsd">
                    <publisher topic="topic.message.queue.config.03" disabled="true">
                        <connection name="amqp" exchange="magento2" unexpected="10"/>
                    </publisher>
                </config>',
                [
                    "Element 'connection', attribute 'unexpected': The attribute 'unexpected' is not allowed.The xml was: \n" .
                    "0:<?xml version=\"1.0\"?>\n1:<config xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xsi:noNamespaceSchemaLocation=\"urn:magento:framework-message-queue:etc/publisher.xsd\">\n" .
                    "2:                    <publisher topic=\"topic.message.queue.config.03\" disabled=\"true\">\n" .
                    "3:                        <connection name=\"amqp\" exchange=\"magento2\" unexpected=\"10\"/>\n" .
                    "4:                    </publisher>\n5:                </config>\n6:\n",
                ],
            ],
            'invalid connection attribute value' => [
                '<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:framework-message-queue:etc/publisher.xsd">
                    <publisher topic="topic.message.queue.config.03" disabled="true">
                        <connection name="amqp" exchange="magento2" disabled="disabled"/>
                    </publisher>
                </config>',
                [
                    "Element 'connection', attribute 'disabled': 'disabled' is not a valid value of the atomic type 'xs:boolean'.The xml was: \n" .
                    "0:<?xml version=\"1.0\"?>\n1:<config xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xsi:noNamespaceSchemaLocation=\"urn:magento:framework-message-queue:etc/publisher.xsd\">\n" .
                    "2:                    <publisher topic=\"topic.message.queue.config.03\" disabled=\"true\">\n" .
                    "3:                        <connection name=\"amqp\" exchange=\"magento2\" disabled=\"disabled\"/>\n" .
                    "4:                    </publisher>\n5:                </config>\n6:\n",
                ],
            ],
            'invalid publisher attribute value' => [
                '<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:framework-message-queue:etc/publisher.xsd">
                    <publisher topic="topic.message.queue.config.03" disabled="disabled">
                        <connection name="amqp" exchange="magento2" />
                    </publisher>
                </config>',
                [
                    "Element 'publisher', attribute 'disabled': 'disabled' is not a valid value of the atomic type 'xs:boolean'.The xml was: \n" .
                    "0:<?xml version=\"1.0\"?>\n1:<config xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xsi:noNamespaceSchemaLocation=\"urn:magento:framework-message-queue:etc/publisher.xsd\">\n" .
                    "2:                    <publisher topic=\"topic.message.queue.config.03\" disabled=\"disabled\">\n" .
                    "3:                        <connection name=\"amqp\" exchange=\"magento2\"/>\n" .
                    "4:                    </publisher>\n5:                </config>\n6:\n",
                ],
            ],
        ];
        // @codingStandardsIgnoreEnd
    }
}
