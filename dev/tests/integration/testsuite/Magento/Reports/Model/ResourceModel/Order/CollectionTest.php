<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Reports\Model\ResourceModel\Order;

use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class CollectionTest extends TestCase
{
    /**
     * @param int[] $storeIds
     * @return void
     */
    #[DbIsolation(true)]
    #[DataProvider('storeIdsProvider')]
    public function testSetStoreIdsProducesExecutableQuery(array $storeIds): void
    {
        /** @var Collection $collection */
        $collection = Bootstrap::getObjectManager()->create(Collection::class);
        $collection->setStoreIds($storeIds);

        $sql = (string)$collection->getSelect();
        $this->assertStringContainsString(' AS `profit`', $sql);
        $this->assertStringNotContainsString('`SUM(', $sql);
        $this->assertIsArray($collection->getConnection()->fetchAll($collection->getSelect()));
    }

    /**
     * @return array
     */
    public static function storeIdsProvider(): array
    {
        return [
            'no stores' => [[]],
            'single store' => [[1]],
        ];
    }
}
