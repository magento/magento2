<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
     * Elasticsearch Client instances
     *
     * @var \Elasticsearch\Client[]
     */
    private $client;

    /**
     * @var array
     */
    private $clientOptions;

    /**
     * @var bool
     */
    private $pingResult;

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
        if (empty($options['hostname'])
            || ((!empty($options['enableAuth']) && ($options['enableAuth'] == 1))
                && (empty($options['username']) || empty($options['password'])))
        ) {
            throw new LocalizedException(
                __('The search failed because of a search engine misconfiguration.')
            );
        }

        if (!($elasticsearchClient instanceof \Elasticsearch\Client)) {
            $config = $this->buildConfig($options);
            $elasticsearchClient = \Elasticsearch\ClientBuilder::fromConfig($config, true);
        }
        $this->client[getmypid()] = $elasticsearchClient;
        $this->clientOptions = $options;
    }

    /**
     * Get Elasticsearch Client
     *
     * @return \Elasticsearch\Client
     */
    private function getClient()
    {
        $pid = getmypid();
        if (!isset($this->client[$pid])) {
            $config = $this->buildConfig($this->clientOptions);
            $this->client[$pid] = \Elasticsearch\ClientBuilder::fromConfig($config, true);
        }
        return $this->client[$pid];
    }

    /**
     * Ping the Elasticsearch client
     *
     * @return bool
     */
    public function ping()
    {
        if ($this->pingResult === null) {
            $this->pingResult = $this->getClient()->ping(['client' => ['timeout' => $this->clientOptions['timeout']]]);
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
     * Build config.
     *
     * @param array $options
     * @return array
     */
    private function buildConfig($options = [])
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
     * Performs bulk query over Elasticsearch index
     *
     * @param array $query
     * @return void
     */
    public function bulkQuery($query)
    {
        $this->getClient()->bulk($query);
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
        $this->getClient()->indices()->create(
            [
                'index' => $index,
                'body' => $settings,
            ]
        );
    }

    /**
     * Delete an Elasticsearch index.
     *
     * @param string $index
     * @return void
     */
    public function deleteIndex($index)
    {
        $this->getClient()->indices()->delete(['index' => $index]);
    }

    /**
     * Check if index is empty.
     *
     * @param string $index
     * @return bool
     */
    public function isEmptyIndex($index)
    {
        $stats = $this->getClient()->indices()->stats(['index' => $index, 'metric' => 'docs']);
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

        $this->getClient()->indices()->updateAliases($params);
    }

    /**
     * Checks whether Elasticsearch index exists
     *
     * @param string $index
     * @return bool
     */
    public function indexExists($index)
    {
        return $this->getClient()->indices()->exists(['index' => $index]);
    }

    /**
     * Check if alias exists.
     *
     * @param string $alias
     * @param string $index
     * @return bool
     */
    public function existsAlias($alias, $index = '')
    {
        $params = ['name' => $alias];
        if ($index) {
            $params['index'] = $index;
        }
        return $this->getClient()->indices()->existsAlias($params);
    }

    /**
     * Get alias.
     *
     * @param string $alias
     * @return array
     */
    public function getAlias($alias)
    {
        return $this->getClient()->indices()->getAlias(['name' => $alias]);
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
                        'type' => 'string',
                    ],
                    'properties' => [],
                    'dynamic_templates' => [
                        [
                            'price_mapping' => [
                                'match' => 'price_*',
                                'match_mapping' => 'string',
                                'mapping' => [
                                    'type' => 'float',
                                    'store' => true,
                                ],
                            ],
                        ],
                        [
                            'position_mapping' => [
                                'match' => 'position_*',
                                'match_mapping' => 'string',
                                'mapping' => [
                                    'type' => 'integer',
                                    'index' => 'not_analyzed',
                                ],
                            ],
                        ],
                        [
                            'string_mapping' => [
                                'match' => '*',
                                'match_mapping' => 'string',
                                'mapping' => [
                                    'type' => 'string',
                                    'index' => 'not_analyzed',
                                ],
                            ],
                        ],
                        [
                            'integer_mapping' => [
                                'match_mapping_type' => 'long',
                                'mapping' => [
                                    'type' => 'integer',
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
        $this->getClient()->indices()->putMapping($params);
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
        $this->getClient()->indices()->deleteMapping(
            [
                'index' => $index,
                'type' => $entityType,
            ]
        );
    }

    /**
     * Execute search by $query
     *
     * @param array $query
     * @return array
     */
    public function query($query)
    {
        return $this->getClient()->search($query);
    }

    /**
     * Execute suggest query
     *
     * @param array $query
     * @return array
     */
    public function suggest($query)
    {
        return $this->getClient()->suggest($query);
    }
}
