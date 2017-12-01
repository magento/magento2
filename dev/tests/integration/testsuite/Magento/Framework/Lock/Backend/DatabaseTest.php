<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * \Magento\Framework\Lock\Backend\Database test case
 */
namespace Magento\Framework\Lock\Backend;

class DatabaseTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Lock\Backend\Database
     */
    private $model;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->model = $this->objectManager->create(\Magento\Framework\Lock\Backend\Database::class);
    }

    public function testLockAndRelease()
    {
        $name = 'test_lock';

        $this->assertFalse($this->model->isLocked($name));

        $this->assertTrue($this->model->acquireLock($name));
        $this->assertTrue($this->model->isLocked($name));

        $this->assertTrue($this->model->releaseLock($name));
        $this->assertFalse($this->model->isLocked($name));
    }

    public function testReleaseLockWithoutExistingLock()
    {
        $name = 'test_lock';

        $this->assertFalse($this->model->isLocked($name));
        $this->assertFalse($this->model->releaseLock($name));
    }
}
