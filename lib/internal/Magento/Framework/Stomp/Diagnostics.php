<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Stomp;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\MessageQueue\ConnectionTypeResolver;
use Psr\Log\LoggerInterface;

/**
 * Message Queue Diagnostics Tool
 *
 * Helps diagnose connection and configuration issues for both AMQP and STOMP
 */
class Diagnostics
{
    /**
     * @var DeploymentConfig
     */
    private $deploymentConfig;

    /**
     * @var ConnectionTypeResolver
     */
    private $connectionTypeResolver;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var StompClientFactory
     */
    private $stompClientFactory;

    /**
     * @param DeploymentConfig $deploymentConfig
     * @param ConnectionTypeResolver $connectionTypeResolver
     * @param LoggerInterface $logger
     * @param StompClientFactory $stompClientFactory
     */
    public function __construct(
        DeploymentConfig $deploymentConfig,
        ConnectionTypeResolver $connectionTypeResolver,
        LoggerInterface $logger,
        StompClientFactory $stompClientFactory
    ) {
        $this->deploymentConfig = $deploymentConfig;
        $this->connectionTypeResolver = $connectionTypeResolver;
        $this->logger = $logger;
        $this->stompClientFactory = $stompClientFactory;
    }

    /**
     * Run comprehensive diagnostics
     *
     * @param string|null $connectionName
     * @return array
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function runDiagnostics(?string $connectionName = null): array
    {
        $results = [
            'configuration' => $this->checkConfiguration(),
            'connections' => [],
            'recommendations' => []
        ];

        if ($connectionName) {
            $results['connections'][$connectionName] = $this->testConnection($connectionName);
        } else {
            // Test all configured connections
            $queueConfig = $this->deploymentConfig->getConfigData('queue') ?? [];

            // Test AMQP if configured
            if (isset($queueConfig['amqp'])) {
                $results['connections']['amqp'] = $this->testAmqpConnection($queueConfig['amqp']);
            }

            // Test STOMP if configured
            if (isset($queueConfig['stomp'])) {
                $results['connections']['stomp'] = $this->testStompConnection('stomp');
            }

            // Test additional STOMP connections
            if (isset($queueConfig['connections'])) {
                foreach ($queueConfig['connections'] as $name => $config) {
                    $results['connections'][$name] = $this->testStompConnection($name);
                }
            }
        }

        $results['recommendations'] = $this->generateRecommendations($results);
        return $results;
    }

    /**
     * Check overall configuration
     *
     * @return array
     */
    private function checkConfiguration(): array
    {
        $queueConfig = $this->deploymentConfig->getConfigData('queue') ?? [];

        return [
            'queue_config_exists' => !empty($queueConfig),
            'amqp_configured' => isset($queueConfig['amqp']),
            'stomp_configured' => isset($queueConfig['stomp']),
            'additional_connections' => isset($queueConfig['connections']) ? count($queueConfig['connections']) : 0,
            'config_details' => [
                'amqp' => $queueConfig['amqp'] ?? null,
                'stomp' => isset($queueConfig['stomp']) ? $this->sanitizeConfig($queueConfig['stomp']) : null,
                'connections' => isset($queueConfig['connections']) ?
                    array_map([$this, 'sanitizeConfig'], $queueConfig['connections']) : []
            ]
        ];
    }

