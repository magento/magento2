<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Model;

use Magento\Framework\Setup\Option\AbstractConfigOption;
use Magento\Framework\Setup\Option\SelectConfigOption;
use Magento\Framework\Setup\Option\TextConfigOption;

/**
 * Search engine configuration options for install
 */
class SearchConfigOptionsList
{
    public const INPUT_KEY_SEARCH_ENGINE = 'search-engine';

    /**
     * Input key for the Elasticsearch options
     */
    public const INPUT_KEY_ELASTICSEARCH_HOST = 'elasticsearch-host';
    public const INPUT_KEY_ELASTICSEARCH_PORT = 'elasticsearch-port';
    public const INPUT_KEY_ELASTICSEARCH_ENABLE_AUTH = 'elasticsearch-enable-auth';
    public const INPUT_KEY_ELASTICSEARCH_USERNAME = 'elasticsearch-username';
    public const INPUT_KEY_ELASTICSEARCH_PASSWORD = 'elasticsearch-password';
    public const INPUT_KEY_ELASTICSEARCH_INDEX_PREFIX = 'elasticsearch-index-prefix';
    public const INPUT_KEY_ELASTICSEARCH_TIMEOUT = 'elasticsearch-timeout';
    /**
     * Input key for the OpenSearch options
     */
    public const INPUT_KEY_OPENSEARCH_HOST = 'opensearch-host';
    public const INPUT_KEY_OPENSEARCH_PORT = 'opensearch-port';
    public const INPUT_KEY_OPENSEARCH_ENABLE_AUTH = 'opensearch-enable-auth';
    public const INPUT_KEY_OPENSEARCH_USERNAME = 'opensearch-username';
    public const INPUT_KEY_OPENSEARCH_PASSWORD = 'opensearch-password';
    public const INPUT_KEY_OPENSEARCH_INDEX_PREFIX = 'opensearch-index-prefix';
    public const INPUT_KEY_OPENSEARCH_TIMEOUT = 'opensearch-timeout';

    /**
     * Get options list for search engine configuration
     *
     * @return AbstractConfigOption[]
     */
    public function getOptionsList(): array
    {
        return [
            new SelectConfigOption(
                self::INPUT_KEY_SEARCH_ENGINE,
                SelectConfigOption::FRONTEND_WIZARD_SELECT,
                array_keys($this->getAvailableSearchEngineList()),
                '',
                'Search engine. Values: ' . implode(', ', array_keys($this->getAvailableSearchEngineList()))
            ),
            new TextConfigOption(
                self::INPUT_KEY_ELASTICSEARCH_HOST,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                '',
                'Elasticsearch server host.'
            ),
            new TextConfigOption(
                self::INPUT_KEY_ELASTICSEARCH_PORT,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                '',
                'Elasticsearch server port.'
            ),
            new TextConfigOption(
                self::INPUT_KEY_ELASTICSEARCH_ENABLE_AUTH,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                '',
                'Set to 1 to enable authentication. (default is 0, disabled)'
            ),
            new TextConfigOption(
                self::INPUT_KEY_ELASTICSEARCH_USERNAME,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                '',
                'Elasticsearch username. Only applicable if HTTP auth is enabled'
            ),
            new TextConfigOption(
                self::INPUT_KEY_ELASTICSEARCH_PASSWORD,
                TextConfigOption::FRONTEND_WIZARD_PASSWORD,
                '',
                'Elasticsearch password. Only applicable if HTTP auth is enabled'
            ),
            new TextConfigOption(
                self::INPUT_KEY_ELASTICSEARCH_INDEX_PREFIX,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                '',
                'Elasticsearch index prefix.'
            ),
            new TextConfigOption(
                self::INPUT_KEY_ELASTICSEARCH_TIMEOUT,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                '',
                'Elasticsearch server timeout.'
            ),
            new TextConfigOption(
                self::INPUT_KEY_OPENSEARCH_HOST,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                '',
                'OpenSearch server host.'
            ),
            new TextConfigOption(
                self::INPUT_KEY_OPENSEARCH_PORT,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                '',
                'OpenSearch server port.'
            ),
            new TextConfigOption(
                self::INPUT_KEY_OPENSEARCH_ENABLE_AUTH,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                '',
                'Set to 1 to enable authentication. (default is 0, disabled)'
            ),
            new TextConfigOption(
                self::INPUT_KEY_OPENSEARCH_USERNAME,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                '',
                'OpenSearch username. Only applicable if HTTP auth is enabled'
            ),
            new TextConfigOption(
                self::INPUT_KEY_OPENSEARCH_PASSWORD,
                TextConfigOption::FRONTEND_WIZARD_PASSWORD,
                '',
                'OpenSearch password. Only applicable if HTTP auth is enabled'
            ),
            new TextConfigOption(
                self::INPUT_KEY_OPENSEARCH_INDEX_PREFIX,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                '',
                'OpenSearch index prefix.'
            ),
            new TextConfigOption(
                self::INPUT_KEY_OPENSEARCH_TIMEOUT,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                '',
                'OpenSearch server timeout.'
            )
        ];
    }

    /**
     * Get UI friendly list of available search engines
     *
     * @return array
     */
    public function getAvailableSearchEngineList(): array
    {
        return [
            'elasticsearch5' => 'Elasticsearch 5.x (deprecated)',
            'elasticsearch7' => 'Elasticsearch 7.x',
            'elasticsearch8' => 'Elasticsearch 8.x',
            'opensearch' => 'OpenSearch'
        ];
    }
}
