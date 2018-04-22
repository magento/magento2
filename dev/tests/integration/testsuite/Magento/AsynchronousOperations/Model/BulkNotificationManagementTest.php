<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AsynchronousOperations\Model;

use Magento\AsynchronousOperations\Api\Data\BulkSummaryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\ObjectManagerInterface;

class BulkNotificationManagementTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var BulkNotificationManagement
     */
    private $model;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();

        $this->model = $this->objectManager->create(
            BulkNotificationManagement::class
        );
    }

    /**
     * @magentoDataFixture Magento/AsynchronousOperations/_files/bulk.php
     */
    public function testAcknowledgeBulks()
    {
        $this->model->acknowledgeBulks(['bulk-uuid-5']);

        $acknowledgedBulks = $this->model->getAcknowledgedBulksByUser(1);
        $this->assertCount(1, $acknowledgedBulks);
        /** @var BulkSummaryInterface $acknowledgedBulk */
        $acknowledgedBulk = array_pop($acknowledgedBulks);
        $this->assertEquals('bulk-uuid-5', $acknowledgedBulk->getBulkId());
    }

    /**
     * @magentoDataFixture Magento/AsynchronousOperations/_files/acknowledged_bulk.php
     */
    public function testIgnoreBulks()
    {
        // 'bulk-uuid-5' and 'bulk-uuid-4' are marked as acknowledged in fixture
        $this->model->ignoreBulks(['bulk-uuid-5']);

        $acknowledgedBulks = $this->model->getAcknowledgedBulksByUser(1);
        $this->assertCount(1, $acknowledgedBulks);
        /** @var BulkSummaryInterface $acknowledgedBulk */
        $acknowledgedBulk = array_pop($acknowledgedBulks);
        $this->assertEquals('bulk-uuid-4', $acknowledgedBulk->getBulkId());
    }

    /**
     * @magentoDataFixture Magento/AsynchronousOperations/_files/acknowledged_bulk.php
     */
    public function testGetAcknowledgedBulks()
    {
        // 'bulk-uuid-5' and 'bulk-uuid-4' are marked as acknowledged in fixture
        $acknowledgedBulks = $this->model->getAcknowledgedBulksByUser(1);
        $this->assertCount(2, $acknowledgedBulks);
    }

    /**
     * @magentoDataFixture Magento/AsynchronousOperations/_files/acknowledged_bulk.php
     */
    public function testGetIgnoredBulks()
    {
        // 'bulk-uuid-5' and 'bulk-uuid-4' are marked as acknowledged in fixture. Fixture creates 5 bulks in total.
        $ignoredBulks = $this->model->getIgnoredBulksByUser(1);
        $this->assertCount(3, $ignoredBulks);
    }
}
