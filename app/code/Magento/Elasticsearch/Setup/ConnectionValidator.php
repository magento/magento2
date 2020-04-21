<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\Setup;

use Magento\AdvancedSearch\Model\Client\ClientResolver;

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
     * @param string|null $searchEngine if empty, uses the currently configured engine
     * @return bool true if the connection succeeded, false otherwise
     */
    public function validate(string $searchEngine = null): bool
    {
        try {
            $client = $this->clientResolver->create($searchEngine);
            return $client->testConnection();
        } catch (\Exception $e) {
            return false;
        }
    }
}
