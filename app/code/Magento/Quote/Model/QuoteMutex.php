<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Model;

use Magento\Framework\App\ResourceConnection;

/**
 * @inheritDoc
 */
class QuoteMutex implements QuoteMutexInterface
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        ResourceConnection $resourceConnection
    ) {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * @inheritDoc
     */
    public function execute(array $maskedIds, callable $callable, array $args = [])
    {
        if (empty($maskedIds)) {
            throw new \InvalidArgumentException('Quote masked ids must be provided');
        }

        $connection = $this->resourceConnection->getConnection();
        $connection->beginTransaction();
        $query = $connection->select()
            ->from($this->resourceConnection->getTableName('quote_id_mask'), 'entity_id')
            ->where('masked_id IN (?)', $maskedIds)
            ->forUpdate(true);
        $connection->query($query);

        try {
            $result = $callable(...$args);
            $this->resourceConnection->getConnection()->commit();
            return $result;
        } catch (\Throwable $e) {
            $this->resourceConnection->getConnection()->rollBack();
            throw $e;
        }
    }
}
