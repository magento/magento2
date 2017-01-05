<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Test\Unit\Publisher;

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
        if (!function_exists('libxml_set_external_entity_loader')) {
            $this->markTestSkipped('Skipped on HHVM. Will be fixed in MAGETWO-45033');
        }
        $urnResolver = new \Magento\Framework\Config\Dom\UrnResolver();
        $this->_schemaFile = $urnResolver->getRealPath('urn:magento:framework-message-queue:etc/publisher.xsd');
    }

    /**
     * @param string $fixtureXml
     * @param array $expectedErrors
     * @dataProvider exemplarXmlDataProvider
     */
    public function testExemplarXml($fixtureXml, array $expectedErrors)
    {
        $validationState = $this->getMock(\Magento\Framework\Config\ValidationStateInterface::class, [], [], '', false);
        $validationState->expects($this->any())
            ->method('isValidationRequired')
            ->willReturn(true);
        $messageFormat = '%message%';
        $dom = new \Magento\Framework\Config\Dom($fixtureXml, $validationState, [], null, null, $messageFormat);
        $actualErrors = [];
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
                    "Element 'publisher': Duplicate key-sequence ['topic.message.queue.config.01'] in unique identity-constraint 'unique-publisher-topic'."
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
                    "Element 'connection': Duplicate key-sequence ['amqp'] in unique identity-constraint 'unique-connection-name'."
                ],
            ],
            'missed required publisher attribute' => [
                '<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:framework-message-queue:etc/publisher.xsd">
                    <publisher disabled="false">
                        <connection name="amqp" exchange="magento2" />                        
                    </publisher>
                </config>',
                [
                    "Element 'publisher': The attribute 'topic' is required but missing."
                ],
            ],
            'missed required connection attribute' => [
                '<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:framework-message-queue:etc/publisher.xsd">
                    <publisher topic="top01">
                        <connection exchange="magento2" />                        
                    </publisher>
                </config>',
                [
                    "Element 'connection': The attribute 'name' is required but missing."
                ],
            ],
            'unexpected publisher element' => [
                '<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:framework-message-queue:etc/publisher.xsd">
                    <unexpected name="10">20</unexpected>
                    <publisher topic="topic.message.queue.config.03" disabled="true" />
                </config>',
                [
                    "Element 'unexpected': This element is not expected. Expected is ( publisher )."
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
                    "Element 'unexpected': This element is not expected. Expected is ( connection )."
                ],
            ],
            'unexpected publisher attribute' => [
                '<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:framework-message-queue:etc/publisher.xsd">
                    <publisher topic="topic.message.queue.config.03" disabled="true" unexpected="10"/>
                </config>',
                [
                    "Element 'publisher', attribute 'unexpected': The attribute 'unexpected' is not allowed.",
                ],
            ],
            'unexpected connection attribute' => [
                '<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:framework-message-queue:etc/publisher.xsd">
                    <publisher topic="topic.message.queue.config.03" disabled="true">
                        <connection name="amqp" exchange="magento2" unexpected="10"/>
                    </publisher>
                </config>',
                [
                    "Element 'connection', attribute 'unexpected': The attribute 'unexpected' is not allowed.",
                ],
            ],
            'invalid connection attribute value' => [
                '<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:framework-message-queue:etc/publisher.xsd">
                    <publisher topic="topic.message.queue.config.03" disabled="true">
                        <connection name="amqp" exchange="magento2" disabled="disabled"/>
                    </publisher>
                </config>',
                [
                    "Element 'connection', attribute 'disabled': 'disabled' is not a valid value of the atomic type 'xs:boolean'.",
                ],
            ],
            'invalid publisher attribute value' => [
                '<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:framework-message-queue:etc/publisher.xsd">
                    <publisher topic="topic.message.queue.config.03" disabled="disabled">
                        <connection name="amqp" exchange="magento2" />
                    </publisher>
                </config>',
                [
                    "Element 'publisher', attribute 'disabled': 'disabled' is not a valid value of the atomic type 'xs:boolean'.",
                ],
            ],
        ];
    }
}
