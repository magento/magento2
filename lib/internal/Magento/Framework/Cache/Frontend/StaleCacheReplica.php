<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Cache\Frontend;


use Magento\Framework\Cache\FrontendInterface;
use Magento\Framework\Lock\LockManagerInterface;

class StaleCacheReplica implements FrontendInterface
{
    /**
     * @var FrontendInterface
     */
    private $masterCache;

    /**
     * @var FrontendInterface
     */
    private $slaveCache;

    /**
     * @var LockManagerInterface
     */
    private $lockManager;

    /**
     * @var string
     */
    private $lockId;

    /**
     * @var array
     */
    private $masterOnlyIdentifiers;


    public function __construct(
        FrontendInterface $masterCache,
        FrontendInterface $slaveCache,
        LockManagerInterface $lockManager,
        string $lockId,
        array $masterOnlyIdentifiers = []
    ) {
        $this->masterCache = $masterCache;
        $this->slaveCache = $slaveCache;
        $this->lockManager = $lockManager;
        $this->lockId = $lockId;
        $this->masterOnlyIdentifiers = $masterOnlyIdentifiers;
    }

    /**
     * {@inheritDoc}
     */
    public function test($identifier)
    {
        // TODO: Implement test() method.
    }

    /**
     * {@inheritDoc}
     */
    public function load($identifier)
    {
        $cachedData = $this->masterCache->load($identifier);

        if ($cachedData !== false) {
            return $cachedData;
        }

        if (!in_array($identifier, $this->masterOnlyIdentifiers)
            && $this->lockManager->isLocked($this->lockId)) {
            return $this->slaveCache->load($identifier);
        }

        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function save($data, $identifier, array $tags = [], $lifeTime = null)
    {
        $result = $this->masterCache->save($data, $identifier, $tags, $lifeTime);
        $this->slaveCache->save($data, $identifier);

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function remove($identifier)
    {
        return $this->masterCache->remove($identifier);
    }

    /**
     * {@inheritDoc}
     */
    public function clean($mode = \Zend_Cache::CLEANING_MODE_ALL, array $tags = [])
    {
        return $this->masterCache->clean($mode, $tags);
    }

    /**
     * {@inheritDoc}
     */
    public function getBackend()
    {
        return $this->masterCache->getBackend();
    }

    /**
     * {@inheritDoc}
     */
    public function getLowLevelFrontend()
    {
        return $this->masterCache->getLowLevelFrontend();
    }
}
