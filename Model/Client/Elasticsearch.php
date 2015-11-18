<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Model\Client;

use Magento\Framework\Exception\LocalizedException;
use Elasticsearch\Common\Exceptions\NoNodesAvailableException;
use Elasticsearch\Common\Exceptions\Missing404Exception;
use Magento\AdvancedSearch\Model\Client\ClientInterface;

/**
 * Elasticsearch client
 */
class Elasticsearch implements ClientInterface
{
    /**#@+
     * Text flags for Elasticsearch ping statuses
     */
    const ELASTICSEARCH_PING_STATUS_OK = 'OK';
    const ELASTICSEARCH_PING_STATUS_ERROR = 'ERROR';
    /**#@-*/

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
            $elasticsearchClient = \Elasticsearch\ClientBuilder::fromConfig($config);
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
        return $this->client->ping(['client' => ['timeout' => $this->clientOptions['timeout']]]);
    }

    /**
     * Validate connection params
     *
     * @return bool
     */
    public function testConnection()
    {
        if (!empty($this->clientOptions['index'])) {
            return $this->client->indices()->exists(['index' => $this->clientOptions['index']]);
        } else {
            // if no index is given simply perform a ping
            $this->ping();
        }
        return true;
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
        $config = [
            'hosts' => [
                $host,
            ],
        ];
        return $config;
    }

    /**
     * Adds a collection of documents in bulk format to Elasticsearch index
     *
     * @param array $documents
     * @return void
     */
    public function addDocuments($documents)
    {
        $this->client->bulk($documents);
    }

    /**
     * Delete all documents from index
     *
     * @param string $index
     * @param string $entityType
     * @return void
     */
    public function deleteDocumentsFromIndex($index, $entityType)
    {
        try {
            $this->client->deleteByQuery([
                'index' => $index,
                'type' => $entityType,
                'body' => [
                    'query' => [
                        'match_all' => [],
                    ],
                ],
            ]);
        } catch (Missing404Exception $e) {
            // Data wasn't indexer yet.
        }
    }

    /**
     * Delete documents from index by ids
     *
     * @param array $ids
     * @param string $index
     * @param string $entityType
     * @param $entityType
     */
    public function deleteDocumentsByIds(array $ids, $index, $entityType)
    {
        try {
            $this->client->deleteByQuery([
                'index' => $index,
                'type' => $entityType,
                'body' => [
                    'query' => [
                        'ids' => [
                            'type' => $entityType,
                            'values' => $ids,
                        ],
                    ],
                ],
            ]);
        } catch (Missing404Exception $e) {
            // Data wasn't indexer yet.
        }
    }

    /**
     * Creates an Elasticsearch index if not exists
     *
     * @param string $index
     * @return bool
     * @throws \Exception
     */
    public function createIndexIfNotExists($index)
    {
        $params['index'] = $index;
        try {
            $this->client->indices()->getSettings($params);
        } catch (Missing404Exception $e) {
            $this->client->indices()->create($params);
            return true;
        } catch (\Exception $e) {
            throw $e;
        }
        return false;
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
                ],
            ],
        ];

        foreach ($fields as $field => $fieldInfo) {
            $params['body'][$entityType]['properties'][$field] = $fieldInfo;
        }

        try {
            $this->client->indices()->putMapping($params);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Delete mapping in Elasticsearch index
     *
     * @param string $index
     * @param string $entityType
     * @return void
     * @throws \Exception
     */
    public function deleteMapping($index, $entityType)
    {
        try {
            $this->client->indices()->deleteMapping([
                'index' => $index,
                'type' => $entityType,
            ]);
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
