<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Model\Client;

use Magento\Framework\Exception\LocalizedException;
use Magento\AdvancedSearch\Model\Client\ClientInterface;

/**
 * Elasticsearch client
 */
class Elasticsearch implements ClientInterface
{
    /**
     * Elasticsearch Client instance
     *
     * @var \Elasticsearch\Client
     */
    protected $client;

    /**
     * @var array
     */
    protected $clientOptions;

    /**
     * @var bool
     */
    protected $pingResult;

    /**
     * Initialize Elasticsearch Client
     *
     * @param array $options
     * @param \Elasticsearch\Client|null $elasticsearchClient
     * @throws LocalizedException
     */
    public function __construct(
        $options = [],
        $elasticsearchClient = null
    ) {
        if (empty($options['hostname']) || ((!empty($options['enableAuth']) &&
            ($options['enableAuth'] == 1)) && (empty($options['username']) || empty($options['password'])))) {
            throw new LocalizedException(
                __('We were unable to perform the search because of a search engine misconfiguration.')
            );
        }

        if (!($elasticsearchClient instanceof \Elasticsearch\Client)) {
            $config = $this->buildConfig($options);
            $elasticsearchClient = \Elasticsearch\ClientBuilder::fromConfig($config, true);
        }
        $this->client = $elasticsearchClient;
        $this->clientOptions = $options;
    }

    /**
     * Ping the Elasticsearch client
     *
     * @return bool
     */
    public function ping()
    {
        if ($this->pingResult === null) {
            $this->pingResult = $this->client->ping(['client' => ['timeout' => $this->clientOptions['timeout']]]);
        }
        return $this->pingResult;
    }

    /**
     * Validate connection params
     *
     * @return bool
     */
    public function testConnection()
    {
        return $this->ping();
    }

    /**
     * @param array $options
     * @return array
     */
    private function buildConfig($options = [])
    {
        $host = preg_replace('/http[s]?:\/\//i', '', $options['hostname']);
        $protocol = parse_url($options['hostname'], PHP_URL_SCHEME);
        if (!$protocol) {
            $protocol = 'http';
        }
        if (!empty($options['port'])) {
            $host .= ':' . $options['port'];
        }
        if (!empty($options['enableAuth']) && ($options['enableAuth'] == 1)) {
            $host = sprintf('%s://%s:%s@%s', $protocol, $options['username'], $options['password'], $host);
        }

        $options['hosts'] = [$host];
        return $options;
    }

    /**
     * Performs bulk query over Elasticsearch index
     *
     * @param array $query
     * @return void
     */
    public function bulkQuery($query)
    {
        $this->client->bulk($query);
    }

    /**
     * Creates an Elasticsearch index.
     *
     * @param string $index
     * @param array $settings
     * @return void
     */
    public function createIndex($index, $settings)
    {
        $this->client->indices()->create([
            'index' => $index,
            'body' => $settings,
        ]);
    }

    /**
     * Delete an Elasticsearch index.
     *
     * @param string $index
     * @return void
     */
    public function deleteIndex($index)
    {
        $this->client->indices()->delete(['index' => $index]);
    }

    /**
     * Check if index is empty.
     *
     * @param string $index
     * @return bool
     */
    public function isEmptyIndex($index)
    {
        $stats = $this->client->indices()->stats(['index' => $index, 'metric' => 'docs']);
        if ($stats['indices'][$index]['primaries']['docs']['count'] == 0) {
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
    public function updateAlias($alias, $newIndex, $oldIndex = '')
    {
        $params['body'] = ['actions' => []];
        if ($oldIndex) {
            $params['body']['actions'][] = ['remove' => ['alias' => $alias, 'index' => $oldIndex]];
        }
        if ($newIndex) {
            $params['body']['actions'][] = ['add' => ['alias' => $alias, 'index' => $newIndex]];
        }

        $this->client->indices()->updateAliases($params);
    }

    /**
     * Checks whether Elasticsearch index exists
     *
     * @param string $index
     * @return bool
     */
    public function indexExists($index)
    {
         return $this->client->indices()->exists(['index' => $index]);
    }

    /**
     * @param string $alias
     * @param string $index
     *
     * @return bool
     */
    public function existsAlias($alias, $index = '')
    {
        $params = ['name' => $alias];
        if ($index) {
            $params['index'] = $index;
        }
        return $this->client->indices()->existsAlias($params);
    }

    /**
     * @param string $alias
     *
     * @return array
     */
    public function getAlias($alias)
    {
        return $this->client->indices()->getAlias(['name' => $alias]);
    }

    /**
     * Add mapping to Elasticsearch index
     *
     * @param array $fields
     * @param string $index
     * @param string $entityType
     * @return void
     */
    public function addFieldsMapping(array $fields, $index, $entityType)
    {
        $params = [
            'index' => $index,
            'type' => $entityType,
            'body' => [
                $entityType => [
                    '_all' => [
                        'enabled' => true,
                        'type' => 'string'
                    ],
                    'properties' => [],
                    'dynamic_templates' => [
                        [
                            'price_mapping' => [
                                'match' => 'price_*',
                                'match_mapping' => 'string',
                                'mapping' => [
                                    'type' => 'float'
                                ],
                            ],
                        ],
                        [
                            'string_mapping' => [
                                'match' => '*',
                                'match_mapping' => 'string',
                                'mapping' => [
                                    'type' => 'string',
                                    'index' => 'no'
                                ],
                            ],
                        ],
                        [
                            'position_mapping' => [
                                'match' => 'position_*',
                                'match_mapping' => 'string',
                                'mapping' => [
                                    'type' => 'int'
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
        foreach ($fields as $field => $fieldInfo) {
            $params['body'][$entityType]['properties'][$field] = $fieldInfo;
        }
        $this->client->indices()->putMapping($params);
    }

    /**
     * Delete mapping in Elasticsearch index
     *
     * @param string $index
     * @param string $entityType
     * @return void
     */
    public function deleteMapping($index, $entityType)
    {
        $this->client->indices()->deleteMapping([
            'index' => $index,
            'type' => $entityType,
        ]);
    }

    /**
     * Execute search by $query
     *
     * @param array $query
     * @return array
     */
    public function query($query)
    {
        return $this->client->search($query);
    }

    /**
     * Execute suggest query
     *
     * @param array $query
     * @return array
     */
    public function suggest($query)
    {
        return $this->client->suggest($query);
    }
}
