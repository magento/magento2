<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Cache\Zend;

use Magento\TestFramework\Cache\CacheConfigurationProvider;
use Magento\TestFramework\Cache\CacheFrontendTestCase;
use Magento\Framework\Cache\Backend\RemoteSynchronizedCache;

/**
 * Verifies legacy RemoteSynchronizedCache re-indexes tags when the payload is unchanged.
 *
 * @magentoAppIsolation enabled
 * @magentoDbIsolation enabled
 */
class ZendIdenticalPayloadRetagTest extends CacheFrontendTestCase
{
    public function testIdenticalPayloadWithChangedTagsReindexesRemoteTags(): void
    {
        $configuration = CacheConfigurationProvider::provide()['zend-l1-l2'];
        $options = $configuration[1]['backend_options'];
        $options['local_backend_options']['cache_dir'] = BP . '/var/cache/integration_retag_zend';
        if (!is_dir($options['local_backend_options']['cache_dir'])) {
            mkdir($options['local_backend_options']['cache_dir'], 0777, true);
        }
        $backend = new RemoteSynchronizedCache($options);
        $id = $this->cacheId('zend-l1-l2', 'identical', 'integration_retag');
        $remote = $this->getRemoteBackend($backend);

        try {
            $this->assertTrue($backend->save('same-value', $id, ['OLD_TAG'], 3600));
            $beforeTags = $remote->getMetadatas($id);
            $this->assertContains(
                $id,
                $backend->getIdsMatchingAnyTags(['OLD_TAG']),
                $this->diagnostic('before-retag', $id, $remote, $beforeTags, $backend)
            );

            $this->assertTrue($backend->save('same-value', $id, ['NEW_TAG'], 3600));
            $afterTags = $remote->getMetadatas($id);
            $oldIds = $backend->getIdsMatchingAnyTags(['OLD_TAG']);
            $newIds = $backend->getIdsMatchingAnyTags(['NEW_TAG']);
            $diagnostic = $this->diagnostic('after-retag', $id, $remote, $afterTags, $backend, $oldIds, $newIds);
            $this->assertNotContains($id, $oldIds, $diagnostic);
            $this->assertContains($id, $newIds, $diagnostic);
            $this->assertSame('same-value', $backend->load($id));
        } finally {
            $backend->remove($id);
        }
    }

    private function getRemoteBackend(object $backend): object
    {
        $reflection = new \ReflectionObject($backend);
        return $reflection->hasProperty('remote')
            ? $reflection->getProperty('remote')->getValue($backend)
            : $backend;
    }

    private function diagnostic(
        string $stage,
        string $id,
        object $remote,
        $metadata,
        object $backend,
        array $oldIds = [],
        array $newIds = []
    ): string {
        return json_encode([
            'stage' => $stage,
            'backend' => get_class($backend),
            'remote_backend' => get_class($remote),
            'id' => $id,
            'metadata' => $metadata,
            'old_tag_ids' => $oldIds,
            'new_tag_ids' => $newIds,
        ], JSON_THROW_ON_ERROR);
    }
}
