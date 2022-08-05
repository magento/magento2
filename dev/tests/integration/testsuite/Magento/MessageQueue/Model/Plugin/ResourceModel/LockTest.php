<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\MessageQueue\Model\Plugin\ResourceModel;

use Magento\TestFramework\Event\Magento;

class LockTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var \Magento\Framework\MessageQueue\LockInterface
     */
    protected $lock;

    /**
     * @var \Magento\Framework\MessageQueue\Lock\WriterInterface
     */
    protected $writer;

    /**
     * @var \Magento\Framework\MessageQueue\Lock\ReaderInterface
     */
    protected $reader;

    protected function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        $this->lock = $this->objectManager->get(\Magento\Framework\MessageQueue\LockInterface::class);
        $this->writer = $this->objectManager->get(\Magento\Framework\MessageQueue\Lock\WriterInterface::class);
        $this->reader = $this->objectManager->get(\Magento\Framework\MessageQueue\Lock\ReaderInterface::class);
    }

    /**
     * Test to ensure Queue Lock Table is cleared when maintenance mode transitions from on to off.
     *
     * @return void
     */
    public function testLockClearedByMaintenanceModeOff()
    {
        /** @var $maintenanceMode \Magento\Framework\App\MaintenanceMode */
        $maintenanceMode = $this->objectManager->get(\Magento\Framework\App\MaintenanceMode::class);
        // md5() here is not for cryptographic use.
        // phpcs:ignore Magento2.Security.InsecureFunction
        $code = md5('consumer.name-1');
        $this->lock->setMessageCode($code);
        $this->writer->saveLock($this->lock);
        $this->reader->read($this->lock, $code);
        $id = $this->lock->getId();
        $maintenanceMode->set(true);
        $maintenanceMode->set(false);
        $this->reader->read($this->lock, $code);
        $emptyId = $this->lock->getId();

        $this->assertGreaterThanOrEqual('1', $id);
        $this->assertEmpty($emptyId);
    }
}
