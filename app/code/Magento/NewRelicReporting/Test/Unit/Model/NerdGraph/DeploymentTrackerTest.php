<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\NewRelicReporting\Test\Unit\Model\NerdGraph;

use Magento\Framework\Exception\LocalizedException;
use Magento\NewRelicReporting\Model\Config;
use Magento\NewRelicReporting\Model\NerdGraph\Client;
use Magento\NewRelicReporting\Model\NerdGraph\DeploymentTracker;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Test for NerdGraph DeploymentTracker
 */
class DeploymentTrackerTest extends TestCase
{
    /**
     * @var Client|MockObject
     */
    private $nerdGraphClientMock;

    /**
     * @var Config|MockObject
     */
    private $configMock;

    /**
     * @var LoggerInterface|MockObject
     */
    private $loggerMock;

    /**
     * @var DeploymentTracker
     */
    private $deploymentTracker;

    /**
     * Setup mocks and DeploymentTracker instance
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->nerdGraphClientMock = $this->createMock(Client::class);
        $this->configMock = $this->createMock(Config::class);
        $this->loggerMock = $this->createMock(LoggerInterface::class);

        $this->deploymentTracker = new DeploymentTracker(
            $this->nerdGraphClientMock,
            $this->configMock,
            $this->loggerMock
        );
    }

    /**
     * Test successful deployment creation
     * @throws LocalizedException
     */
    public function testCreateDeploymentSuccess()
    {
        $description = 'Test deployment';
        $changelog = 'Bug fixes';
        $user = 'deploy-user';
        $version = 'v1.0.0';
        $commit = 'abc123';
        $deepLink = 'https://github.com/test/commit/abc123';
        $groupId = 'production';
        $entityGuid = 'TEST_ENTITY_GUID';

        $this->configMock->expects($this->once())
            ->method('getEntityGuid')
            ->willReturn($entityGuid);

        $expectedMutationResponse = [
            'data' => [
                'changeTrackingCreateDeployment' => [
                    'deploymentId' => '12345678-1234-1234-1234-123456789012',
                    'entityGuid' => $entityGuid,
                    'timestamp' => 1234567890000,
                    'version' => $version,
                    'description' => $description,
                    'user' => $user,
                    'change_log' => $changelog,
                    'commit' => $commit,
                    'deepLink' => $deepLink,
                    'groupId' => $groupId
                ]
            ]
        ];

        $this->nerdGraphClientMock->expects($this->once())
            ->method('query')
            ->with(
                $this->stringContains('mutation createDeployment'),
                $this->callback(function ($variables) use (
                    $entityGuid,
                    $description,
                    $version,
                    $changelog,
                    $user,
                    $commit,
                    $deepLink,
                    $groupId
                ) {
                    return isset($variables['deployment']) &&
                           $variables['deployment']['entityGuid'] === $entityGuid &&
                           $variables['deployment']['description'] === $description &&
                           $variables['deployment']['version'] === $version &&
                           $variables['deployment']['changelog'] === $changelog &&
                           $variables['deployment']['user'] === $user &&
                           $variables['deployment']['commit'] === $commit &&
                           $variables['deployment']['deepLink'] === $deepLink &&
                           $variables['deployment']['groupId'] === $groupId;
                })
            )
            ->willReturn($expectedMutationResponse);

        $this->loggerMock->expects($this->once())
            ->method('info')
            ->with('NerdGraph deployment created successfully');

        $result = $this->deploymentTracker->setDeployment(
            $description,
            $changelog,
            $user,
            $version,
            $commit,
            $deepLink,
            $groupId
        );

        $this->assertIsArray($result);
        $this->assertEquals('12345678-1234-1234-1234-123456789012', $result['deploymentId']);
        $this->assertEquals($entityGuid, $result['entityGuid']);
        $this->assertEquals($version, $result['version']);
        $this->assertEquals($description, $result['description']);
        $this->assertEquals($changelog, $result['changelog']);
        $this->assertEquals($user, $result['user']);
        $this->assertEquals($commit, $result['commit']);
        $this->assertEquals($deepLink, $result['deepLink']);
        $this->assertEquals($groupId, $result['groupId']);
        $this->assertIsInt($result['timestamp']);
        $this->assertGreaterThan(0, $result['timestamp']);
    }

