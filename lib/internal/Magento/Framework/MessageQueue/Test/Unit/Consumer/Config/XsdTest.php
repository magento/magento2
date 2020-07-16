<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\MessageQueue\Test\Unit\Consumer\Config;

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

    /**
     * @var string
     */
    private $schemaQueueFile;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp(): void
    {
        if (!function_exists('libxml_set_external_entity_loader')) {
            $this->markTestSkipped('Skipped on HHVM. Will be fixed in MAGETWO-45033');
        }
        $urnResolver = new UrnResolver();
        $this->schemaFile = $urnResolver->getRealPath('urn:magento:framework-message-queue:etc/consumer.xsd');
        $this->schemaQueueFile = $urnResolver->getRealPath('urn:magento:framework-message-queue:etc/queue.xsd');
    }

    /**
     * @param string $fixtureXml
     * @param array $expectedErrors
     * @dataProvider exemplarXmlDataProvider
     */
    public function testExemplarXml($fixtureXml, array $expectedErrors)
    {
        $validationState = $this->getMockForAbstractClass(ValidationStateInterface::class);
        $validationState->expects($this->atLeastOnce())
            ->method('isValidationRequired')
            ->willReturn(true);
        $messageFormat = '%message%';
        $dom = new Dom($fixtureXml, $validationState, [], null, null, $messageFormat);
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
                    <consumer name="consumer1" queue="queue1" handler="handlerClass_One1::handlerMethod1" consumerInstance="consumerClass1" connection="amqp" maxMessages="100"/>
                    <consumer name="consumer2" queue="queue2" handler="handlerClassOne2::handler_Method2" consumerInstance="consumerClass2" connection="db"/>
                    <consumer name="consumer3" queue="queue3" handler="handlerClassThree::handlerMethodThree" consumerInstance="consumerClass3"/>
                    <consumer name="consumer4" queue="queue4" handler="handlerClassFour::handlerMethodFour"/>
                    <consumer name="consumer5" queue="queue4"/>
                </config>',
                [
                    "Element 'consumer', attribute 'handler': [facet 'pattern'] The value 'handlerClass_One1::handlerMethod1' is not accepted by the pattern '[a-zA-Z0-9\\\\]+::[a-zA-Z0-9]+'.",
                    "Element 'consumer', attribute 'handler': 'handlerClass_One1::handlerMethod1' is not a valid value of the atomic type 'handlerType'.",
                    "Element 'consumer', attribute 'handler': [facet 'pattern'] The value 'handlerClassOne2::handler_Method2' is not accepted by the pattern '[a-zA-Z0-9\\\\]+::[a-zA-Z0-9]+'.",
                    "Element 'consumer', attribute 'handler': 'handlerClassOne2::handler_Method2' is not a valid value of the atomic type 'handlerType'.",
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
        // @codingStandardsIgnoreEnd
    }

    /**
     * @param string $fixtureXml
     * @param array $expectedErrors
     * @dataProvider exemplarQueueXmlDataProvider
     */
    public function testExemplarQueueXml($fixtureXml, array $expectedErrors)
    {
        $validationState = $this->getMockForAbstractClass(ValidationStateInterface::class);
        $validationState->expects($this->atLeastOnce())
            ->method('isValidationRequired')
            ->willReturn(true);
        $messageFormat = '%message%';
        $dom = new Dom($fixtureXml, $validationState, [], null, null, $messageFormat);
        $actualErrors = [];
        $actualResult = $dom->validate($this->schemaQueueFile, $actualErrors);
        $this->assertEquals(empty($expectedErrors), $actualResult, "Validation result is invalid.");
        $this->assertEquals($expectedErrors, $actualErrors, "Validation errors does not match.");
    }

    /**
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function exemplarQueueXmlDataProvider()
    {
        // @codingStandardsIgnoreStart
        return [
            'valid' => [
                '<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:framework-message-queue:etc/queue.xsd">
                     <broker topic="asd" >
                        <queue name="queue1" consumer="consumer1" handler="handlerClassOne1::handlerMethod1" consumerInstance="consumerClass1" maxMessages="5"/>
                        <queue name="queue2" consumer="consumer2" handler="handlerClassOne2::handlerMethod2" consumerInstance="consumerClass2" maxMessages="5"/>
                    </broker>
                </config>',
                [],
            ],
            'invalid handler format' => [
                '<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:framework-message-queue:etc/queue.xsd">
                    <broker topic="asd" >
                        <queue name="queue1" consumer="consumer1" handler="handlerClass_One1::handlerMethod1" consumerInstance="consumerClass1" maxMessages="5"/>
                        <queue name="queue2" consumer="consumer2" handler="handlerClassOne2::handler_Method2" consumerInstance="consumerClass2" maxMessages="5"/>
                    </broker>
                </config>',
                [
                    "Element 'queue', attribute 'handler': [facet 'pattern'] The value 'handlerClass_One1::handlerMethod1' is not accepted by the pattern '[a-zA-Z0-9\\\\]+::[a-zA-Z0-9]+'.",
                    "Element 'queue', attribute 'handler': 'handlerClass_One1::handlerMethod1' is not a valid value of the atomic type 'handlerType'.",
                    "Element 'queue', attribute 'handler': [facet 'pattern'] The value 'handlerClassOne2::handler_Method2' is not accepted by the pattern '[a-zA-Z0-9\\\\]+::[a-zA-Z0-9]+'.",
                    "Element 'queue', attribute 'handler': 'handlerClassOne2::handler_Method2' is not a valid value of the atomic type 'handlerType'.",
                ],
            ],
            'invalid instance format' => [
                '<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:framework-message-queue:etc/queue.xsd">
                     <broker topic="asd" >
                        <queue name="queue1" consumer="consumer1" handler="handlerClassOne1::handlerMethod1" consumerInstance="consumer_Class1" maxMessages="5"/>
                        <queue name="queue2" consumer="consumer2" handler="handlerClassOne2::handlerMethod2" consumerInstance="consumerClass_2" maxMessages="5"/>
                    </broker>
                </config>',
                [
                    "Element 'queue', attribute 'consumerInstance': [facet 'pattern'] The value 'consumer_Class1' is not accepted by the pattern '[a-zA-Z0-9\\\\]+'.",
                    "Element 'queue', attribute 'consumerInstance': 'consumer_Class1' is not a valid value of the atomic type 'instanceType'.",
                    "Element 'queue', attribute 'consumerInstance': [facet 'pattern'] The value 'consumerClass_2' is not accepted by the pattern '[a-zA-Z0-9\\\\]+'.",
                    "Element 'queue', attribute 'consumerInstance': 'consumerClass_2' is not a valid value of the atomic type 'instanceType'.",
                ],
            ],
            'invalid maxMessages format' => [
                '<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:framework-message-queue:etc/queue.xsd">
                    <broker topic="asd" >
                        <queue name="queue1" consumer="consumer1" handler="handlerClassOne1::handlerMethod1" consumerInstance="consumerClass1" maxMessages="ABC"/>
                        <queue name="queue2" consumer="consumer2" handler="handlerClassOne2::handlerMethod2" consumerInstance="consumerClass2" maxMessages="5"/>
                    </broker>
                </config>',
                [
                    "Element 'queue', attribute 'maxMessages': 'ABC' is not a valid value of the atomic type 'xs:integer'.",
                ],
            ],
            'unexpected element' => [
                '<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:framework-message-queue:etc/queue.xsd">
                    <broker topic="asd" >
                        <queue name="queue1" consumer="consumer1" handler="handlerClassOne1::handlerMethod1" consumerInstance="consumerClass1" maxMessages="2"/>
                        <queue name="queue2" consumer="consumer2" handler="handlerClassOne2::handlerMethod2" consumerInstance="consumerClass2" maxMessages="5"/>
                        <unexpected name="queue2"/>
                    </broker>
                </config>',
                [
                    "Element 'unexpected': This element is not expected. Expected is ( queue ).",
                ],
            ],
            'unexpected attribute' => [
                '<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:framework-message-queue:etc/queue.xsd">
                    <broker topic="asd" >
                        <queue name="queue1" consumer="consumer1" handler="handlerClassOne1::handlerMethod1" consumerInstance="consumerClass1" maxMessages="2"/>
                        <queue name="queue2" consumer="consumer2" handler="handlerClassOne2::handlerMethod2" consumerInstance="consumerClass2" maxMessages="5" unexpected="unexpected"/>
                    </broker>
                </config>',
                [
                    "Element 'queue', attribute 'unexpected': The attribute 'unexpected' is not allowed.",
                ],
            ],
        ];
        // @codingStandardsIgnoreEnd
    }
}
