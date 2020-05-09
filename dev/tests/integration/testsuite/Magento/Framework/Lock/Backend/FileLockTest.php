<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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

    protected function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->model = $this->objectManager->create(
            \Magento\Framework\Lock\Backend\FileLock::class,
            ['path' => '/tmp']
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
}
