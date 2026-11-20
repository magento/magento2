<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Swagger\Api;

use Magento\Framework\Webapi\Rest\Request;
use Magento\TestFramework\TestCase\WebapiAbstract;

/**
 * Web API functional test for Swagger schema.
 *
 * Ensures the REST schema endpoint exposes expected service tag names.
 */
class SwaggerSchemaTest extends WebapiAbstract
{
    /**
     * Verify Swagger schema contains expected service tags.
     * @return void
     */
    public function testSchemaContainsExpectedServiceTags(): void
    {
        $serviceInfo = [
            'rest' => [
                // The REST client will prefix "/rest/", the adapter will inject "/all"
                // so final URL is: /rest/all/schema?services=all
                'resourcePath' => '/schema?services=all',
                'httpMethod' => Request::HTTP_METHOD_GET,
            ],
        ];

        // Force store scope 'all' so schema aggregates all services
        $response = $this->_webApiCall($serviceInfo, [], self::ADAPTER_REST, 'all');
        self::assertIsArray($response);
        self::assertArrayHasKey('tags', $response);
        self::assertIsArray($response['tags']);

        $tagNames = array_column($response['tags'], 'name');
        self::assertContains('storeStoreRepositoryV1', $tagNames);
        self::assertContains('quoteCartRepositoryV1', $tagNames);
        self::assertContains('catalogProductRepositoryV1', $tagNames);
    }
}
