<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Cache;

use Magento\TestFramework\Cache\CacheConfigurationProvider;
use Magento\TestFramework\Cache\CacheFrontendTestCase;
use PHPUnit\Framework\Attributes\DataProviderExternal;

/**
 * Verifies Magento's public cache frontend contract against all supported configurations.
 *
 * @magentoAppIsolation enabled
 * @magentoDbIsolation enabled
 */
class FrontendContractTest extends CacheFrontendTestCase
{
    #[DataProviderExternal(CacheConfigurationProvider::class, 'provide')]
    public function testSaveLoadAndRemove(string $configurationName, array $configuration): void
    {
        $frontend = $this->createFrontend($configuration, $configurationName);
        $id = $this->cacheId($configurationName, 'contract');
        $value = 'magento-cache-value-' . $id;

        try {
            $this->assertTrue($frontend->save($value, $id, ['INTEGRATION_CONTRACT'], 3600));
            $this->assertSame($value, $frontend->load($id));
            $this->assertTrue($frontend->remove($id));
            $this->assertFalse($frontend->load($id));
        } finally {
            $frontend->remove($id);
        }
    }

    #[DataProviderExternal(CacheConfigurationProvider::class, 'provide')]
    public function testMatchingTagUsesAndSemantics(
        string $configurationName,
        array $configuration
    ): void {
        $frontend = $this->createFrontend($configuration, $configurationName);
        $both = $this->cacheId($configurationName, 'tag_both');
        $first = $this->cacheId($configurationName, 'tag_first');
        $second = $this->cacheId($configurationName, 'tag_second');

        try {
            $frontend->save('both', $both, ['INTEGRATION_A', 'INTEGRATION_B'], 3600);
            $frontend->save('first', $first, ['INTEGRATION_A'], 3600);
            $frontend->save('second', $second, ['INTEGRATION_B'], 3600);
            $this->assertTrue($frontend->clean(
                CacheConstants::CLEANING_MODE_MATCHING_TAG,
                ['INTEGRATION_A', 'INTEGRATION_B']
            ));
            $this->assertFalse($frontend->load($both));
            $this->assertSame('first', $frontend->load($first));
            $this->assertSame('second', $frontend->load($second));
        } finally {
            $frontend->remove($both);
            $frontend->remove($first);
            $frontend->remove($second);
        }
    }

    #[DataProviderExternal(CacheConfigurationProvider::class, 'provide')]
    public function testMatchingAnyTagUsesOrSemantics(
        string $configurationName,
        array $configuration
    ): void {
        $frontend = $this->createFrontend($configuration, $configurationName);
        $first = $this->cacheId($configurationName, 'any_first');
        $second = $this->cacheId($configurationName, 'any_second');
        $other = $this->cacheId($configurationName, 'any_other');

        try {
            $frontend->save('first', $first, ['INTEGRATION_A'], 3600);
            $frontend->save('second', $second, ['INTEGRATION_B'], 3600);
            $frontend->save('other', $other, ['INTEGRATION_C'], 3600);
            $this->assertTrue($frontend->clean(
                CacheConstants::CLEANING_MODE_MATCHING_ANY_TAG,
                ['INTEGRATION_A', 'INTEGRATION_B']
            ));
            $this->assertFalse($frontend->load($first));
            $this->assertFalse($frontend->load($second));
            $this->assertSame('other', $frontend->load($other));
        } finally {
            $frontend->remove($first);
            $frontend->remove($second);
            $frontend->remove($other);
        }
    }

    #[DataProviderExternal(CacheConfigurationProvider::class, 'provide')]
    public function testCleanAllRemovesEntries(
        string $configurationName,
        array $configuration
    ): void {
        $frontend = $this->createFrontend($configuration, $configurationName);
        $id = $this->cacheId($configurationName, 'clean_all');

        try {
            $frontend->save('value', $id, ['INTEGRATION_ALL'], 3600);
            $this->assertSame('value', $frontend->load($id));
            $this->assertTrue($frontend->clean(CacheConstants::CLEANING_MODE_ALL));
            $this->assertFalse($frontend->load($id));
        } finally {
            $frontend->remove($id);
        }
    }
}
