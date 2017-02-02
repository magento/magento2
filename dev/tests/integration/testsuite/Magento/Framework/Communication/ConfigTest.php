<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Communication;

/**
 * Test of communication configuration reading and parsing.
 *
 * @magentoCache config disabled
 */
class ConfigTest extends \PHPUnit_Framework_TestCase
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
     * @expectedException \LogicException
     * @expectedExceptionMessage Service method specified in the definition of topic "customerDeletedNumbers" is not available. Given "V55\Customer\Api\CustomerRepositoryInterface::delete99"
     */
    public function testGetTopicsNumeric()
    {
        $this->getConfigInstance(__DIR__ . '/_files/valid_communication_numeric.xml')->getTopics();
    }

    // @codingStandardsIgnoreStart
    /**
     * Get topic configuration by its name
     *
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Invalid XML in file 0:
    Element 'topic', attribute 'schema': [facet 'pattern'] The value '55\Customer\Api\CustomerRepositoryInterface::delete' is not accepted by the pattern '[a-zA-Z]+[a-zA-Z0-9\\]+::[a-zA-Z0-9]+'.
    Line: 9

    Element 'topic', attribute 'schema': '55\Customer\Api\CustomerRepositoryInterface::delete' is not a valid value of the atomic type 'schemaType'.
    Line: 9

    Element 'handler', attribute 'type': [facet 'pattern'] The value '55\Customer\Api\CustomerRepositoryInterface' is not accepted by the pattern '[a-zA-Z]+[a-zA-Z0-9\\]+'.
    Line: 10

    Element 'handler', attribute 'type': '55\Customer\Api\CustomerRepositoryInterface' is not a valid value of the atomic type 'serviceTypeType'.
    Line: 10
     *
     */
    // @codingStandardsIgnoreEnd
    public function testGetTopicsNumericInvalid()
    {
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
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Topic "invalidTopic" is not configured.
     */
    public function testGetTopicInvalidName()
    {
        $this->getConfigInstance(__DIR__ . '/_files/valid_communication.xml')->getTopic('invalidTopic');
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Either "request" or "schema" attribute must be specified for topic "customerUpdated"
     */
    public function testGetTopicsExceptionMissingRequest()
    {
        $this->getConfigInstance(__DIR__ . '/_files/communication_missing_request.xml')->getTopics();
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Service method specified in the definition of topic "customerRetrieved" is not
     */
    public function testGetTopicsExceptionNotExistingServiceMethod()
    {
        $this->getConfigInstance(__DIR__ . '/_files/communication_not_existing_service_method.xml')->getTopics();
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Service method specified in the definition of topic "customerRetrieved" is not
     */
    public function testGetTopicsExceptionNotExistingService()
    {
        $this->getConfigInstance(__DIR__ . '/_files/communication_not_existing_service.xml')->getTopics();
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Either "request" or "schema" attribute must be specified for topic "customerRetrieved"
     */
    public function testGetTopicsExceptionNoAttributes()
    {
        $this->getConfigInstance(__DIR__ . '/_files/communication_no_attributes.xml')->getTopics();
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Response schema definition for topic "customerUpdated" should reference existing
     */
    public function testGetTopicsExceptionInvalidResponseSchema()
    {
        $this->getConfigInstance(__DIR__ . '/_files/communication_response_not_existing_service.xml')->getTopics();
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Request schema definition for topic "customerUpdated" should reference existing
     */
    public function testGetTopicsExceptionInvalidRequestSchema()
    {
        $this->getConfigInstance(__DIR__ . '/_files/communication_request_not_existing_service.xml')->getTopics();
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Topic "customerDeleted" is configured for synchronous requests, that is why it must
     */
    public function testGetTopicsExceptionMultipleHandlersSynchronousMode()
    {
        $this->getConfigInstance(__DIR__ . '/_files/communication_multiple_handlers_synchronous_mode.xml')->getTopics();
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Service method specified in the definition of handler "customHandler" for topic "custo
     */
    public function testGetTopicsExceptionInvalidHandler()
    {
        $this->getConfigInstance(__DIR__ . '/_files/communication_not_existing_handler_method.xml')->getTopics();
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Topic name "customerAdded" and attribute "name" = "customerCreated" must be equal
     */
    public function testGetTopicsExceptionInvalidTopicNameInEnv()
    {
        $this->getConfigInstance(
            __DIR__ . '/_files/valid_communication.xml',
            __DIR__ . '/_files/communication_invalid_topic_name.php'
        )->getTopics();
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Topic "customerCreated" must contain data
     */
    public function testGetTopicsExceptionTopicWithoutDataInEnv()
    {
        $this->getConfigInstance(
            __DIR__ . '/_files/valid_communication.xml',
            __DIR__ . '/_files/communication_topic_without_data.php'
        )->getTopics();
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Topic "customerCreated" has missed keys: [response]
     */
    public function testGetTopicsExceptionTopicWithMissedKeysInEnv()
    {
        $this->getConfigInstance(
            __DIR__ . '/_files/valid_communication.xml',
            __DIR__ . '/_files/communication_topic_with_missed_keys.php'
        )->getTopics();
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Topic "customerCreated" has excessive keys: [some_incorrect_key]
     */
    public function testGetTopicsExceptionTopicWithExcessiveKeysInEnv()
    {
        $this->getConfigInstance(
            __DIR__ . '/_files/valid_communication.xml',
            __DIR__ . '/_files/communication_topic_with_excessive_keys.php'
        )->getTopics();
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Topic name "customerDeleted" and attribute "name" = "customerRemoved" must be equal
     */
    public function testGetTopicsExceptionTopicWithNonMatchedNameInEnv()
    {
        $this->getConfigInstance(
            __DIR__ . '/_files/valid_communication.xml',
            __DIR__ . '/_files/communication_with_non_matched_name.php'
        )->getTopics();
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Topic "customerDeleted" is configured for synchronous requests, that is why it must
     */
    public function testGetTopicsExceptionMultipleHandlersSynchronousModeInEnv()
    {
        $this->getConfigInstance(
            __DIR__ . '/_files/valid_communication.xml',
            __DIR__ . '/_files/communication_multiple_handlers_synchronous_mode.php'
        )->getTopics();
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Request schema definition for topic "customerCreated" should reference existing service
     */
    public function testGetTopicsExceptionInvalidRequestSchemaInEnv()
    {
        $this->getConfigInstance(
            __DIR__ . '/_files/valid_communication.xml',
            __DIR__ . '/_files/communication_request_not_existing_service.php'
        )->getTopics();
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Response schema definition for topic "customerCreated" should reference existing type o
     */
    public function testGetTopicsExceptionInvalidResponseSchemaInEnv()
    {
        $this->getConfigInstance(
            __DIR__ . '/_files/valid_communication.xml',
            __DIR__ . '/_files/communication_response_not_existing_service.php'
        )->getTopics();
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Service method specified in the definition of handler "customerCreatedFirst" for topic
     */
    public function testGetTopicsExceptionInvalidMethodInHandlerInEnv()
    {
        $this->getConfigInstance(
            __DIR__ . '/_files/valid_communication.xml',
            __DIR__ . '/_files/communication_not_existing_handler_method.php'
        )->getTopics();
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Disabled handler "default" for topic "customerCreated" cannot be added to the config fi
     */
    public function testGetTopicsExceptionWithDisabledHandlerInEnv()
    {
        $this->getConfigInstance(
            __DIR__ . '/_files/valid_communication.xml',
            __DIR__ . '/_files/communication_with_disabled_handler.php'
        )->getTopics();
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Request schema type for topic "customerCreated" must be "object_interface" or "service_
     */
    public function testGetTopicsExceptionIncorrectRequestSchemaTypeInEnv()
    {
        $this->getConfigInstance(
            __DIR__ . '/_files/valid_communication.xml',
            __DIR__ . '/_files/communication_incorrect_request_schema_type.php'
        )->getTopics();
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage The attribute "is_synchronous" for topic "customerCreated" should have the value of the
     */
    public function testGetTopicsExceptionIsNotBooleanTypeOfIsSynchronousInEnv()
    {
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
