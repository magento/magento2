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
 * Verifies tag cleanup and tag replacement through Magento's public cache frontend.
 *
 * @magentoAppIsolation enabled
 * @magentoDbIsolation enabled
 */
class TagCleaningTest extends CacheFrontendTestCase
{
    #[DataProviderExternal(CacheConfigurationProvider::class, 'provide')]
    public function testMultipleTagsAreStoredAndMatched(
        string $configurationName,
        array $configuration
    ): void {
        $frontend = $this->createFrontend($configuration, $configurationName, 'IT_TAG');
        $id = $this->cacheId($configurationName, 'multiple');

        try {
            $this->assertTrue($frontend->save(
                'tagged-value',
                $id,
                ['INTEGRATION_TAG_A', 'INTEGRATION_TAG_B'],
                3600
            ));
            $this->assertSame('tagged-value', $frontend->load($id));

            $this->assertTrue($frontend->clean(
                CacheConstants::CLEANING_MODE_MATCHING_TAG,
                ['INTEGRATION_TAG_A', 'INTEGRATION_TAG_B']
            ));
            $this->assertFalse($frontend->load($id));
        } finally {
            $frontend->remove($id);
        }
    }

    #[DataProviderExternal(CacheConfigurationProvider::class, 'provide')]
    public function testMatchingTagUsesAndAndAnyTagUsesOr(
        string $configurationName,
        array $configuration
    ): void {
        $frontend = $this->createFrontend($configuration, $configurationName, 'IT_TAG');
        $both = $this->cacheId($configurationName, 'both');
        $first = $this->cacheId($configurationName, 'first');
        $second = $this->cacheId($configurationName, 'second');
        $other = $this->cacheId($configurationName, 'other');

        try {
            $frontend->save('both', $both, ['INTEGRATION_A', 'INTEGRATION_B'], 3600);
            $frontend->save('first', $first, ['INTEGRATION_A'], 3600);
            $frontend->save('second', $second, ['INTEGRATION_B'], 3600);
            $frontend->save('other', $other, ['INTEGRATION_C'], 3600);

            $this->assertTrue($frontend->clean(
                CacheConstants::CLEANING_MODE_MATCHING_TAG,
                ['INTEGRATION_A', 'INTEGRATION_B']
            ));
            $this->assertFalse($frontend->load($both));
            $this->assertSame('first', $frontend->load($first));
            $this->assertSame('second', $frontend->load($second));
            $this->assertSame('other', $frontend->load($other));

            $frontend->save('both', $both, ['INTEGRATION_A', 'INTEGRATION_B'], 3600);
            $this->assertTrue($frontend->clean(
                CacheConstants::CLEANING_MODE_MATCHING_ANY_TAG,
                ['INTEGRATION_A', 'INTEGRATION_B']
            ));
            $this->assertFalse($frontend->load($both));
            $this->assertFalse($frontend->load($first));
            $this->assertFalse($frontend->load($second));
            $this->assertSame('other', $frontend->load($other));
        } finally {
            $frontend->remove($both);
            $frontend->remove($first);
            $frontend->remove($second);
            $frontend->remove($other);
        }
    }

    #[DataProviderExternal(CacheConfigurationProvider::class, 'provide')]
    public function testSavingSameIdReplacesOldTagAssociation(
        string $configurationName,
        array $configuration
    ): void {
        if ($configurationName === 'zend-file') {
            $this->markTestSkipped(
                'Legacy file frontend does not provide retag-index replacement; '
                . 'legacy Redis retagging is covered by test13_retag_cleanup.php.'
            );
        }

        $frontend = $this->createFrontend($configuration, $configurationName, 'IT_TAG');
        $id = $this->cacheId($configurationName, 'retag');

        try {
            $this->assertTrue($frontend->save(
                'old-value',
                $id,
                ['INTEGRATION_OLD_TAG'],
                3600
            ));
            $this->assertTrue($frontend->save(
                'new-value',
                $id,
                ['INTEGRATION_NEW_TAG'],
                3600
            ));
            $this->assertSame('new-value', $frontend->load($id));

            $this->assertTrue($frontend->clean(
                CacheConstants::CLEANING_MODE_MATCHING_TAG,
                ['INTEGRATION_OLD_TAG']
            ));
            $this->assertSame('new-value', $frontend->load($id));

            $this->assertTrue($frontend->clean(
                CacheConstants::CLEANING_MODE_MATCHING_TAG,
                ['INTEGRATION_NEW_TAG']
            ));
            $this->assertFalse($frontend->load($id));
        } finally {
            $frontend->remove($id);
        }
    }
}