    /**
     * Test a specific connection
     *
     * @param string $connectionName
     * @return array
     */
    private function testConnection(string $connectionName): array
    {
        try {
            $connectionType = $this->connectionTypeResolver->getConnectionType($connectionName);

            if ($connectionType === 'stomp') {
                return $this->testStompConnection($connectionName);
            } elseif ($connectionType === 'amqp' || $connectionType === null) {
                $queueConfig = $this->deploymentConfig->getConfigData('queue') ?? [];
                return $this->testAmqpConnection($queueConfig['amqp'] ?? []);
            }

            return [
                'status' => 'error',
                'message' => "Unknown connection type: {$connectionType}",
                'connection_type' => $connectionType
            ];

        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage(),
                'connection_type' => 'unknown'
            ];
        }
    }

    /**
     * Test STOMP connection
     *
     * @param string $connectionName
     * @return array
     */
    private function testStompConnection(string $connectionName): array
    {
        $result = [
            'connection_type' => 'stomp',
            'connection_name' => $connectionName,
            'status' => 'unknown',
            'tests' => []
        ];

        try {
            // Test configuration loading
            $result['tests']['config_load'] = $this->testStompConfigLoad($connectionName);

            // Test network connectivity
            $result['tests']['network'] = $this->testStompNetworkConnectivity($connectionName);

            // Test STOMP client creation
            $result['tests']['client_creation'] = $this->testStompClientCreation($connectionName);

            // Test basic operations
            $result['tests']['basic_operations'] = $this->testStompBasicOperations($connectionName);

            // Determine overall status
            $allPassed = true;
            foreach ($result['tests'] as $test) {
                if ($test['status'] !== 'pass') {
                    $allPassed = false;
                    break;
                }
            }

            $result['status'] = $allPassed ? 'pass' : 'fail';

        } catch (\Exception $e) {
            $result['status'] = 'error';
            $result['error'] = $e->getMessage();
        }

        return $result;
    }

    /**
     * Test AMQP connection
     *
     * @param array $config
     * @return array
     */
    private function testAmqpConnection(array $config): array
    {
        $result = [
            'connection_type' => 'amqp',
            'status' => 'unknown',
            'tests' => []
        ];

        try {
            // Test configuration
            $result['tests']['config'] = [
                'status' => !empty($config) ? 'pass' : 'fail',
                'message' => !empty($config) ? 'AMQP configuration found' : 'AMQP configuration missing'
            ];

            // Test network connectivity (basic check)
            $host = $config['host'] ?? 'localhost';
            $port = $config['port'] ?? 5672;

            $result['tests']['network'] = $this->testNetworkConnectivity($host, $port);

            $result['status'] = $result['tests']['config']['status'] === 'pass' &&
                              $result['tests']['network']['status'] === 'pass' ? 'pass' : 'fail';

        } catch (\Exception $e) {
            $result['status'] = 'error';
            $result['error'] = $e->getMessage();
        }

        return $result;
    }

    /**
     * Test STOMP configuration loading
     *
     * @param string $connectionName
     * @return array
     */
    private function testStompConfigLoad(string $connectionName): array
    {
        try {
            $config = new Config($this->deploymentConfig, $connectionName);
            $host = $config->getValue(Config::HOST);
            $port = $config->getValue(Config::PORT);

            return [
                'status' => 'pass',
                'message' => "Configuration loaded successfully (Host: {$host}, Port: {$port})"
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'fail',
                'message' => "Configuration load failed: " . $e->getMessage()
            ];
        }
    }

    /**
     * Test STOMP network connectivity
     *
     * @param string $connectionName
     * @return array
     */
    private function testStompNetworkConnectivity(string $connectionName): array
    {
        try {
            $config = new Config($this->deploymentConfig, $connectionName);
            $host = $config->getValue(Config::HOST) ?? 'localhost';
            $port = $config->getValue(Config::PORT) ?? 61613;

            return $this->testNetworkConnectivity($host, $port);
        } catch (\Exception $e) {
            return [
                'status' => 'fail',
                'message' => "Network test failed: " . $e->getMessage()
            ];
        }
    }

    /**
     * Test network connectivity to host:port
     *
     * @param string $host
     * @param int $port
     * @return array
     */
    private function testNetworkConnectivity(string $host, int $port): array
    {
        $timeout = 5;
        $socket = @fsockopen($host, $port, $errno, $errstr, $timeout);

        if ($socket) {
            fclose($socket);
            return [
                'status' => 'pass',
                'message' => "Successfully connected to {$host}:{$port}"
            ];
        } else {
            return [
                'status' => 'fail',
                'message' => "Failed to connect to {$host}:{$port} - {$errstr} ({$errno})"
            ];
        }
    }

    /**
     * Test STOMP client creation
     *
     * @param string $connectionName
     * @return array
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    private function testStompClientCreation(string $connectionName): array
    {
        try {
            $config = new Config($this->deploymentConfig, $connectionName);
            $client = $this->stompClientFactory->create();

            return [
                'status' => 'pass',
                'message' => 'STOMP client created successfully'
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'fail',
                'message' => "STOMP client creation failed: " . $e->getMessage()
            ];
        }
    }

    /**
     * Test basic STOMP operations
     *
     * @param string $connectionName
     * @return array
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    private function testStompBasicOperations(string $connectionName): array
    {
        try {
            $config = new Config($this->deploymentConfig, $connectionName);
            $client = $this->stompClientFactory->create();

            // Try to subscribe to a test queue (this doesn't actually create the queue)
            $testQueue = 'diagnostic.test.queue';
            $client->subscribeQueue($testQueue);

            return [
                'status' => 'pass',
                'message' => 'Basic STOMP operations successful'
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'fail',
                'message' => "Basic operations failed: " . $e->getMessage()
            ];
        }
    }

    /**
     * Generate recommendations based on test results
     *
     * @param array $results
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function generateRecommendations(array $results): array
    {
        $recommendations = [];

        // Configuration recommendations
        if (!$results['configuration']['queue_config_exists']) {
            $recommendations[] = "Add queue configuration to env.php";
        }

        if (!$results['configuration']['amqp_configured'] && !$results['configuration']['stomp_configured']) {
            $recommendations[] = "Configure at least one message queue connection (AMQP or STOMP)";
        }

        // Connection-specific recommendations
        foreach ($results['connections'] as $name => $connection) {
            if ($connection['status'] === 'fail' || $connection['status'] === 'error') {
                $recommendations[] = "Fix connection '{$name}' - check configuration and network connectivity";

                if (isset($connection['tests']['network']) && $connection['tests']['network']['status'] === 'fail') {
                    $recommendations[] = "Verify that the message queue service is running for connection '{$name}'";
                }

                if (isset($connection['tests']['config_load'])
                    && $connection['tests']['config_load']['status'] === 'fail') {
                    $recommendations[] = "Check configuration syntax for connection '{$name}' in env.php";
                }
            }
        }

        // Performance recommendations
        if ($results['configuration']['stomp_configured']) {
            $recommendations[] = "Consider tuning STOMP heartbeat and timeout settings for your workload";
            $recommendations[] = "Monitor queue depth and implement alerts for queue buildup";
        }

        return $recommendations;
    }

    /**
     * Sanitize configuration for output (remove passwords)
     *
     * @param array $config
     * @return array
     */
    private function sanitizeConfig(array $config): array
    {
        $sanitized = $config;
        if (isset($sanitized['password'])) {
            $sanitized['password'] = '***HIDDEN***';
        }
        return $sanitized;
    }
}
