<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch8\Model\Client;

use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\ClientBuilder;
use Magento\AdvancedSearch\Model\Client\ClientInterface;
use Magento\Elasticsearch\Model\Adapter\FieldsMappingPreprocessorInterface;
use Magento\Elasticsearch8\Model\Adapter\DynamicTemplatesProvider;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;

/**
 * Elasticsearch client
 * @deprecated Elasticsearch8 is no longer supported by Adobe
 * @see this class will be responsible for ES8 only
 */
class Elasticsearch implements ClientInterface
{
    /**
     * @var array
     */
    private array $clientOptions;

    /**
     * Elasticsearch Client instances
     *
     * @var Client[]
     */
    private array $client;

    /**
     * @var bool
     */
    private bool $pingResult = false;

    /**
     * @var FieldsMappingPreprocessorInterface[]
     */
    private array $fieldsMappingPreprocessors;

    /**
     * @var DynamicTemplatesProvider|null
     */
    private $dynamicTemplatesProvider;

    /**
     * Initialize Elasticsearch 8 Client
     *
     * @param array $options
     * @param Client|null $elasticsearchClient
     * @param array $fieldsMappingPreprocessors
     * @param DynamicTemplatesProvider|null $dynamicTemplatesProvider
     * @throws LocalizedException
     */
    public function __construct(
        array $options = [],
        ?Client $elasticsearchClient = null,
        array $fieldsMappingPreprocessors = [],
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
        if ($elasticsearchClient instanceof Client) {
            $this->client[getmypid()] = $elasticsearchClient;
        }
        $this->clientOptions = $options;
        $this->fieldsMappingPreprocessors = $fieldsMappingPreprocessors;
        $this->dynamicTemplatesProvider = $dynamicTemplatesProvider ?: ObjectManager::getInstance()
            ->get(DynamicTemplatesProvider::class);
    }

    /**
     * Get Elasticsearch 8 Client
     *
     * @return Client|null
     */
    private function getElasticsearchClient(): ?Client /** @phpstan-ignore-line */
    {
        $pid = getmypid();
        if (!isset($this->client[$pid])) {
            $config = $this->buildESConfig($this->clientOptions);
            $this->client[$pid] = ClientBuilder::fromConfig($config, true); /** @phpstan-ignore-line */
        }

        return $this->client[$pid];
    }

    /**
     * Ping the Elasticsearch 8 client
     *
     * @return bool
     */
    public function ping(): bool
    {
        $elasticsearchClient = $this->getElasticsearchClient();
        if ($this->pingResult === false && $elasticsearchClient) {
            $this->pingResult = $elasticsearchClient->ping(
                ['client' => ['timeout' => $this->clientOptions['timeout']]]
            )->asBool();
        }

        return $this->pingResult;
    }

    /**
     * Validate connection params for Elasticsearch 8
     *
     * @return bool
     */
    public function testConnection(): bool
    {
        return $this->ping();
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
        $elasticsearchClient = $this->getElasticsearchClient();
        if ($elasticsearchClient) {
            $elasticsearchClient->indices()
                ->putSettings(['index' => $index, 'body' => $settings]);
        }
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
        $elasticsearchClient = $this->getElasticsearchClient();
        if ($elasticsearchClient === null) {
            return;
        }

        $params = ['body' => ['actions' => []]];
        if ($newIndex) {
            $params['body']['actions'][] = ['add' => ['alias' => $alias, 'index' => $newIndex]];
        }

        if ($oldIndex) {
            $params['body']['actions'][] = ['remove' => ['alias' => $alias, 'index' => $oldIndex]];
        }

        $elasticsearchClient->indices()->updateAliases($params);
    }

    /**
     * Checks whether Elasticsearch 8 index exists
     *
     * @param string $index
     * @return bool
     */
    public function indexExists(string $index): bool
    {
        $indexExists = false;
        $elasticsearchClient = $this->getElasticsearchClient();
        if ($elasticsearchClient) {
            $indexExists = $elasticsearchClient->indices()
                ->exists(['index' => $index])
                ->asBool();
        }

        return $indexExists;
    }

