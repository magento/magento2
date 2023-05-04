<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestFramework\TestCase\GraphQl;

use Magento\Framework\App\Cache\StateInterface as CacheStateInterface;
use Magento\GraphQlCache\Model\Cache\Query\Resolver\Result\Type as GraphQlResolverCache;
use Magento\TestFramework\TestCase\GraphQlAbstract;

class ResolverCacheAbstract extends GraphQlAbstract
{
    /**
     * @var GraphQlResolverCache
     */
    private $graphQlResolverCache;

    /**
     * @var CacheStateInterface
     */
    private $cacheState;

    /**
     * @var bool
     */
    private $originalCacheStateEnabledStatus;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->cacheState = $objectManager->get(CacheStateInterface::class);
        $this->originalCacheStateEnabledStatus = $this->cacheState->isEnabled(GraphQlResolverCache::TYPE_IDENTIFIER);
        $this->cacheState->setEnabled(GraphQlResolverCache::TYPE_IDENTIFIER, true);
        $this->graphQlResolverCache = $objectManager->get(GraphQlResolverCache::class);

        parent::setUp();
    }

    /**
     * @inheritDoc
     */
    protected function tearDown(): void
    {
        // clean graphql resolver cache and reset to original enablement status
        $this->graphQlResolverCache->clean();
        $this->cacheState->setEnabled(
            GraphQlResolverCache::TYPE_IDENTIFIER,
            $this->originalCacheStateEnabledStatus
        );

        parent::tearDown();
    }
}
