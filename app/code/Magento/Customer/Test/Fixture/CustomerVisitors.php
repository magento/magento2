<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Fixture;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DataObject;
use Magento\Framework\Stdlib\DateTime;
use Magento\TestFramework\Fixture\RevertibleDataFixtureInterface;

/**
 * Create multiple customer_visitor rows for a given customer and optional guest rows.
 */
class CustomerVisitors implements RevertibleDataFixtureInterface
{
    /**
     * @var ResourceConnection
     */
    private ResourceConnection $resource;

    /**
     * @var DateTime
     */
    private DateTime $dateTime;

    /**
     * @param ResourceConnection $resource
     * @param DateTime $dateTime
     */
    public function __construct(
        ResourceConnection $resource,
        DateTime $dateTime
    ) {
        $this->resource = $resource;
        $this->dateTime = $dateTime;
    }

    /**
     * Apply fixture.
     *
     * Supported $data keys:
     * - customer_id (int) required
     * - count (int) number of sessions for customer (default 3)
     * - include_guest (bool) add one guest row (default true)
     * - spacing (int) seconds spacing between sessions (default 10)
     * - base_time (int) unix timestamp base (default time())
     */
    public function apply(array $data = []): ?DataObject
    {
        $customerId = (int)($data['customer_id'] ?? 0);
        if ($customerId <= 0) {
            throw new \InvalidArgumentException('customer_id is required and must be > 0');
        }
        $count = (int)($data['count'] ?? 3);
        $includeGuest = array_key_exists('include_guest', $data) ? (bool)$data['include_guest'] : true;
        $spacing = (int)($data['spacing'] ?? 10);
        $baseTime = (int)($data['base_time'] ?? time());

        $connection = $this->resource->getConnection();
        $table = $this->resource->getTableName('customer_visitor');

        $visitorIds = [];
        // Insert oldest first and newest last so the last inserted ID corresponds to the latest session.
        for ($offset = $count; $offset >= 1; $offset--) {
            $timestamp = $baseTime - ($spacing * $offset);
            $connection->insert($table, [
                'customer_id' => $customerId,
                'last_visit_at' => $this->dateTime->formatDate($timestamp),
            ]);
            $visitorIds[] = (int)$connection->lastInsertId($table);
        }

        $guestIds = [];
        if ($includeGuest) {
            $connection->insert($table, [
                'customer_id' => null,
                'last_visit_at' => $this->dateTime->formatDate($baseTime - (int)floor($spacing / 2)),
            ]);
            $guestIds[] = (int)$connection->lastInsertId($table);
        }

        return new DataObject([
            'visitor_ids' => $visitorIds,
            'guest_ids' => $guestIds,
        ]);
    }

    /**
     * Revert fixture.
     */
    public function revert(DataObject $data): void
    {
        $connection = $this->resource->getConnection();
        $table = $this->resource->getTableName('customer_visitor');

        $visitorIds = (array)$data->getData('visitor_ids');
        $guestIds = (array)$data->getData('guest_ids');
        $allIds = array_values(array_filter(array_merge($visitorIds, $guestIds)));
        if ($allIds) {
            $connection->delete($table, ['visitor_id IN (?)' => $allIds]);
        }
    }
}
