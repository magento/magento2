<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\PageCache;

use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Abstract class for GraphQL page cache test
 */
class GraphQLPageCacheAbstract extends GraphQlAbstract
{
    /**
     * Assert that we obtain a cache MISS when sending the provided query & headers.
     *
     * @param string $query
     * @param array $headers
     * @return array
     */
    protected function assertCacheMissAndReturnResponse(string $query, array $headers) :array
    {
        $responseMiss = $this->graphQlQueryWithResponseHeaders($query, [], '', $headers);
        $this->assertArrayHasKey('X-Magento-Cache-Debug', $responseMiss['headers']);
        $this->assertEquals('MISS', $responseMiss['headers']['X-Magento-Cache-Debug']);
        return $responseMiss;
    }

    /**
     * Assert that we obtain a cache HIT when sending the provided query & headers.
     *
     * @param string $query
     * @param array $headers
     * @return array
     */
    protected function assertCacheHitAndReturnResponse(string $query, array $headers) :array
    {
        $responseHit = $this->graphQlQueryWithResponseHeaders($query, [], '', $headers);
        $this->assertArrayHasKey('X-Magento-Cache-Debug', $responseHit['headers']);
        $this->assertEquals('HIT', $responseHit['headers']['X-Magento-Cache-Debug']);
        return $responseHit;
    }
}
