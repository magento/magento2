<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Cache\Frontend\Adapter;

use Magento\Framework\Cache\FrontendInterface;

/**
 * InMemory cache adapter for tests
 * and long running applications
 */
class InMemoryCache implements FrontendInterface
{
    const CLEAN_MATCHING_TAG = \Zend_Cache::CLEANING_MODE_MATCHING_TAG;
    const CLEAN_MATCHING_ANY_TAG = \Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG;
    const CLEAN_ALL = \Zend_Cache::CLEANING_MODE_ALL;

    /** @var string[] */
    private $data = [];

    /** @var string[][] */
    private $dataTags = [];

    /** @var float[] */
    private $dataExpires = [];

    /**
     * {@inheritDoc}
     */
    public function test($identifier)
    {
        return isset($this->data[$identifier]);
    }

    /**
     * {@inheritDoc}
     */
    public function load($identifier)
    {
        $this->invalidateIfExpired($identifier);

        return $this->data[$identifier] ?? false;
    }

    /**
     * {@inheritDoc}
     */
    public function save($data, $identifier, array $tags = [], $lifeTime = null)
    {
        $this->data[$identifier] = $data;
        $this->dataTags[$identifier] = $tags;

        if ($lifeTime !== null) {
            $this->dataExpires[$identifier] = microtime(true) + $lifeTime;
        }

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function remove($identifier)
    {
        unset($this->data[$identifier], $this->dataTags[$identifier], $this->dataExpires[$identifier]);
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function clean($mode = self::CLEAN_ALL, array $tags = [])
    {
        if ($mode === self::CLEAN_ALL) {
            $this->data = [];
            return true;
        }

        foreach ($this->dataTags as $identifier => $entryTags) {
            $matchedTags = array_intersect($entryTags, $tags);
            $isTagMatched = count($matchedTags) > 0;

            if ($mode === self::CLEAN_MATCHING_TAG) {
                $isTagMatched = $isTagMatched && count($matchedTags) === count($tags);
            }

            if ($isTagMatched) {
                $this->remove($identifier);
            }
        }

        return true;
    }

    /**
     * This cache frontend does not have any backend, as it is a simple in memory implementation
     *
     * @throws \RuntimeException
     */
    public function getBackend()
    {
        throw new \RuntimeException('$this->getBackend() is not supported by InMemoryCache cache frontend.');
    }

    /**
     * This cache frontend does not have any low level frontend, as it is a simple in memory implementation
     *
     * @throws \RuntimeException
     */
    public function getLowLevelFrontend()
    {
        throw new \RuntimeException('$this->getLowLevelFrontend() is not supported by InMemoryCache cache frontend.');
    }

    /**
     * Invalidates cache key if it was expired
     *
     * @param string $identifier
     */
    private function invalidateIfExpired(string $identifier)
    {
        if (isset($this->dataExpires[$identifier])
            && microtime(true) > $this->dataExpires[$identifier]) {
            $this->remove($identifier);
        }
    }
}
