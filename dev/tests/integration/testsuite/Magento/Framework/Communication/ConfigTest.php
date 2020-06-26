<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Communication;

/**
 * Test of communication configuration reading and parsing.
 *
 * @magentoCache config disabled
 */
class ConfigTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Check how valid communication XML config is parsed.
     */
    public function testGetTopics()
    {
        $topics = $this->getConfigInstance(__DIR__ . '/_files/valid_communication.xml')->getTopics();
        $expectedParsedTopics = include __DIR__ . '/_files/valid_communication_expected.php';
        $this->assertEquals($expectedParsedTopics, $topics);
    }

    /**
     * Get topic configuration by its name
     *
     */
    public function testGetTopicsNumeric()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Service method specified in the definition of topic "customerDeletedNumbers" is not av');

        $this->getConfigInstance(__DIR__ . '/_files/valid_communication_numeric.xml')->getTopics();
    }

    // @codingStandardsIgnoreStart
    /**
     * Get topic configuration by its name
     *
    Element 'topic', attribute 'schema': [facet 'pattern'] The value '55\Customer\Api\CustomerRepositoryInterface::delete' is not accepted by the pattern '[a-zA-Z]+[a-zA-Z0-9\\]+::[a-zA-Z0-9]+'.
    Line: 9

    Element 'topic', attribute 'schema': '55\Customer\Api\CustomerRepositoryInterface::delete' is not a valid value of the atomic type 'schemaType'.
    Line: 9

    Element 'handler', attribute 'type': [facet 'pattern'] The value '55\Customer\Api\CustomerRepositoryInterface' is not accepted by the pattern '[a-zA-Z]+[a-zA-Z0-9\\]+'.
    Line: 10

    Element 'handler', attribute 'type': '55\Customer\Api\CustomerRepositoryInterface' is not a valid value of the atomic type 'serviceTypeType'.
    Line: 10
    Verify the XML and try again.
     *
     */
    // @codingStandardsIgnoreEnd
    public function testGetTopicsNumericInvalid()
    {
        $this->expectException(\Magento\Framework\Exception\LocalizedException::class);
        $this->expectExceptionMessage('The XML in file "0" is invalid:');

        $this->getConfigInstance(__DIR__ . '/_files/invalid_communication_numeric.xml')->getTopics();
    }

    /**
     * Get topic configuration by its name
     */
    public function testGetTopic()
    {
        $topics = $this->getConfigInstance(__DIR__ . '/_files/valid_communication.xml')->getTopic('customerCreated');
        $expectedParsedTopics = include __DIR__ . '/_files/valid_communication_expected.php';
        $this->assertEquals($expectedParsedTopics['customerCreated'], $topics);
    }

    /**
     * Get topic configuration by its name
     *
     */
    public function testGetTopicInvalidName()
    {
        $this->expectException(\Magento\Framework\Exception\LocalizedException::class);
        $this->expectExceptionMessage('Topic "invalidTopic" is not configured.');

        $this->getConfigInstance(__DIR__ . '/_files/valid_communication.xml')->getTopic('invalidTopic');
    }

    /**
     */
    public function testGetTopicsExceptionMissingRequest()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Either "request" or "schema" attribute must be specified for topic "customerUpdated"');

        $this->getConfigInstance(__DIR__ . '/_files/communication_missing_request.xml')->getTopics();
    }

    /**
     */
    public function testGetTopicsExceptionNotExistingServiceMethod()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Service method specified in the definition of topic "customerRetrieved" is not');

        $this->getConfigInstance(__DIR__ . '/_files/communication_not_existing_service_method.xml')->getTopics();
    }

    /**
     */
    public function testGetTopicsExceptionNotExistingService()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Service method specified in the definition of topic "customerRetrieved" is not');

        $this->getConfigInstance(__DIR__ . '/_files/communication_not_existing_service.xml')->getTopics();
    }

    /**
     */
    public function testGetTopicsExceptionNoAttributes()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Either "request" or "schema" attribute must be specified for topic "customerRetrieved"');

        $this->getConfigInstance(__DIR__ . '/_files/communication_no_attributes.xml')->getTopics();
    }

    /**
     */
    public function testGetTopicsExceptionInvalidResponseSchema()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Response schema definition for topic "customerUpdated" should reference existing');

        $this->getConfigInstance(__DIR__ . '/_files/communication_response_not_existing_service.xml')->getTopics();
    }

    /**
     */
    public function testGetTopicsExceptionInvalidRequestSchema()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Request schema definition for topic "customerUpdated" should reference existing');

        $this->getConfigInstance(__DIR__ . '/_files/communication_request_not_existing_service.xml')->getTopics();
    }

    /**
     */
    public function testGetTopicsExceptionMultipleHandlersSynchronousMode()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Topic "customerDeleted" is configured for synchronous requests, that is why it must');

        $this->getConfigInstance(__DIR__ . '/_files/communication_multiple_handlers_synchronous_mode.xml')->getTopics();
    }

    /**
     */
    public function testGetTopicsExceptionInvalidHandler()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Service method specified in the definition of handler "customHandler" for topic "custo');

        $this->getConfigInstance(__DIR__ . '/_files/communication_not_existing_handler_method.xml')->getTopics();
    }

    /**
     */
    public function testGetTopicsExceptionInvalidTopicNameInEnv()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Topic name "customerAdded" and attribute "name" = "customerCreated" must be equal');

        $this->getConfigInstance(
            __DIR__ . '/_files/valid_communication.xml',
            __DIR__ . '/_files/communication_invalid_topic_name.php'
        )->getTopics();
    }

    /**
     */
    public function testGetTopicsExceptionTopicWithoutDataInEnv()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Topic "customerCreated" must contain data');

        $this->getConfigInstance(
            __DIR__ . '/_files/valid_communication.xml',
            __DIR__ . '/_files/communication_topic_without_data.php'
        )->getTopics();
    }

    /**
     */
    public function testGetTopicsExceptionTopicWithMissedKeysInEnv()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Topic "customerCreated" has missed keys: [response]');

        $this->getConfigInstance(
            __DIR__ . '/_files/valid_communication.xml',
            __DIR__ . '/_files/communication_topic_with_missed_keys.php'
        )->getTopics();
    }

    /**
     */
    public function testGetTopicsExceptionTopicWithExcessiveKeysInEnv()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Topic "customerCreated" has excessive keys: [some_incorrect_key]');

        $this->getConfigInstance(
            __DIR__ . '/_files/valid_communication.xml',
            __DIR__ . '/_files/communication_topic_with_excessive_keys.php'
        )->getTopics();
    }

    /**
     */
    public function testGetTopicsExceptionTopicWithNonMatchedNameInEnv()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Topic name "customerDeleted" and attribute "name" = "customerRemoved" must be equal');

        $this->getConfigInstance(
            __DIR__ . '/_files/valid_communication.xml',
            __DIR__ . '/_files/communication_with_non_matched_name.php'
        )->getTopics();
    }

    /**
     */
    public function testGetTopicsExceptionMultipleHandlersSynchronousModeInEnv()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Topic "customerDeleted" is configured for synchronous requests, that is why it must');

        $this->getConfigInstance(
            __DIR__ . '/_files/valid_communication.xml',
            __DIR__ . '/_files/communication_multiple_handlers_synchronous_mode.php'
        )->getTopics();
    }

    /**
     */
    public function testGetTopicsExceptionInvalidRequestSchemaInEnv()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Request schema definition for topic "customerCreated" should reference existing service');

        $this->getConfigInstance(
            __DIR__ . '/_files/valid_communication.xml',
            __DIR__ . '/_files/communication_request_not_existing_service.php'
        )->getTopics();
    }

    /**
     */
    public function testGetTopicsExceptionInvalidResponseSchemaInEnv()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Response schema definition for topic "customerCreated" should reference existing type o');

        $this->getConfigInstance(
            __DIR__ . '/_files/valid_communication.xml',
            __DIR__ . '/_files/communication_response_not_existing_service.php'
        )->getTopics();
    }

    /**
     */
    public function testGetTopicsExceptionInvalidMethodInHandlerInEnv()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Service method specified in the definition of handler "customerCreatedFirst" for topic');

        $this->getConfigInstance(
            __DIR__ . '/_files/valid_communication.xml',
            __DIR__ . '/_files/communication_not_existing_handler_method.php'
        )->getTopics();
    }

    /**
     */
    public function testGetTopicsExceptionWithDisabledHandlerInEnv()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Disabled handler "default" for topic "customerCreated" cannot be added to the config fi');

        $this->getConfigInstance(
            __DIR__ . '/_files/valid_communication.xml',
            __DIR__ . '/_files/communication_with_disabled_handler.php'
        )->getTopics();
    }

    /**
     */
    public function testGetTopicsExceptionIncorrectRequestSchemaTypeInEnv()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Request schema type for topic "customerCreated" must be "object_interface" or "service_');

        $this->getConfigInstance(
            __DIR__ . '/_files/valid_communication.xml',
            __DIR__ . '/_files/communication_incorrect_request_schema_type.php'
        )->getTopics();
    }

    /**
     */
    public function testGetTopicsExceptionIsNotBooleanTypeOfIsSynchronousInEnv()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('The attribute "is_synchronous" for topic "customerCreated" should have the value of the');

        $this->getConfigInstance(
            __DIR__ . '/_files/valid_communication.xml',
            __DIR__ . '/_files/communication_is_synchronous_is_not_boolean.php'
        )->getTopics();
    }

    /**
     * Create config instance initialized with configuration from $configFilePath
     *
     * @param string $configFilePath
     * @param string|null $envConfigFilePath
     * @return \Magento\Framework\Communication\ConfigInterface
     */
    protected function getConfigInstance($configFilePath, $envConfigFilePath = null)
    {
        $fileResolver = $this->getMockForAbstractClass(\Magento\Framework\Config\FileResolverInterface::class);
        $fileResolver->expects($this->any())
            ->method('get')
            ->willReturn([file_get_contents($configFilePath)]);
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $xmlReader = $objectManager->create(
            \Magento\Framework\Communication\Config\Reader\XmlReader::class,
            ['fileResolver' => $fileResolver]
        );
        $deploymentConfigReader = $this->getMockBuilder(\Magento\Framework\App\DeploymentConfig\Reader::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $envConfigData = include $envConfigFilePath ?: __DIR__ . '/_files/valid_communication_input.php';
        $deploymentConfigReader->expects($this->any())->method('load')->willReturn($envConfigData);
        $deploymentConfig = $objectManager->create(
            \Magento\Framework\App\DeploymentConfig::class,
            ['reader' => $deploymentConfigReader]
        );
        $methodsMap = $objectManager->create(\Magento\Framework\Reflection\MethodsMap::class);
        $envReader = $objectManager->create(
            \Magento\Framework\Communication\Config\Reader\EnvReader::class,
            [
                'deploymentConfig' => $deploymentConfig,
                'methodsMap' => $methodsMap
            ]
        );
        $readersConfig = [
            'xmlReader' => ['reader' => $xmlReader, 'sortOrder' => 10],
            'envReader' => ['reader' => $envReader, 'sortOrder' => 20]
        ];
        /** @var \Magento\Framework\Communication\Config\CompositeReader $reader */
        $reader = $objectManager->create(
            \Magento\Framework\Communication\Config\CompositeReader::class,
            ['readers' => $readersConfig]
        );
        /** @var \Magento\Framework\Communication\Config $config */
        $configData = $objectManager->create(
            \Magento\Framework\Communication\Config\Data::class,
            [
                'reader' => $reader
            ]
        );
        return $objectManager->create(
            \Magento\Framework\Communication\ConfigInterface::class,
            ['configData' => $configData]
        );
    }
}
