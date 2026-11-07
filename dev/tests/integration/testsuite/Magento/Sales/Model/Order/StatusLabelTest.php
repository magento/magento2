<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sales\Model\Order;

use Magento\Framework\App\Area;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\ObjectManager\ResetAfterRequestInterface;
use Magento\Sales\Model\ResourceModel\Order\Status as StatusResource;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Tests status label cache queries and persistence invalidation.
 */
#[DbIsolation(true)]
class StatusLabelTest extends TestCase
{
    /**
     * @inheritDoc
     */
    protected function tearDown(): void
    {
        $labels = Bootstrap::getObjectManager()->get(StatusLabel::class);
        if ($labels instanceof ResetAfterRequestInterface) {
            $labels->_resetState();
        }
        parent::tearDown();
    }

    /**
     * @return void
     */
    public function testOrderListWorkloadLoadsEachStatusOnce(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $storeId = (int)$objectManager->get(StoreManagerInterface::class)->getStore()->getId();
        $this->saveStatus('cache_one', 'One', [$storeId => 'Store one']);
        $this->saveStatus('cache_two', 'Two', []);
        $labels = $objectManager->get(StatusLabel::class);
        $resource = $objectManager->get(ResourceConnection::class);
        $connection = $objectManager->get(StatusResource::class)->getConnection();
        $profiler = $connection->getProfiler();
        $enabled = $profiler->getEnabled();
        $profiler->clear();
        $profiler->setEnabled(true);
        try {
            for ($order = 0; $order < 200; $order++) {
                for ($comment = 0; $comment < 10; $comment++) {
                    self::assertSame(
                        'Store one',
                        $labels->getStatusFrontendLabel('cache_one', Area::AREA_FRONTEND, $storeId)
                    );
                    self::assertSame(
                        'Two',
                        $labels->getStatusFrontendLabel('cache_two', Area::AREA_FRONTEND, $storeId)
                    );
                }
            }
            $queries = $profiler->getQueryProfiles(\Zend_Db_Profiler::SELECT) ?: [];
            $statusQueries = 0;
            $labelQueries = 0;
            $statusTable = $resource->getTableName('sales_order_status');
            $labelTable = $resource->getTableName('sales_order_status_label');
            foreach ($queries as $query) {
                $sql = $query->getQuery();
                $statusQueries += (int)str_contains($sql, '`' . $statusTable . '`');
                $labelQueries += (int)str_contains($sql, '`' . $labelTable . '`');
            }
            self::assertSame(2, $statusQueries);
            self::assertSame(2, $labelQueries);
        } finally {
            $profiler->setEnabled($enabled);
            $profiler->clear();
        }
    }

    /**
     * @return void
     */
    public function testSaveDeleteAndRecreateRefreshLabels(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $storeId = (int)$objectManager->get(StoreManagerInterface::class)->getStore()->getId();
        $labels = $objectManager->get(StatusLabel::class);
        $status = $this->saveStatus('cache_custom', 'Original', [$storeId => 'Store original']);
        self::assertSame('Original', $labels->getStatusFrontendLabel('cache_custom', Area::AREA_ADMINHTML));
        self::assertSame(
            'Store original',
            $labels->getStatusFrontendLabel('cache_custom', Area::AREA_FRONTEND, $storeId)
        );

        $status->setData('label', 'Updated');
        $status->setData('store_labels', [$storeId => 'Store updated']);
        $resource = $objectManager->get(StatusResource::class);
        $resource->save($status);
        self::assertSame('Updated', $labels->getStatusFrontendLabel('cache_custom', Area::AREA_ADMINHTML));
        self::assertSame(
            'Store updated',
            $labels->getStatusFrontendLabel('cache_custom', Area::AREA_FRONTEND, $storeId)
        );

        $status->setData('store_labels', []);
        $resource->save($status);
        self::assertSame('Updated', $labels->getStatusFrontendLabel('cache_custom', Area::AREA_FRONTEND, $storeId));
        $resource->delete($status);
        self::assertNull($labels->getStatusFrontendLabel('cache_custom', Area::AREA_ADMINHTML));
        self::assertSame('', $labels->getStatusFrontendLabel('cache_custom', Area::AREA_FRONTEND, $storeId));
        $this->saveStatus('cache_custom', 'Recreated', [$storeId => 'Store recreated']);
        self::assertSame('Recreated', $labels->getStatusFrontendLabel('cache_custom', Area::AREA_ADMINHTML));
        self::assertSame(
            'Store recreated',
            $labels->getStatusFrontendLabel('cache_custom', Area::AREA_FRONTEND, $storeId)
        );
    }

    /**
     * Create a status with store labels.
     *
     * @param string $code
     * @param string $label
     * @param array $storeLabels
     * @return Status
     */
    private function saveStatus(string $code, string $label, array $storeLabels): Status
    {
        $objectManager = Bootstrap::getObjectManager();
        $status = $objectManager->create(Status::class);
        $status->setData(['status' => $code, 'label' => $label, 'store_labels' => $storeLabels]);
        $objectManager->get(StatusResource::class)->save($status);
        return $status;
    }
}
