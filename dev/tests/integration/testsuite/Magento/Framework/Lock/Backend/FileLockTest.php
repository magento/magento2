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

    /**
     * @dataProvider lockNameProvider
     */
    public function testLockAndUnlock(string $name)
    {
        $this->assertFalse($this->model->isLocked($name));

        $this->assertTrue($this->model->lock($name));
        $this->assertTrue($this->model->isLocked($name));
        $this->assertFalse($this->model->lock($name, 2));

        $this->assertTrue($this->model->unlock($name));
        $this->assertFalse($this->model->isLocked($name));
    }

    /**
     * @dataProvider lockNameProvider
     */
    public function testUnlockWithoutExistingLock(string $name)
    {
        $this->assertFalse($this->model->isLocked($name));
        $this->assertFalse($this->model->unlock($name));
    }

    /**
     * @dataProvider lockNameProvider
     */
    public function testCleanupOldFile(string $name)
    {
        $this->assertTrue($this->model->lock($name));
        $this->assertTrue($this->model->unlock($name));

        touch($this->getFilePath($name), strtotime('30 hours ago'));

        $this->assertEquals(1, $this->model->cleanupOldLocks());
    }

    /**
     * @dataProvider lockNameProvider
     */
    public function testDontCleanupNewFile(string $name)
    {
        $this->assertTrue($this->model->lock($name));
        $this->assertTrue($this->model->unlock($name));

        touch($this->getFilePath($name), strtotime('1 hour ago'));

        $this->assertEquals(0, $this->model->cleanupOldLocks());
    }

    private function getFilePath(string $name): string
    {
        return $this->lockPath . '/' . rawurlencode($name);
    }

    public static function lockNameProvider(): array
    {
        return [
            'standard_alphanumeric' => ['test_lock_name'],
            'with_unix_forbidden_chars' => ['test/lock/name'],
            'with_windows_forbidden_chars' => ['*t<e>s:t"l/o\\c|k?_name'],
            'with_spaces' => ['test lock name']
        ];
    }
}
