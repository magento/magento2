<?php
/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Lock\Backend;

/**
 * \Magento\Framework\Lock\Backend\File test case
 */
class FileLockTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Lock\Backend\FileLock
     */
    private $model;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /** @var string */
    private string $lockPath;

    protected function setUp(): void
    {
        $this->lockPath = '/tmp/magento-test-locks';
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->model = $this->objectManager->create(
            \Magento\Framework\Lock\Backend\FileLock::class,
            ['path' => $this->lockPath]
        );
    }

    public function testLockAndUnlock()
    {
        $name = 'test_lock';

        $this->assertFalse($this->model->isLocked($name));

        $this->assertTrue($this->model->lock($name));
        $this->assertTrue($this->model->isLocked($name));
        $this->assertFalse($this->model->lock($name, 2));

        $this->assertTrue($this->model->unlock($name));
        $this->assertFalse($this->model->isLocked($name));
    }

    public function testUnlockWithoutExistingLock()
    {
        $name = 'test_lock';

        $this->assertFalse($this->model->isLocked($name));
        $this->assertFalse($this->model->unlock($name));
    }

    public function testCleanupOldFile()
    {
        $name = 'test_lock';

        $this->assertTrue($this->model->lock($name));
        $this->assertTrue($this->model->unlock($name));

        touch(sprintf('%s/%s', $this->lockPath, $name), strtotime('30 hours ago'));

        $this->assertEquals(1, $this->model->cleanupOldLocks());
    }

    public function testDontCleanupNewFile()
    {
        $name = 'test_lock';

        $this->assertTrue($this->model->lock($name));
        $this->assertTrue($this->model->unlock($name));

        touch(sprintf('%s/%s', $this->lockPath, $name), strtotime('1 hour ago'));

        $this->assertEquals(0, $this->model->cleanupOldLocks());
    }
}
