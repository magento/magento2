<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\NewRelicReporting\Model\NerdGraph;

use Exception;
use Magento\NewRelicReporting\Model\Config;
use Psr\Log\LoggerInterface;

/**
 * NerdGraph-based deployment tracking service
 */
class DeploymentTracker
{
    /**
     * @var Client
     */
    private Client $nerdGraphClient;

    /**
     * @var Config
     */
    private Config $config;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * Constructor
     *
     * @param Client $nerdGraphClient
     * @param Config $config
     * @param LoggerInterface $logger
     */
    public function __construct(
        Client $nerdGraphClient,
        Config $config,
        LoggerInterface $logger
    ) {
        $this->nerdGraphClient = $nerdGraphClient;
        $this->config = $config;
        $this->logger = $logger;
    }

    /**
     * Create a deployment marker via NerdGraph
     *
     * @param string $description Deployment description
     * @param string|null $changelog commit message
     * @param string|null $user User who performed the deployment
     * @param string|null $version Version or revision
     * @param string|null $commit Git commit hash
     * @param string|null $deepLink Deep link to deployment details
     * @param string|null $groupId Group ID for organizing deployments
     * @return array|false Deployment data on success, false on failure
     */
    public function setDeployment(
        string $description,
        ?string $changelog = null,
        ?string $user = null,
        ?string $version = null,
        ?string $commit = null,
        ?string $deepLink = null,
        ?string $groupId = null
    ): false|array {
        try {
            $entityGuid = $this->getEntityGuid();
            if (!$entityGuid) {
                $this->logger->error('Cannot create NerdGraph deployment: Entity GUID not found');
                return false;
            }

            $variables = $this->buildDeploymentVariables(
                $entityGuid,
                $description,
                $version,
                $changelog,
                $user,
                $commit,
                $deepLink,
                $groupId
            );

            $response = $this->nerdGraphClient->query($this->getDeploymentMutation(), $variables);

            return $this->processDeploymentResponse(
                $response,
                $variables,
                $description,
                $changelog,
                $user,
                $commit,
                $deepLink,
                $groupId
            );
        } catch (Exception $e) {
            $this->logger->error('NerdGraph deployment creation failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get entity GUID for the configured application
     *
     * @return string|null Entity GUID or null if not found
     */
    private function getEntityGuid(): ?string
    {
        // First try to get configured Entity GUID directly
        $configuredEntityGuid = $this->config->getEntityGuid();
        if (!empty($configuredEntityGuid)) {
            return $configuredEntityGuid;
        }

        // Fallback: Try to resolve Entity GUID from application name/ID
        $appId = $this->config->getNewRelicAppId();
        $appName = $this->config->getNewRelicAppName();

        if (!$appId && !$appName) {
            $this->logger->error('No Entity GUID, New Relic application ID, or name configured');
            return null;
        }

        $this->logger->info('Entity GUID not configured, attempting to resolve from application name/ID');
        return $this->nerdGraphClient->getEntityGuidFromApplication($appName, (string)$appId);
    }

    /**
     * Generate a version string if none provided
     *
     * @return string Generated version string
     */
    private function generateVersion(): string
    {
        return date('Y-m-d_H-i-s') . '_' . substr(hash('sha256', (string)time()), 0, 8);
    }

    /**
     * Get the GraphQL mutation for creating deployment
     *
     * @return string
     */
    private function getDeploymentMutation(): string
    {
        return '
            mutation CreateDeployment($deployment: ChangeTrackingDeploymentInput!) {
                changeTrackingCreateDeployment(deployment: $deployment) {
                    deploymentId
                    entityGuid
                }
            }
        ';
    }

    /**
     * Build deployment variables array
     *
     * @param string $entityGuid
     * @param string $description
     * @param string|null $version
     * @param string|null $changelog
     * @param string|null $user
     * @param string|null $commit
     * @param string|null $deepLink
     * @param string|null $groupId
     * @return array
     */
    private function buildDeploymentVariables(
        string $entityGuid,
        string $description,
        ?string $version,
        ?string $changelog,
        ?string $user,
        ?string $commit,
        ?string $deepLink,
        ?string $groupId
    ): array {
        $variables = [
            'deployment' => [
                'entityGuid' => $entityGuid,
                'version' => $version ?: $this->generateVersion(),
                'description' => $description,
                'deploymentType' => 'BASIC',
                'timestamp' => time() * 1000 // NerdGraph expects milliseconds
            ]
        ];

        $this->addOptionalFields($variables, $changelog, $user, $commit, $deepLink, $groupId);

        return $variables;
    }

    /**
     * Add optional fields to deployment variables
     *
     * @param array $variables
     * @param string|null $changelog
     * @param string|null $user
     * @param string|null $commit
     * @param string|null $deepLink
     * @param string|null $groupId
     * @return void
     */
    private function addOptionalFields(
        array &$variables,
        ?string $changelog,
        ?string $user,
        ?string $commit,
        ?string $deepLink,
        ?string $groupId
    ): void {
        if ($changelog) {
            $variables['deployment']['changelog'] = $changelog;
        }

        if ($user) {
            $variables['deployment']['user'] = $user;
        }

        if ($commit) {
            $variables['deployment']['commit'] = $commit;
        }

        if ($deepLink) {
            $variables['deployment']['deepLink'] = $deepLink;
        }

        if ($groupId) {
            $variables['deployment']['groupId'] = $groupId;
        }
    }

    /**
     * Process deployment response
     *
     * @param array $response
     * @param array $variables
     * @param string $description
     * @param string|null $changelog
     * @param string|null $user
     * @param string|null $commit
     * @param string|null $deepLink
     * @param string|null $groupId
     * @return array|false
     */
    private function processDeploymentResponse(
        array $response,
        array $variables,
        string $description,
        ?string $changelog,
        ?string $user,
        ?string $commit,
        ?string $deepLink,
        ?string $groupId
    ): false|array {
        $deploymentData = $response['data']['changeTrackingCreateDeployment'] ?? null;
        $deployedVersion = $variables['deployment']['version'];

        if ($deploymentData) {
            $this->logger->info(
                'NerdGraph deployment created successfully',
                [
                    'deploymentId' => $deploymentData['deploymentId'],
                    'entityGuid' => $deploymentData['entityGuid'],
                    'version' => $deployedVersion,
                    'description' => $description
                ]
            );

            return [
                'deploymentId' => $deploymentData['deploymentId'],
                'entityGuid' => $deploymentData['entityGuid'],
                'version' => $deployedVersion,
                'description' => $description,
                'changelog' => $changelog,
                'user' => $user,
                'commit' => $commit,
                'deepLink' => $deepLink,
                'groupId' => $groupId,
                'timestamp' => $variables['deployment']['timestamp']
            ];
        }

        $this->logger->error('NerdGraph deployment creation failed: No deployment data in response');
        return false;
    }
}
