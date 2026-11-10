<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GraphQlResolverCache;

use Magento\GraphQl\Service\GraphQlRequest;
use Magento\GraphQlResolverCache\Model\Resolver\Result\Type;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Verifies the resolver cache through a real GraphQL request and Magento cache type.
 *
 * @magentoAppArea graphql
 * @magentoAppIsolation enabled
 */
class GraphQlResolverCacheIntegrationTest extends TestCase
{
    public function testRepeatedStoreConfigRequestIsCacheable(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $request = $objectManager->get(GraphQlRequest::class);
        $cache = $objectManager->get(Type::class);
        $cache->clean();

        $query = '{ storeConfig { code store_name } }';
        try {
            $first = $request->send($query);
            $this->assertSame(200, $first->getHttpResponseCode());
            $this->assertStringNotContainsString('errors', strtolower((string) $first->getBody()));

            $second = $request->send($query);
            $this->assertSame(200, $second->getHttpResponseCode());
            $this->assertSame((string) $first->getBody(), (string) $second->getBody());
        } finally {
            $cache->clean();
        }
    }
}
