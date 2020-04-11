<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Model;

use Magento\Framework\Setup\Option\AbstractConfigOption;
use Magento\Framework\Setup\Option\FlagConfigOption;
use Magento\Framework\Setup\Option\SelectConfigOption;
use Magento\Framework\Setup\Option\TextConfigOption;

/**
 * Search engine configuration options for install
 */
class SearchConfigOptionsList
{
    /**
     * Input key for the options
     */
    const INPUT_KEY_SEARCH_ENGINE = 'search-engine';
    const INPUT_KEY_ELASTICSEARCH_HOST = 'elasticsearch-host';
    const INPUT_KEY_ELASTICSEARCH_PORT = 'elasticsearch-port';
    const INPUT_KEY_ELASTICSEARCH_ENABLE_AUTH = 'elasticsearch-enable-auth';
    const INPUT_KEY_ELASTICSEARCH_USERNAME = 'elasticsearch-username';
    const INPUT_KEY_ELASTICSEARCH_PASSWORD = 'elasticsearch-password';
    const INPUT_KEY_ELASTICSEARCH_INDEX_PREFIX = 'elasticsearch-index-prefix';
    const INPUT_KEY_ELASTICSEARCH_TIMEOUT = 'elasticsearch-timeout';

    /**
     * Default values
     */
    const DEFAULT_SEARCH_ENGINE = 'elasticsearch7';
    const DEFAULT_ELASTICSEARCH_HOST = 'localhost';
    const DEFAULT_ELASTICSEARCH_PORT = '9200';
    const DEFAULT_ELASTICSEARCH_INDEX_PREFIX = 'magento2';
    const DEFAULT_ELASTICSEARCH_TIMEOUT = 15;

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
                'Search engine. Values: ' . implode(', ', array_keys($this->getAvailableSearchEngineList())),
                self::DEFAULT_SEARCH_ENGINE
            ),
            new TextConfigOption(
                self::INPUT_KEY_ELASTICSEARCH_HOST,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                '',
                'Elasticsearch server host.',
                self::DEFAULT_ELASTICSEARCH_HOST
            ),
            new TextConfigOption(
                self::INPUT_KEY_ELASTICSEARCH_PORT,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                '',
                'Elasticsearch server port.',
                self::DEFAULT_ELASTICSEARCH_PORT
            ),
            new FlagConfigOption(
                self::INPUT_KEY_ELASTICSEARCH_ENABLE_AUTH,
                '',
                'Enable Elasticsearch HTTP authentication.',
                null
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
                'Elasticsearch index prefix.',
                self::DEFAULT_ELASTICSEARCH_INDEX_PREFIX
            ),
            new TextConfigOption(
                self::INPUT_KEY_ELASTICSEARCH_TIMEOUT,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                '',
                'Elasticsearch server timeout.',
                self::DEFAULT_ELASTICSEARCH_TIMEOUT
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
            'elasticsearch6' => 'Elasticsearch 6.x',
            'elasticsearch7' => 'Elasticsearch 7.x'
        ];
    }
}
