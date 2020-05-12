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
     * @return string[]
     */
    public function validate(): array
    {
        $errors = [];
        try {
            $client = $this->clientResolver->create();
            if (!$client->testConnection()) {
                $errors[] = 'Could not validate a connection to Elasticsearch.'
                    . ' Verify that the Elasticsearch host and port are configured correctly.';
            }
        } catch (\Exception $e) {
            $errors[] = 'Could not validate a connection to Elasticsearch. ' . $e->getMessage();
        }
        return $errors;
    }
}
