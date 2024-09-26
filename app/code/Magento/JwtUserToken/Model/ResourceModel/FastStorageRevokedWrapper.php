<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\JwtUserToken\Model\ResourceModel;

use Magento\Authorization\Model\UserContextInterface;
use Magento\JwtUserToken\Api\ConfigReaderInterface;
use Magento\JwtUserToken\Api\Data\Revoked;
use Magento\JwtUserToken\Api\RevokedRepositoryInterface;
use Magento\Framework\App\CacheInterface;

/**
 * Stores revoked token data in a fast storage on top of other storage type.
 */
class FastStorageRevokedWrapper implements RevokedRepositoryInterface
{
    private const CACHE_ID = 'jwt-user-token:revoked:%d:%d';

    /**
     * @var RevokedRepositoryInterface
     */
    private $slowRepo;

    /**
     * @var CacheInterface
     */
    private $cache;

    /**
     * @var ConfigReaderInterface
     */
    private $jwtConfig;

    /**
     * @param RevokedRepositoryInterface $slowRepo
     * @param CacheInterface $cache
     * @param ConfigReaderInterface $configReader
     */
    public function __construct(
        RevokedRepositoryInterface $slowRepo,
        CacheInterface $cache,
        ConfigReaderInterface $configReader
    ) {
        $this->slowRepo = $slowRepo;
        $this->cache = $cache;
        $this->jwtConfig = $configReader;
    }

    /**
     * @inheritDoc
     */
    public function saveRevoked(Revoked $revoked): void
    {
        $this->cacheData($revoked);
        $this->slowRepo->saveRevoked($revoked);
    }

    /**
     * @inheritDoc
     */
    public function findRevoked(int $userTypeId, int $userId): ?Revoked
    {
        $cached = $this->cache->load(sprintf(self::CACHE_ID, $userTypeId, $userId));
        if ($cached) {
            return new Revoked($userTypeId, $userId, (int) $cached);
        }

        $revoked = $this->slowRepo->findRevoked($userTypeId, $userId);
        if ($revoked) {
            $this->cacheData($revoked);
        }

        return $revoked;
    }

    private function cacheData(Revoked $revoked): void
    {
        //Caching revoked token data for the duration exceeding any previously issued tokens TTL.
        if ($revoked->getUserTypeId() === UserContextInterface::USER_TYPE_ADMIN) {
            $ttlMin = $this->jwtConfig->getAdminTtl();
        } else {
            $ttlMin = $this->jwtConfig->getCustomerTtl();
        }
        $this->cache->save(
            (string) $revoked->getBeforeTimestamp(),
            sprintf(self::CACHE_ID, $revoked->getUserTypeId(), $revoked->getUserId()),
            [],
            $ttlMin * 60
        );
    }
}
