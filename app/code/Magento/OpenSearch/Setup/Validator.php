<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\OpenSearch\Setup;

use Magento\AdvancedSearch\Model\Client\ClientResolver;
use Magento\Search\Model\SearchEngine\ValidatorInterface;

/**
 * Validate Search engine connection
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
     * @inheritdoc
     */
    public function validate(): array
    {
        $errors = [];
        try {
            $client = $this->clientResolver->create();
            if (!$client->testConnection()) {
                $engine = $this->clientResolver->getCurrentEngine();
                $errors[] = "Could not validate a connection to the Search engine: $engine."
                    . ' Verify that the host and port are configured correctly.';
            }
        } catch (\Exception $e) {
            $errors[] = 'Could not validate a connection to the OpenSearch. ' . $e->getMessage();
        }
        return $errors;
    }
}
