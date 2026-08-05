<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\NewRelicReporting\Model\NerdGraph;

use Magento\Framework\HTTP\LaminasClientFactory;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\NewRelicReporting\Model\Config;
use Psr\Log\LoggerInterface;
use Laminas\Http\Request;
use Laminas\Http\Exception\RuntimeException;

/**
 * NerdGraph GraphQL API Client for New Relic
 */
class Client
{
    /**
     * @var LaminasClientFactory
     */
    private LaminasClientFactory $httpClientFactory;

    /**
     * @var SerializerInterface
     */
    private SerializerInterface $serializer;

    /**
     * @var Config
     */
    private Config $config;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @param LaminasClientFactory $httpClientFactory
     * @param SerializerInterface $serializer
     * @param Config $config
     * @param LoggerInterface $logger
     */
    public function __construct(
        LaminasClientFactory $httpClientFactory,
        SerializerInterface $serializer,
        Config $config,
        LoggerInterface $logger
    ) {
        $this->httpClientFactory = $httpClientFactory;
        $this->serializer = $serializer;
        $this->config = $config;
        $this->logger = $logger;
    }

    /**
     * Execute a GraphQL query against the NerdGraph API
     *
     * @param string $query The GraphQL query string
     * @param array $variables Optional variables for the query
     * @return array The decoded response data
     * @throws RuntimeException on request or response errors
     */
    public function query(string $query, array $variables = []): array
    {
        if (!$this->config->isNewRelicEnabled()) {
            throw new RuntimeException('New Relic is not enabled');
        }
        try {
            // Use the same API key field for both v2 REST and NerdGraph modes
            $apiKey = $this->config->getNewRelicApiKey();
            $nerdGraphUrl = $this->config->getNerdGraphUrl();

            $client = $this->httpClientFactory->create();
            $client->setUri($nerdGraphUrl);
            $client->setMethod(Request::METHOD_POST);
            $client->setHeaders([
                'Content-Type' => 'application/json',
                'Api-Key' => $apiKey
            ]);

            $requestBody = [
                'query' => $query,
                'variables' => empty($variables) ? (object)[] : $variables
            ];

            $client->setRawBody($this->serializer->serialize($requestBody));
            $response = $client->send();
        } catch (RuntimeException $e) {
            $this->logger->error('NerdGraph API request failed: ' . $e->getMessage());
            throw new RuntimeException('NerdGraph API request failed: ' . $e->getMessage(), 0, $e);
        }

        if ($response->getStatusCode() !== 200) {
            $errorMsg = sprintf(
                'NerdGraph API returned status %d: %s',
                $response->getStatusCode(),
                $response->getBody()
            );
            $this->logger->error($errorMsg);
            throw new RuntimeException($errorMsg);
        }

        $responseData = $this->serializer->unserialize($response->getBody());

        if (!empty($responseData['errors'])) {
            $errorMessages = array_map(
                static fn ($err) => $err['message'] ?? 'Unknown GraphQL error',
                $responseData['errors']
            );
            $errorMsg = 'NerdGraph GraphQL errors: ' . implode(', ', $errorMessages);
            $this->logger->error($errorMsg);
            throw new RuntimeException($errorMsg);
        }

        return $responseData;
    }

    /**
     * Get the GUID of an entity by searching for an application name or ID
     *
     * If multiple entities match, prefer one that is currently reporting data.
     * If none are reporting, return the first match.
     *
     * @param string|null $appName The application name to search for
     * @param string|null $appId The application ID to search for
     * @return string|null The entity GUID, or null if not found
     */
    public function getEntityGuidFromApplication(?string $appName = null, ?string $appId = null): ?string
    {
        $searchQuery = 'type = \'APPLICATION\'';

        if ($appName) {
            $searchQuery .= sprintf(" AND name = '%s'", str_replace("'", "\\'", $appName));
        } elseif ($appId) {
            $searchQuery .= sprintf(" AND appId = '%s'", str_replace("'", "\\'", $appId));
        }

        $query = <<<GRAPHQL
        query GetEntityByApplication(\$query: String!) {
            actor {
                entitySearch(query: \$query) {
                    results {
                        entities {
                            guid
                            name
                            type
                            accountId
                            reporting
                        }
                    }
                }
            }
        }
        GRAPHQL;

        try {
            $response = $this->query($query, ['query' => $searchQuery]);

            $entities = $response['data']['actor']['entitySearch']['results']['entities'] ?? [];
            if (empty($entities)) {
                $this->logger->warning('No entities found for search: ' . $searchQuery);
                return null;
            }

            foreach ($entities as $entity) {
                if ($entity['reporting']) {
                    $this->logger->info(
                        'Using active entity: ' . ($entity['name'] ?? 'Unknown') . ' (GUID: ' . $entity['guid'] . ')'
                    );
                    return $entity['guid'];
                }
            }

            $firstEntity = $entities[0];
            $this->logger->info(
                'Fallback to first entity: ' .
                ($firstEntity['name'] ?? 'Unknown') .
                ' (GUID: ' . $firstEntity['guid'] . ')'
            );
            return $firstEntity['guid'];
        } catch (RuntimeException $e) {
            $this->logger->error(sprintf(
                'Failed to get entity GUID. Search query: %s. Error: %s',
                $searchQuery,
                $e->getMessage()
            ));
            return null;
        }
    }
}