    /**
     * Build config for Elasticsearch 8
     *
     * @param array $options
     * @return array
     */
    private function buildESConfig(array $options = []): array
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
     * Exists alias.
     *
     * @param string $alias
     * @param string $index
     * @return bool
     */
    public function existsAlias(string $alias, string $index = ''): bool
    {
        $existAlias = false;
        $elasticsearchClient = $this->getElasticsearchClient();
        if ($elasticsearchClient) {
            $params = ['name' => $alias];
            if ($index) {
                $params['index'] = $index;
            }

            $existAlias = $elasticsearchClient->indices()->existsAlias($params)->asBool();
        }

        return $existAlias;
    }

    /**
     * Performs bulk query over Elasticsearch 8 index
     *
     * @param array $query
     * @return \Elastic\Elasticsearch\Response\Elasticsearch|\Http\Promise\Promise
     */
    public function bulkQuery(array $query)
    {
        return $this->getElasticsearchClient()->bulk($query);
    }

    /**
     * Creates an Elasticsearch 8 index.
     *
     * @param string $index
     * @param array $settings
     * @return void
     */
    public function createIndex(string $index, array $settings): void
    {
        $elasticsearchClient = $this->getElasticsearchClient();
        if ($elasticsearchClient) {
            $elasticsearchClient->indices()
                ->create([
                    'index' => $index,
                    'body' => $settings,
                ]);
        }
    }

    /**
     * Get alias.
     *
     * @param string $alias
     * @return array
     */
    public function getAlias(string $alias): array
    {
        $elasticsearchClient = $this->getElasticsearchClient();
        if ($elasticsearchClient === null) {
            return [];
        }

        return $elasticsearchClient->indices()
            ->getAlias(['name' => $alias])
            ->asArray();
    }

    /**
     * Add mapping to Elasticsearch 8 index
     *
     * @param array $fields
     * @param string $index
     * @param string $entityType
     * @return void
     * @SuppressWarnings("unused")
     */
    public function addFieldsMapping(array $fields, string $index, string $entityType)
    {
        $elasticsearchClient = $this->getElasticsearchClient();
        if ($elasticsearchClient === null) {
            return;
        }

        $params = [
            'index' => $index,
            'body' => [
                'properties' => [],
                'dynamic_templates' => $this->dynamicTemplatesProvider->getTemplates(),
            ],
        ];

        foreach ($this->applyFieldsMappingPreprocessors($fields) as $field => $fieldInfo) {
            $params['body']['properties'][$field] = $fieldInfo;
        }

        $elasticsearchClient->indices()->putMapping($params);
    }

    /**
     * Delete an Elasticsearch 8 index.
     *
     * @param string $index
     * @return void
     */
    public function deleteIndex(string $index)
    {
        $elasticsearchClient = $this->getElasticsearchClient();
        if ($elasticsearchClient) {
            $elasticsearchClient->indices()
                ->delete(['index' => $index]);
        }
    }

    /**
     * Check if index is empty.
     *
     * @param string $index
     * @return bool
     */
    public function isEmptyIndex(string $index): bool
    {
        $elasticsearchClient = $this->getElasticsearchClient();
        if ($elasticsearchClient === null) {
            return false;
        }

        $stats = $this->getElasticsearchClient()->indices()->stats(['index' => $index, 'metric' => 'docs']);
        if ($stats['indices'][$index]['primaries']['docs']['count'] === 0) {
            return true;
        }

        return false;
    }

    /**
     * Execute search by $query
     *
     * @param array $query
     */
    public function query(array $query): array
    {
        $elasticsearchClient = $this->getElasticsearchClient();

        return $elasticsearchClient === null ? [] : $elasticsearchClient->search($query)->asArray();
    }

    /**
     * Get mapping from Elasticsearch index.
     *
     * @param array $params
     * @return array
     */
    public function getMapping(array $params): array
    {
        $elasticsearchClient = $this->getElasticsearchClient();

        return $elasticsearchClient === null ? [] : $elasticsearchClient->indices()->getMapping($params)->asArray();
    }

    /**
     * Apply fields mapping preprocessors
     *
     * @param array $properties
     * @return array
     */
    private function applyFieldsMappingPreprocessors(array $properties): array
    {
        foreach ($this->fieldsMappingPreprocessors as $preprocessor) {
            $properties = $preprocessor->process($properties);
        }

        return $properties;
    }
}
