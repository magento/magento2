<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\Setup;

use Magento\AdvancedSearch\Model\Client\ClientResolver;
use Magento\Search\Model\SearchEngine\ValidatorInterface;

/**
 * Validate Elasticsearch connection
 */
class Validator implements ValidatorInterface
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
     * @param array $searchConfig
     * @return array
     */
    public function validate(array $searchConfig = []): array
    {
        $errors = [];
        $searchEngine = $searchConfig['search-engine'] ?? null;
        try {
            $client = $this->clientResolver->create($searchEngine);
            if (!$client->testConnection()) {
                $errors[] = 'Elasticsearch connection validation failed';
            }
        } catch (\Exception $e) {
            $errors[] = 'Elasticsearch connection validation failed: ' . $e->getMessage();
        }
        return $errors;
    }
}