    /**
     * Test deployment creation with minimal parameters
     */
    public function testCreateDeploymentWithMinimalParameters()
    {
        $description = 'Test deployment';
        $entityGuid = 'TEST_ENTITY_GUID';

        $this->configMock->expects($this->once())
            ->method('getEntityGuid')
            ->willReturn($entityGuid);

        $expectedMutationResponse = [
            'data' => [
                'changeTrackingCreateDeployment' => [
                    'deploymentId' => '12345678-1234-1234-1234-123456789012',
                    'entityGuid' => $entityGuid,
                    'timestamp' => 1234567890000,
                    'description' => $description
                ]
            ]
        ];

        $this->nerdGraphClientMock->expects($this->once())
            ->method('query')
            ->with(
                $this->stringContains('mutation createDeployment'),
                $this->callback(function ($variables) use ($entityGuid, $description) {
                    return isset($variables['deployment']) &&
                           $variables['deployment']['entityGuid'] === $entityGuid &&
                           $variables['deployment']['description'] === $description &&
                           !isset($variables['deployment']['changelog']) &&
                           !isset($variables['deployment']['user']);
                })
            )
            ->willReturn($expectedMutationResponse);

        $this->loggerMock->expects($this->once())
            ->method('info')
            ->with('NerdGraph deployment created successfully');

        $result = $this->deploymentTracker->setDeployment($description);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('deploymentId', $result);
    }

    /**
     * Test deployment creation with entity GUID fallback from app name
     */
    public function testCreateDeploymentEntityGuidFallbackFromAppName()
    {
        $description = 'Test deployment';
        $appName = 'My Application';
        $resolvedGuid = 'RESOLVED_ENTITY_GUID';

        $this->configMock->expects($this->once())
            ->method('getEntityGuid')
            ->willReturn('');

        $this->configMock->expects($this->once())
            ->method('getNewRelicAppId')
            ->willReturn(null);

        $this->configMock->expects($this->once())
            ->method('getNewRelicAppName')
            ->willReturn($appName);

        $this->nerdGraphClientMock->expects($this->once())
            ->method('getEntityGuidFromApplication')
            ->with($appName, "")
            ->willReturn($resolvedGuid);

        $expectedMutationResponse = [
            'data' => [
                'changeTrackingCreateDeployment' => [
                    'deploymentId' => '12345678-1234-1234-1234-123456789012',
                    'entityGuid' => $resolvedGuid,
                    'timestamp' => 1234567890000,
                    'description' => $description
                ]
            ]
        ];

        $this->nerdGraphClientMock->expects($this->once())
            ->method('query')
            ->with(
                $this->stringContains('mutation createDeployment'),
                $this->callback(function ($variables) use ($resolvedGuid, $description) {
                    return isset($variables['deployment']) &&
                           $variables['deployment']['entityGuid'] === $resolvedGuid &&
                           $variables['deployment']['description'] === $description;
                })
            )
            ->willReturn($expectedMutationResponse);

        $this->loggerMock->expects($this->exactly(2))
            ->method('info');

        $result = $this->deploymentTracker->setDeployment($description);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('deploymentId', $result);
    }

    /**
     * Test deployment creation with entity GUID fallback from app ID
     */
    public function testCreateDeploymentEntityGuidFallbackFromAppId()
    {
        $description = 'Test deployment';
        $appId = '123456789';
        $resolvedGuid = 'RESOLVED_ENTITY_GUID';

        $this->configMock->expects($this->once())
            ->method('getEntityGuid')
            ->willReturn('');

        $this->configMock->expects($this->once())
            ->method('getNewRelicAppId')
            ->willReturn($appId);

        $this->configMock->expects($this->once())
            ->method('getNewRelicAppName')
            ->willReturn('');

        $this->nerdGraphClientMock->expects($this->once())
            ->method('getEntityGuidFromApplication')
            ->with('', $appId)
            ->willReturn($resolvedGuid);

        $expectedMutationResponse = [
            'data' => [
                'changeTrackingCreateDeployment' => [
                    'deploymentId' => '12345678-1234-1234-1234-123456789012',
                    'entityGuid' => $resolvedGuid,
                    'timestamp' => 1234567890000,
                    'description' => $description
                ]
            ]
        ];

        $this->nerdGraphClientMock->expects($this->once())
            ->method('query')
            ->with(
                $this->stringContains('mutation createDeployment'),
                $this->callback(function ($variables) use ($resolvedGuid, $description) {
                    return isset($variables['deployment']) &&
                           $variables['deployment']['entityGuid'] === $resolvedGuid &&
                           $variables['deployment']['description'] === $description;
                })
            )
            ->willReturn($expectedMutationResponse);

        $this->loggerMock->expects($this->exactly(2))
            ->method('info');

        $result = $this->deploymentTracker->setDeployment($description);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('deploymentId', $result);
    }

