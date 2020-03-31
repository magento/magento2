<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\Setup;

use Magento\AdvancedSearch\Model\Client\ClientResolver;
use Magento\Setup\Model\SearchConfigOptionsList;

/**
 * Validate Elasticsearch connection
 */
class ConnectionValidator
{
    /**
     * @var ClientResolver
     */
    private $clientResolver;

    /**
     * @param ClientResolver $clientResolver
     */
    public function __construct(ClientResolver $clientResolver)
    {
        $this->clientResolver = $clientResolver;
    }

    /**
     * Checks Elasticsearch Connection
     *
     * @param array $configuration
     * @return bool true if the connection succeeded, false otherwise
     */
    public function validate($configuration)
    {
        if (isset($configuration[SearchConfigOptionsList::INPUT_KEY_ELASTICSEARCH_SKIP_VALIDATION])
            && $configuration[SearchConfigOptionsList::INPUT_KEY_ELASTICSEARCH_SKIP_VALIDATION]
        ) {
            return true;
        }

        $configOptions = [
            'hostname' => $configuration[SearchConfigOptionsList::INPUT_KEY_ELASTICSEARCH_HOST],
            'port' => $configuration[SearchConfigOptionsList::INPUT_KEY_ELASTICSEARCH_PORT],
            'index' => $configuration[SearchConfigOptionsList::INPUT_KEY_ELASTICSEARCH_INDEX_PREFIX],
            'enableAuth' => $configuration[SearchConfigOptionsList::INPUT_KEY_ELASTICSEARCH_ENABLE_AUTH],
            'username' => $configuration[SearchConfigOptionsList::INPUT_KEY_ELASTICSEARCH_USERNAME] ?? null,
            'password' => $configuration[SearchConfigOptionsList::INPUT_KEY_ELASTICSEARCH_PASSWORD] ?? null,
            'timeout' => $configuration[SearchConfigOptionsList::INPUT_KEY_ELASTICSEARCH_TIMEOUT]
        ];

        try {
            $client = $this->clientResolver->create($configuration['search-engine'], $configOptions);
            return $client->testConnection();
        } catch (\Exception $e) {
            return false;
        }
    }
}
