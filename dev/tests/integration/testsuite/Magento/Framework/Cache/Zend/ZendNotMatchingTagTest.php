<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Cache\Zend;

use Magento\Framework\Cache\CacheConstants;
use Magento\TestFramework\Cache\CacheConfigurationProvider;
use Magento\TestFramework\Cache\CacheFrontendTestCase;

/**
 * Documents the legacy frontend contract for NOT_MATCHING_TAG.
 *
 * @magentoAppIsolation enabled
 * @magentoDbIsolation enabled
 */
class ZendNotMatchingTagTest extends CacheFrontendTestCase
{
    public function testNotMatchingTagIsRejectedByLegacyFrontend(): void
    {
        $configuration = CacheConfigurationProvider::provide()['zend-redis'];
        $frontend = $this->createFrontend($configuration[1], $configuration[0], 'NOT_TAG');

        $this->expectException(\InvalidArgumentException::class);
        $frontend->clean(CacheConstants::CLEANING_MODE_NOT_MATCHING_TAG, ['KEEP_TAG']);
    }
}