    /**
     * Test deployment creation failure due to missing entity GUID
     */
    public function testCreateDeploymentFailureMissingEntityGuid()
    {
        $description = 'Test deployment';

        $this->configMock->expects($this->once())
            ->method('getEntityGuid')
            ->willReturn('');

        $this->configMock->expects($this->once())
            ->method('getNewRelicAppId')
            ->willReturn(null);

        $this->configMock->expects($this->once())
            ->method('getNewRelicAppName')
            ->willReturn('');

        $this->loggerMock->expects($this->atLeastOnce())
            ->method('error');

        $result = $this->deploymentTracker->setDeployment($description);

        $this->assertFalse($result);
    }

    /**
     * Test deployment creation failure due to entity GUID resolution failure
     */
    public function testCreateDeploymentFailureEntityGuidResolutionFailed()
    {
        $description = 'Test deployment';
        $appName = 'My Application';

        $this->configMock->expects($this->once())
            ->method('getEntityGuid')
            ->willReturn('');

        $this->configMock->expects($this->once())
            ->method('getNewRelicAppId')
            ->willReturn(null);

        $this->configMock->expects($this->once())
            ->method('getNewRelicAppName')
            ->willReturn($appName);

        $this->nerdGraphClientMock->expects($this->once())
            ->method('getEntityGuidFromApplication')
            ->with($appName, "")
            ->willReturn(null);

        $this->loggerMock->expects($this->atLeastOnce())->method('error');

        $result = $this->deploymentTracker->setDeployment($description);

        $this->assertFalse($result);
    }

    /**
     * Test deployment creation with GraphQL errors
     */
    public function testCreateDeploymentWithGraphQLErrors()
    {
        $description = 'Test deployment';
        $entityGuid = 'TEST_ENTITY_GUID';

        $this->configMock->expects($this->once())
            ->method('getEntityGuid')
            ->willReturn($entityGuid);

        $errorResponse = [
            'errors' => [
                ['message' => 'Invalid entity GUID']
            ],
            'data' => null
        ];

        $this->nerdGraphClientMock->expects($this->once())
            ->method('query')
            ->willReturn($errorResponse);

        $this->loggerMock->expects($this->atLeastOnce())->method('error');

        $result = $this->deploymentTracker->setDeployment($description);

        $this->assertFalse($result);
    }

    /**
     * Test deployment creation with empty response
     */
    public function testCreateDeploymentWithEmptyResponse()
    {
        $description = 'Test deployment';
        $entityGuid = 'TEST_ENTITY_GUID';

        $this->configMock->expects($this->once())
            ->method('getEntityGuid')
            ->willReturn($entityGuid);

        $this->nerdGraphClientMock->expects($this->once())
            ->method('query')
            ->willReturn([]);

        $this->loggerMock->expects($this->atLeastOnce())->method('error');

        $result = $this->deploymentTracker->setDeployment($description);

        $this->assertFalse($result);
    }

    /**
     * Test deployment creation with malformed response
     */
    public function testCreateDeploymentWithMalformedResponse()
    {
        $description = 'Test deployment';
        $entityGuid = 'TEST_ENTITY_GUID';

        $this->configMock->expects($this->once())
            ->method('getEntityGuid')
            ->willReturn($entityGuid);

        $malformedResponse = [
            'data' => [
                'changeTrackingCreateDeployment' => null
            ]
        ];

        $this->nerdGraphClientMock->expects($this->once())
            ->method('query')
            ->willReturn($malformedResponse);

        $this->loggerMock->expects($this->atLeastOnce())->method('error');

        $result = $this->deploymentTracker->setDeployment($description);

        $this->assertFalse($result);
    }

    /**
     * Test deployment creation with network failure
     */
    public function testCreateDeploymentWithNetworkFailure()
    {
        $description = 'Test deployment';
        $entityGuid = 'TEST_ENTITY_GUID';

        $this->configMock->expects($this->once())
            ->method('getEntityGuid')
            ->willReturn($entityGuid);

        $this->nerdGraphClientMock->expects($this->once())
            ->method('query')
            ->willThrowException(new \Exception('Network error'));

        $this->loggerMock->expects($this->atLeastOnce())->method('error');

        $result = $this->deploymentTracker->setDeployment($description);

        $this->assertFalse($result);
    }

