<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\JwtUserToken\Model\ResourceModel;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\JwtUserToken\Api\RevokedRepositoryInterface;
use Magento\JwtUserToken\Api\Data\Revoked;

/**
 * DB repo.
 */
class RevokedRepository implements RevokedRepositoryInterface
{
    private const TABLE = 'jwt_auth_revoked';

    /**
     * @var ResourceConnection
     */
    private $connection;

    /**
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(ResourceConnection $resourceConnection) {
        $this->connection = $resourceConnection;
    }

    /**
     * @inheritDoc
     */
    public function saveRevoked(Revoked $revoked): void
    {
        $conn = $this->getAdapter();
        $table = $conn->getTableName(self::TABLE);

        $conn->insertOnDuplicate($table, $revoked->getData(), array_keys($revoked->getData()));
    }

    /**
     * @inheritDoc
     */
    public function findRevoked(int $userTypeId, int $userId): ?Revoked
    {
        $conn = $this->getAdapter();
        $table = $conn->getTableName(self::TABLE);

        $data = $conn->fetchRow(
            $conn->select()
                ->from($table)
                ->where('user_type_id = ?', $userTypeId)
                ->where('user_id = ?', $userId)
        );

        if ($data) {
            return new Revoked(null, null, null, $data);
        }
        return null;
    }

    private function getAdapter(): AdapterInterface
    {
        return $this->connection->getConnection(ResourceConnection::DEFAULT_CONNECTION);
    }
}
