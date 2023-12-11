<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Lock\Backend;

use Magento\Framework\Lock\Backend\Zookeeper as ZookeeperLock;
use Magento\Framework\Lock\LockBackendFactory;
use Magento\Framework\Config\File\ConfigFilePool;
use Magento\Framework\App\DeploymentConfig\FileReader;
use Magento\Framework\Stdlib\ArrayManager;

/**
 * \Magento\Framework\Lock\Backend\Zookeeper test case
 */
class ZookeeperTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var FileReader
     */
    private $configReader;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var LockBackendFactory
     */
    private $lockBackendFactory;

    /**
     * @var ArrayManager
     */
    private $arrayManager;

    /**
     * @var ZookeeperLock
     */
    private $model;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        if (!extension_loaded('zookeeper')) {
            $this->markTestSkipped('php extension Zookeeper is not installed.');
        }

        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->configReader = $this->objectManager->get(FileReader::class);
        $this->lockBackendFactory = $this->objectManager->create(LockBackendFactory::class);
        $this->arrayManager = $this->objectManager->create(ArrayManager::class);
        $config = $this->configReader->load(ConfigFilePool::APP_ENV);

        if ($this->arrayManager->get('lock/provider', $config) !== 'zookeeper') {
            $this->markTestSkipped('Zookeeper is not configured during installation.');
        }

        $this->model = $this->lockBackendFactory->create();
        $this->assertInstanceOf(ZookeeperLock::class, $this->model);
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