    /**
     * Test deployment creation with special characters in description
     */
    public function testCreateDeploymentWithSpecialCharacters()
    {
        $description = 'Test deployment with "quotes" and \backslashes & <HTML>';
        $entityGuid = 'TEST_ENTITY_GUID';

        $this->configMock->expects($this->once())
            ->method('getEntityGuid')
            ->willReturn($entityGuid);

        $expectedMutationResponse = [
            'data' => [
                'changeTrackingCreateDeployment' => [
                    'deploymentId' => '12345678-1234-1234-1234-123456789012',
                    'entityGuid' => $entityGuid,
                    'timestamp' => 1234567890000,
                    'description' => $description
                ]
            ]
        ];

        $this->nerdGraphClientMock->expects($this->once())
            ->method('query')
            ->with(
                $this->stringContains('mutation createDeployment'),
                $this->callback(function ($variables) use ($entityGuid, $description) {
                    return isset($variables['deployment']) &&
                           $variables['deployment']['entityGuid'] === $entityGuid &&
                           $variables['deployment']['description'] === $description;
                })
            )
            ->willReturn($expectedMutationResponse);

        $this->loggerMock->expects($this->once())
            ->method('info')
            ->with('NerdGraph deployment created successfully');

        $result = $this->deploymentTracker->setDeployment($description);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('deploymentId', $result);
    }

    /**
     * Test deployment creation with very long parameters
     */
    public function testCreateDeploymentWithLongParameters()
    {
        $description = str_repeat('A', 1000);
        $changelog = str_repeat('B', 2000);
        $user = str_repeat('C', 100);
        $entityGuid = 'TEST_ENTITY_GUID';

        $this->configMock->expects($this->once())
            ->method('getEntityGuid')
            ->willReturn($entityGuid);

        $expectedMutationResponse = [
            'data' => [
                'changeTrackingCreateDeployment' => [
                    'deploymentId' => '12345678-1234-1234-1234-123456789012',
                    'entityGuid' => $entityGuid,
                    'timestamp' => 1234567890000,
                    'description' => $description,
                    'changelog' => $changelog,
                    'user' => $user
                ]
            ]
        ];

        $this->nerdGraphClientMock->expects($this->once())
            ->method('query')
            ->willReturn($expectedMutationResponse);

        $this->loggerMock->expects($this->once())
            ->method('info')
            ->with('NerdGraph deployment created successfully');

        $result = $this->deploymentTracker->setDeployment($description, $changelog, $user);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('deploymentId', $result);
    }

    /**
     * Test that GraphQL mutation includes all provided fields
     */
    public function testCreateDeploymentMutationStructure()
    {
        $description = 'Test deployment';
        $changelog = 'Bug fixes';
        $user = 'deploy-user';
        $version = 'v1.0.0';
        $commit = 'abc123';
        $deepLink = 'https://github.com/test/commit/abc123';
        $groupId = 'production';
        $entityGuid = 'TEST_ENTITY_GUID';

        $this->configMock->expects($this->once())
            ->method('getEntityGuid')
            ->willReturn($entityGuid);

        $expectedMutationResponse = [
            'data' => [
                'changeTrackingCreateDeployment' => [
                    'deploymentId' => '12345678-1234-1234-1234-123456789012',
                    'entityGuid' => $entityGuid
                ]
            ]
        ];

        $this->nerdGraphClientMock->expects($this->once())
            ->method('query')
            ->with(
                $this->stringContains('mutation createDeployment'),
                $this->callback(function ($variables) use (
                    $entityGuid,
                    $description,
                    $changelog,
                    $user,
                    $version,
                    $commit,
                    $deepLink,
                    $groupId
                ) {
                    return isset($variables['deployment']) &&
                           $variables['deployment']['entityGuid'] === $entityGuid &&
                           $variables['deployment']['description'] === $description &&
                           $variables['deployment']['changelog'] === $changelog &&
                           $variables['deployment']['user'] === $user &&
                           $variables['deployment']['version'] === $version &&
                           $variables['deployment']['commit'] === $commit &&
                           $variables['deployment']['deepLink'] === $deepLink &&
                           $variables['deployment']['groupId'] === $groupId;
                })
            )
            ->willReturn($expectedMutationResponse);

        $this->loggerMock->expects($this->once())
            ->method('info')
            ->with('NerdGraph deployment created successfully');

        $result = $this->deploymentTracker->setDeployment(
            $description,
            $changelog,
            $user,
            $version,
            $commit,
            $deepLink,
            $groupId
        );

        $this->assertIsArray($result);
        $this->assertArrayHasKey('deploymentId', $result);
    }
}
