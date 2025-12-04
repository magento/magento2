<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\NewRelicReporting\Model\NerdGraph;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\HTTP\LaminasClient;
use Magento\Framework\HTTP\LaminasClientFactory;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\NewRelicReporting\Model\Apm\Deployments;
use Magento\NewRelicReporting\Model\Config;
use Magento\NewRelicReporting\Model\NerdGraph\Client;
use Magento\NewRelicReporting\Model\NerdGraph\DeploymentTracker;
use Magento\TestFramework\Fixture\Config as ConfigFixture;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use ReflectionException;

/**
 * Integration test for the complete deployment workflow
 *
 * @magentoAppIsolation enabled
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DeploymentWorkflowTest extends TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var DeploymentTracker
     */
    private $deploymentTracker;

    /**
     * @var Deployments
     */
    private $deployments;

    /**
     * @var Client
     */
    private $nerdGraphClient;

    protected function setUp(): void
    {
        /** @phpstan-ignore-next-line */
        $this->objectManager = Bootstrap::getObjectManager();
        $this->config = $this->objectManager->get(Config::class);
        $this->deploymentTracker = $this->objectManager->get(DeploymentTracker::class);
        $this->deployments = $this->objectManager->get(Deployments::class);
        $this->nerdGraphClient = $this->objectManager->get(Client::class);
    }

    /**
     * Test complete dependency injection setup
     */
    public function testDependencyInjectionSetup()
    {
        $this->assertInstanceOf(Config::class, $this->config);
        $this->assertInstanceOf(DeploymentTracker::class, $this->deploymentTracker);
        $this->assertInstanceOf(Deployments::class, $this->deployments);
        $this->assertInstanceOf(Client::class, $this->nerdGraphClient);
    }

    /**
     * Test config methods work properly in integration environment (default values)
     */
    public function testConfigFixtureDefaultValues()
    {
        // Test default values (from config.xml)
        $this->assertFalse($this->config->isNewRelicEnabled());
        $this->assertEquals('v2_rest', $this->config->getApiMode()); // Default from config.xml
        $this->assertEquals('', $this->config->getEntityGuid()); // No default in config.xml
        $this->assertEquals(0, $this->config->getNewRelicAppId()); // No default in config.xml
        $this->assertEquals('', $this->config->getNewRelicAppName()); // No default in config.xml
    }

    /**
     * Test config methods with enabled settings
     */
    #[ConfigFixture('newrelicreporting/general/enable', '1')]
    #[ConfigFixture('newrelicreporting/general/api_mode', 'nerdgraph')]
    #[ConfigFixture('newrelicreporting/general/entity_guid', 'test-guid-123')]
    public function testConfigIntegrationWithEnabledSettings()
    {
        $this->assertTrue($this->config->isNewRelicEnabled());
        $this->assertEquals('nerdgraph', $this->config->getApiMode());
        $this->assertTrue($this->config->isNerdGraphMode());
        $this->assertEquals('test-guid-123', $this->config->getEntityGuid());
    }

    /**
     * Test deployment workflow when disabled
     */
    #[ConfigFixture('newrelicreporting/general/enable', '0')]
    public function testDeploymentWorkflowWhenDisabled()
    {
        // Should handle gracefully when disabled (specific behavior depends on implementation)
        $this->assertFalse($this->config->isNewRelicEnabled());
    }

    /**
     * Test deployment workflow with missing configuration
     */
    #[ConfigFixture('newrelicreporting/general/enable', '1')]
    #[ConfigFixture('newrelicreporting/general/api_mode', 'nerdgraph')]
    public function testDeploymentWorkflowWithMissingConfiguration()
    {
        $result = $this->deploymentTracker->setDeployment('Test deployment');

        $this->assertFalse($result);
    }

    /**
     * Test v2 REST API mode selection
     */
    #[ConfigFixture('newrelicreporting/general/enable', '1')]
    #[ConfigFixture('newrelicreporting/general/api_mode', 'v2_rest')]
    #[ConfigFixture('newrelicreporting/general/app_id', '12345')]
    #[ConfigFixture('newrelicreporting/general/api', 'encrypted_api_key')]
    public function testV2RestApiModeSelection()
    {
        $this->assertFalse($this->config->isNerdGraphMode());
        $this->assertEquals('v2_rest', $this->config->getApiMode());

        // Test that deployments service uses correct mode
        $this->assertInstanceOf(Deployments::class, $this->deployments);
    }

    /**
     * Test NerdGraph mode selection
     */
    #[ConfigFixture('newrelicreporting/general/enable', '1')]
    #[ConfigFixture('newrelicreporting/general/api_mode', 'nerdgraph')]
    #[ConfigFixture('newrelicreporting/general/entity_guid', 'test-entity-guid')]
    #[ConfigFixture('newrelicreporting/general/nerd_graph_api_url', 'https://api.newrelic.com/graphql')]
    public function testNerdGraphModeSelection()
    {
        $this->assertTrue($this->config->isNerdGraphMode());
        $this->assertEquals('nerdgraph', $this->config->getApiMode());
        $this->assertEquals('test-entity-guid', $this->config->getEntityGuid());
        $this->assertEquals('https://api.newrelic.com/graphql', $this->config->getNerdGraphUrl());
    }

    /**
     * Test Deployments service configuration
     * @throws ReflectionException
     */
    public function testDeploymentsServiceConfiguration()
    {
        // Test that Deployments service is properly configured
        $this->assertInstanceOf(Deployments::class, $this->deployments);

        $reflection = new \ReflectionClass($this->deployments);
        $constructor = $reflection->getConstructor();
        $parameters = $constructor->getParameters();

        $expectedParameters = [
            'config',
            'logger',
            'clientFactory',
            'serializer',
            'deploymentTracker'
        ];

        $this->assertCount(count($expectedParameters), $parameters);

        foreach ($parameters as $index => $parameter) {
            $this->assertEquals($expectedParameters[$index], $parameter->getName());
        }
    }

    /**
     * Test NerdGraph Client configuration
     * @throws ReflectionException
     */
    public function testNerdGraphClientConfiguration()
    {
        $this->assertInstanceOf(Client::class, $this->nerdGraphClient);

        $reflection = new \ReflectionClass($this->nerdGraphClient);
        $constructor = $reflection->getConstructor();
        $parameters = $constructor->getParameters();

        $expectedParameters = [
            'httpClientFactory',
            'serializer',
            'config',
            'logger'
        ];

        $this->assertCount(count($expectedParameters), $parameters);

        foreach ($parameters as $index => $parameter) {
            $this->assertEquals($expectedParameters[$index], $parameter->getName());
        }
    }

    /**
     * Test DeploymentTracker configuration
     * @throws ReflectionException
     */
    public function testDeploymentTrackerConfiguration()
    {
        $this->assertInstanceOf(DeploymentTracker::class, $this->deploymentTracker);

        $reflection = new \ReflectionClass($this->deploymentTracker);
        $constructor = $reflection->getConstructor();
        $parameters = $constructor->getParameters();

        $expectedParameters = [
            'nerdGraphClient',
            'config',
            'logger'
        ];

        $this->assertCount(count($expectedParameters), $parameters);

        foreach ($parameters as $index => $parameter) {
            $this->assertEquals($expectedParameters[$index], $parameter->getName());
        }
    }

    /**
     * Test configuration encryption integration
     */
    #[ConfigFixture('newrelicreporting/general/api', 'NRAK-TEST-API-KEY-123')]
    public function testConfigurationEncryptionIntegration()
    {
        // Config should decrypt it (though in test it might just return as-is)
        $retrievedKey = $this->config->getNewRelicApiKey();
        $this->assertIsString($retrievedKey);
    }

    /**
     * Test HTTP Client Factory integration
     */
    public function testHttpClientFactoryIntegration()
    {
        $httpClientFactory = $this->objectManager->get(LaminasClientFactory::class);
        $this->assertInstanceOf(LaminasClientFactory::class, $httpClientFactory);

        // Should be able to create HTTP client
        $httpClient = $httpClientFactory->create();
        $this->assertInstanceOf(LaminasClient::class, $httpClient);
    }

    /**
     * Test Serializer integration
     */
    public function testSerializerIntegration()
    {
        $serializer = $this->objectManager->get(SerializerInterface::class);
        $this->assertInstanceOf(SerializerInterface::class, $serializer);

        // Test basic serialization
        $testData = ['key' => 'value', 'number' => 123];
        $serialized = $serializer->serialize($testData);
        $unserialized = $serializer->unserialize($serialized);

        $this->assertEquals($testData, $unserialized);
    }

    /**
     * Test Logger integration
     */
    public function testLoggerIntegration()
    {
        $logger = $this->objectManager->get(LoggerInterface::class);
        $this->assertInstanceOf(LoggerInterface::class, $logger);

        // Should be able to log without errors
        $logger->info('Test log message from integration test');
        $this->assertTrue(true);
    }

    /**
     * Test error handling in deployment workflow
     */
    #[ConfigFixture('newrelicreporting/general/enable', '1')]
    #[ConfigFixture('newrelicreporting/general/api_mode', 'nerdgraph')]
    #[ConfigFixture('newrelicreporting/general/entity_guid', 'invalid-guid')]
    #[ConfigFixture('newrelicreporting/general/api', 'invalid_api_key')]
    #[ConfigFixture('newrelicreporting/general/nerd_graph_api_url', 'https://invalid.example.com/graphql')]
    public function testDeploymentWorkflowErrorHandling()
    {
        // This should fail gracefully and return false rather than throwing an exception
        $result = $this->deploymentTracker->setDeployment('Test deployment');
        $this->assertFalse($result);
    }

    /**
     * Test v2_rest mode detection in integration
     */
    #[ConfigFixture('newrelicreporting/general/api_mode', 'v2_rest')]
    public function testV2RestModeDetectionIntegration()
    {
        $this->assertEquals('v2_rest', $this->config->getApiMode());
        $this->assertFalse($this->config->isNerdGraphMode());
    }

    /**
     * Test nerdgraph mode detection in integration
     */
    #[ConfigFixture('newrelicreporting/general/api_mode', 'nerdgraph')]
    public function testNerdGraphModeDetectionIntegration()
    {
        $this->assertEquals('nerdgraph', $this->config->getApiMode());
        $this->assertTrue($this->config->isNerdGraphMode());
    }

    /**
     * Test default mode detection (falls back to config.xml default)
     */
    public function testDefaultModeDetectionIntegration()
    {
        $this->assertEquals('v2_rest', $this->config->getApiMode()); // Falls back to default from config.xml
        $this->assertFalse($this->config->isNerdGraphMode());
    }

    /**
     * Test NerdGraph entity GUID validation
     */
    #[ConfigFixture('newrelicreporting/general/enable', '1')]
    #[ConfigFixture('newrelicreporting/general/api_mode', 'nerdgraph')]
    #[ConfigFixture('newrelicreporting/general/entity_guid', 'MzgwNjUyNnxBUE18QVBQTElDQVRJT058OTE2OTk4')]
    #[ConfigFixture('newrelicreporting/general/api', 'test_api_key')]
    public function testNerdGraphEntityGuidValidation()
    {
        $entityGuid = $this->config->getEntityGuid();
        $this->assertEquals('MzgwNjUyNnxBUE18QVBQTElDQVRJT058OTE2OTk4', $entityGuid);

        // Test deployment with valid entity GUID format
        $result = $this->deploymentTracker->setDeployment(
            'Entity GUID validation test',
            'Testing entity GUID handling',
            'entity-tester'
        );

        $this->assertFalse($result); // Should fail with fake credentials but validate format
    }

    /**
     * Test NerdGraph URL configuration and validation
     */
    #[ConfigFixture('newrelicreporting/general/enable', '1')]
    #[ConfigFixture('newrelicreporting/general/api_mode', 'nerdgraph')]
    #[ConfigFixture('newrelicreporting/general/nerd_graph_api_url', 'https://api.newrelic.com/graphql')]
    public function testNerdGraphUrlConfiguration()
    {
        $nerdGraphUrl = $this->config->getNerdGraphUrl();
        $this->assertEquals('https://api.newrelic.com/graphql', $nerdGraphUrl);

        // URL should be valid HTTPS endpoint
        $this->assertStringStartsWith('https://', $nerdGraphUrl);
        $this->assertStringContainsString('newrelic.com', $nerdGraphUrl);
        $this->assertStringEndsWith('/graphql', $nerdGraphUrl);
    }

    /**
     * Test deployment with all NerdGraph parameters
     */
    #[ConfigFixture('newrelicreporting/general/enable', '1')]
    #[ConfigFixture('newrelicreporting/general/api_mode', 'nerdgraph')]
    #[ConfigFixture('newrelicreporting/general/entity_guid', 'test-entity-guid')]
    #[ConfigFixture('newrelicreporting/general/api', 'fake_api_key')]
    public function testDeploymentWithAllNerdGraphParameters()
    {
        $result = $this->deploymentTracker->setDeployment(
            'Full NerdGraph deployment test',
            'Complete changelog with all features',
            'nerdgraph-user',
            'v3.0.0',
            'abc123def456ghi789',
            'https://github.com/company/repo/releases/tag/v3.0.0',
            'production-us-east-1'
        );

        // Should handle all parameters correctly (fail gracefully with fake credentials)
        $this->assertFalse($result);
    }

    /**
     * Test deployment with minimal NerdGraph parameters
     */
    #[ConfigFixture('newrelicreporting/general/enable', '1')]
    #[ConfigFixture('newrelicreporting/general/api_mode', 'nerdgraph')]
    #[ConfigFixture('newrelicreporting/general/entity_guid', 'test-entity-minimal')]
    #[ConfigFixture('newrelicreporting/general/api', 'minimal_api_key')]
    public function testDeploymentWithMinimalNerdGraphParameters()
    {
        $result = $this->deploymentTracker->setDeployment('Minimal NerdGraph test');

        // Should work with just description
        $this->assertFalse($result); // Fails with fake credentials
    }

    /**
     * Test deployment tracker error handling with various scenarios
     */
    #[ConfigFixture('newrelicreporting/general/enable', '1')]
    #[ConfigFixture('newrelicreporting/general/api_mode', 'nerdgraph')]
    public function testDeploymentTrackerErrorHandlingScenarios()
    {
        // Test with missing entity GUID
        $result = $this->deploymentTracker->setDeployment('Error test 1');
        $this->assertFalse($result);

        // Test with empty description
        $result = $this->deploymentTracker->setDeployment('');
        $this->assertFalse($result);
    }

    /**
     * Test NerdGraph API URL fallback behavior
     */
    #[ConfigFixture('newrelicreporting/general/enable', '1')]
    #[ConfigFixture('newrelicreporting/general/api_mode', 'nerdgraph')]
    #[ConfigFixture('newrelicreporting/general/entity_guid', 'test-fallback-guid')]
    public function testNerdGraphApiUrlFallback()
    {
        // Without explicit URL configuration, should use default
        $nerdGraphUrl = $this->config->getNerdGraphUrl();

        // Should be a string; may be empty if not configured
        $this->assertIsString($nerdGraphUrl);
    }
}
