<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Test\Unit\Consumer\Config;

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
        $this->_schemaFile = $urnResolver->getRealPath('urn:magento:framework-message-queue:etc/consumer.xsd');
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
                '<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:framework-message-queue:etc/consumer.xsd">
                    <consumer name="consumer1" queue="queue1" handler="handlerClassOne::handlerMethodOne" consumerInstance="consumerClass1" connection="amqp" maxMessages="100"/>
                    <consumer name="consumer2" queue="queue2" handler="handlerClassTwo::handlerMethodTwo" consumerInstance="consumerClass2" connection="db"/>
                    <consumer name="consumer3" queue="queue3" handler="handlerClassThree::handlerMethodThree" consumerInstance="consumerClass3"/>
                    <consumer name="consumer4" queue="queue4" handler="handlerClassFour::handlerMethodFour"/>
                    <consumer name="consumer5" queue="queue4"/>
                </config>',
                [],
            ],
            'non unique consumer name' => [
                '<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:framework-message-queue:etc/consumer.xsd">
                    <consumer name="consumer1" queue="queue1" handler="handlerClassOne::handlerMethodOne" consumerInstance="consumerClass1" connection="amqp" maxMessages="100"/>
                    <consumer name="consumer1" queue="queue2" handler="handlerClassTwo::handlerMethodTwo" consumerInstance="consumerClass2" connection="db"/>
                    <consumer name="consumer3" queue="queue3" handler="handlerClassThree::handlerMethodThree" consumerInstance="consumerClass3"/>
                    <consumer name="consumer4" queue="queue4" handler="handlerClassFour::handlerMethodFour"/>
                    <consumer name="consumer5" queue="queue4"/>
                </config>',
                [
                    "Element 'consumer': Duplicate key-sequence ['consumer1'] in unique identity-constraint 'consumer-unique-name'."
                ],
            ],
            'invalid handler format' => [
                '<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:framework-message-queue:etc/consumer.xsd">
                    <consumer name="consumer1" queue="queue1" handler="handlerClass1::handlerMethodOne" consumerInstance="consumerClass1" connection="amqp" maxMessages="100"/>
                    <consumer name="consumer2" queue="queue2" handler="handlerClassTwo::handlerMethod2" consumerInstance="consumerClass2" connection="db"/>
                    <consumer name="consumer3" queue="queue3" handler="handlerClassThree::handlerMethodThree" consumerInstance="consumerClass3"/>
                    <consumer name="consumer4" queue="queue4" handler="handlerClassFour::handlerMethodFour"/>
                    <consumer name="consumer5" queue="queue4"/>
                </config>',
                [
                    "Element 'consumer', attribute 'handler': [facet 'pattern'] The value 'handlerClass1::handlerMethodOne' is not accepted by the pattern '[a-zA-Z\\\\]+::[a-zA-Z]+'.",
                    "Element 'consumer', attribute 'handler': 'handlerClass1::handlerMethodOne' is not a valid value of the atomic type 'handlerType'.",
                    "Element 'consumer', attribute 'handler': [facet 'pattern'] The value 'handlerClassTwo::handlerMethod2' is not accepted by the pattern '[a-zA-Z\\\\]+::[a-zA-Z]+'.",
                    "Element 'consumer', attribute 'handler': 'handlerClassTwo::handlerMethod2' is not a valid value of the atomic type 'handlerType'.",
                ],
            ],
            'invalid maxMessages format' => [
                '<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:framework-message-queue:etc/consumer.xsd">
                    <consumer name="consumer1" queue="queue1" handler="handlerClassOne::handlerMethodOne" consumerInstance="consumerClass1" connection="amqp" maxMessages="ABC"/>
                    <consumer name="consumer2" queue="queue2" handler="handlerClassTwo::handlerMethodTwo" consumerInstance="consumerClass2" connection="db"/>
                    <consumer name="consumer3" queue="queue3" handler="handlerClassThree::handlerMethodThree" consumerInstance="consumerClass3"/>
                    <consumer name="consumer4" queue="queue4" handler="handlerClassFour::handlerMethodFour"/>
                    <consumer name="consumer5" queue="queue4"/>
                </config>',
                [
                    "Element 'consumer', attribute 'maxMessages': 'ABC' is not a valid value of the atomic type 'xs:integer'.",
                ],
            ],
            'unexpected element' => [
                '<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:framework-message-queue:etc/consumer.xsd">
                    <consumer name="consumer1" queue="queue1" handler="handlerClassOne::handlerMethodOne" consumerInstance="consumerClass1" connection="amqp" maxMessages="100"/>
                    <consumer name="consumer2" queue="queue2" handler="handlerClassTwo::handlerMethodTwo" consumerInstance="consumerClass2" connection="db"/>
                    <consumer name="consumer3" queue="queue3" handler="handlerClassThree::handlerMethodThree" consumerInstance="consumerClass3"/>
                    <consumer name="consumer4" queue="queue4" handler="handlerClassFour::handlerMethodFour"/>
                    <unexpected name="consumer5" queue="queue4"/>
                </config>',
                [
                    "Element 'unexpected': This element is not expected. Expected is ( consumer ).",
                ],
            ],
            'unexpected attribute' => [
                '<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:framework-message-queue:etc/consumer.xsd">
                    <consumer name="consumer1" queue="queue1" handler="handlerClassOne::handlerMethodOne" consumerInstance="consumerClass1" connection="amqp" maxMessages="100"/>
                    <consumer name="consumer2" queue="queue2" handler="handlerClassTwo::handlerMethodTwo" consumerInstance="consumerClass2" connection="db"/>
                    <consumer name="consumer3" queue="queue3" handler="handlerClassThree::handlerMethodThree" consumerInstance="consumerClass3"/>
                    <consumer name="consumer4" queue="queue4" handler="handlerClassFour::handlerMethodFour"/>
                    <consumer name="consumer5" queue="queue4" unexpected=""/>
                </config>',
                [
                    "Element 'consumer', attribute 'unexpected': The attribute 'unexpected' is not allowed.",
                ],
            ],
        ];
    }
}
