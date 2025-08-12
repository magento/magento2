<?php
/**
 * Copyright 2022 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\OpenSearch\Model;

use Magento\AdvancedSearch\Model\Client\ClientInterface;
use Magento\Elasticsearch\Model\Adapter\FieldsMappingPreprocessorInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\OpenSearch\Model\Adapter\DynamicTemplatesProvider;
use OpenSearch\Client;
use OpenSearch\ClientBuilder;

class SearchClient implements ClientInterface
{
    /**
     * @var array
     */
    private $clientOptions;

    /**
     * Client instances
     *
     * @var Client[]
     */
    private $client;

    /**
     * @var bool
     */
    private $pingResult;

    /**
     * @var FieldsMappingPreprocessorInterface[]
     */
    private $fieldsMappingPreprocessors;

    /**
     * @var DynamicTemplatesProvider|null
     */
    public $dynamicTemplatesProvider;

    /**
     * Initialize Client
     *
     * @param array $options
     * @param Client|null $openSearchClient
     * @param array $fieldsMappingPreprocessors
     * @param DynamicTemplatesProvider|null $dynamicTemplatesProvider
     * @throws LocalizedException
     */
    public function __construct(
        $options = [],
        $openSearchClient = null,
        $fieldsMappingPreprocessors = [],
        ?DynamicTemplatesProvider $dynamicTemplatesProvider = null
    ) {
        if (empty($options['hostname'])
            || ((!empty($options['enableAuth']) && ($options['enableAuth'] == 1))
                && (empty($options['username']) || empty($options['password'])))
        ) {
            throw new LocalizedException(
                __('The search failed because of a search engine misconfiguration.')
            );
        }
        // phpstan:ignore
        if ($openSearchClient instanceof Client) {
            $this->client[getmypid()] = $openSearchClient;
        }
        $this->clientOptions = $options;
        $this->fieldsMappingPreprocessors = $fieldsMappingPreprocessors;
        $this->dynamicTemplatesProvider = $dynamicTemplatesProvider ?: ObjectManager::getInstance()
            ->get(DynamicTemplatesProvider::class);
    }

    /**
     * Execute suggest query for OpenSearch
     *
     * @param array $query
     * @return array
     */
    public function suggest(array $query): array
    {
        return $this->getOpenSearchClient()->suggest($query);
    }

    /**
     * Get OS Client
     *
     * @return Client
     */
    public function getOpenSearchClient(): Client
    {
        $pid = getmypid();
        if (!isset($this->client[$pid])) {
            $config = $this->buildOSConfig($this->clientOptions);
            $this->client[$pid] = ClientBuilder::fromConfig($config, true);
        }
        return $this->client[$pid];
    }

    /**
     * Ping the client
     *
     * @return bool
     */
    public function ping(): bool
    {
        if ($this->pingResult === null) {
            $this->pingResult = $this->getOpenSearchClient()
                ->ping(['client' => ['timeout' => $this->clientOptions['timeout']]]);
        }

        return $this->pingResult;
    }

    /**
     * Validate connection params for OpenSearch
     *
     * @return bool
     */
    public function testConnection(): bool
    {
        return $this->ping();
    }

    /**
     * Build config for OpenSearch
     *
     * @param array $options
     * @return array
     */
    private function buildOSConfig(array $options = []): array
    {
        $hostname = preg_replace('/http[s]?:\/\//i', '', $options['hostname']);
        // @codingStandardsIgnoreStart
        $protocol = parse_url($options['hostname'], PHP_URL_SCHEME);
        // @codingStandardsIgnoreEnd
        if (!$protocol) {
            $protocol = 'http';
        }

        $authString = '';
        if (!empty($options['enableAuth']) && (int)$options['enableAuth'] === 1) {
            $authString = "{$options['username']}:{$options['password']}@";
        }

        $portString = '';
        if (!empty($options['port'])) {
            $portString = ':' . $options['port'];
        }

        $host = $protocol . '://' . $authString . $hostname . $portString;

        $options['hosts'] = [$host];

        return $options;
    }

    /**
     * Performs bulk query over OpenSearch  index
     *
     * @param array $query
     * @return array
     */
    public function bulkQuery(array $query)
    {
        return $this->getOpenSearchClient()->bulk($query);
    }

    /**
     * Creates an OpenSearch index.
     *
     * @param string $index
     * @param array $settings
     * @return void
     */
    public function createIndex(string $index, array $settings)
    {
        $this->getOpenSearchClient()->indices()->create(
            [
                'index' => $index,
                'body' => $settings,
            ]
        );
    }

    /**
     * Add/update an Elasticsearch index settings.
     *
     * @param string $index
     * @param array $settings
     * @return void
     */
    public function putIndexSettings(string $index, array $settings): void
    {
        $this->getOpenSearchClient()->indices()->putSettings(
            [
                'index' => $index,
                'body' => $settings,
            ]
        );
    }

    /**
     * Delete an OpenSearch index.
     *
     * @param string $index
     * @return void
     */
    public function deleteIndex(string $index)
    {
        $this->getOpenSearchClient()->indices()->delete(['index' => $index]);
    }

    /**
     * Check if index is empty.
     *
     * @param string $index
     * @return bool
     */
    public function isEmptyIndex(string $index): bool
    {
        $stats = $this->getOpenSearchClient()->indices()->stats(['index' => $index, 'metric' => 'docs']);
        if ($stats['indices'][$index]['primaries']['docs']['count'] === 0) {
            return true;
        }

        return false;
    }

    /**
     * Updates alias.
     *
     * @param string $alias
     * @param string $newIndex
     * @param string $oldIndex
     * @return void
     */
    public function updateAlias(string $alias, string $newIndex, string $oldIndex = '')
    {
        $params = [
            'body' => [
                'actions' => [],
            ],
        ];
        if ($oldIndex) {
            $params['body']['actions'][] = ['remove' => ['alias' => $alias, 'index' => $oldIndex]];
        }
        if ($newIndex) {
            $params['body']['actions'][] = ['add' => ['alias' => $alias, 'index' => $newIndex]];
        }

        $this->getOpenSearchClient()->indices()->updateAliases($params);
    }

    /**
     * Checks whether OpenSearch index exists
     *
     * @param string $index
     * @return bool
     */
    public function indexExists(string $index): bool
    {
        return $this->getOpenSearchClient()->indices()->exists(['index' => $index]);
    }

    /**
     * Exists alias.
     *
     * @param string $alias
     * @param string $index
     * @return bool
     */
    public function existsAlias(string $alias, string $index = ''): bool
    {
        $params = ['name' => $alias];
        if ($index) {
            $params['index'] = $index;
        }

        return $this->getOpenSearchClient()->indices()->existsAlias($params);
    }

    /**
     * Get alias.
     *
     * @param string $alias
     * @return array
     */
    public function getAlias(string $alias): array
    {
        return $this->getOpenSearchClient()->indices()->getAlias(['name' => $alias]);
    }

    /**
     * Add mapping to OpenSearch index
     *
     * @param array $fields
     * @param string $index
     * @param string $entityType
     * @return void
     */
    public function addFieldsMapping(array $fields, string $index, string $entityType)
    {
        $params = [
            'index' => $index,
            'type' => $entityType,
            'include_type_name' => true,
            'body' => [
                $entityType => [
                    'properties' => [],
                    'dynamic_templates' => $this->dynamicTemplatesProvider->getTemplates(),
                ],
            ],
        ];

        foreach ($this->applyFieldsMappingPreprocessors($fields) as $field => $fieldInfo) {
            $params['body'][$entityType]['properties'][$field] = $fieldInfo;
        }

        $this->getOpenSearchClient()->indices()->putMapping($params);
    }

    /**
     * Execute search by $query
     *
     * @param array $query
     * @return array
     */
    public function query(array $query): array
    {
        return $this->getOpenSearchClient()->search($query);
    }

    /**
     * Get mapping from Elasticsearch index.
     *
     * @param array $params
     * @return array
     */
    public function getMapping(array $params): array
    {
        return $this->getOpenSearchClient()->indices()->getMapping($params);
    }

    /**
     * Delete mapping in OpenSearch index
     *
     * @param string $index
     * @param string $entityType
     * @return void
     */
    public function deleteMapping(string $index, string $entityType)
    {
        $this->getOpenSearchClient()->indices()->deleteMapping(
            [
                'index' => $index,
                'type' => $entityType,
            ]
        );
    }

    /**
     * Apply fields mapping preprocessors
     *
     * @param array $properties
     * @return array
     */
    public function applyFieldsMappingPreprocessors(array $properties): array
    {
        foreach ($this->fieldsMappingPreprocessors as $preprocessor) {
            $properties = $preprocessor->process($properties);
        }
        return $properties;
    }

    /**
     * Open point in time
     *
     * @param array $params
     * @return array
     */
    public function openPointInTime(array $params = []): array
    {
        return $this->getOpenSearchClient()->createPointInTime($params);
    }

    /**
     * Close point in time
     *
     * @param array $params
     * @return array
     */
    public function closePointInTime(array $params = []): array
    {
        return $this->getOpenSearchClient()->deletePointInTime($params);
    }
}
