<?php
/************************************************************************
 *
 * Copyright 2024 Adobe
 * All Rights Reserved.
 *
 * NOTICE: All information contained herein is, and remains
 * the property of Adobe and its suppliers, if any. The intellectual
 * and technical concepts contained herein are proprietary to Adobe
 * and its suppliers and are protected by all applicable intellectual
 * property laws, including trade secret and copyright laws.
 * Dissemination of this information or reproduction of this material
 * is strictly forbidden unless prior written permission is obtained
 * from Adobe.
 * ***********************************************************************
 */
declare(strict_types=1);

namespace Magento\Quote\Model;

use Magento\Framework\App\ResourceConnection;

/**
 * @inheritDoc
 */
class QuoteIdMutex implements QuoteMutexInterface
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
            throw new \InvalidArgumentException('Quote ids must be provided');
        }

        $connection = $this->resourceConnection->getConnection();
        $connection->beginTransaction();
        $query = $connection->select()
            ->from($this->resourceConnection->getTableName('quote'), 'entity_id')
            ->where('entity_id IN (?)', $maskedIds)
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
