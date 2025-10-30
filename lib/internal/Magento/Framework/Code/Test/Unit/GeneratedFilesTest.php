<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Code\Test\Unit;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Code\GeneratedFiles;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\RuntimeException;
use Magento\Framework\Filesystem\Directory\WriteFactory;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Lock\Backend\FileLock;

class GeneratedFilesTest extends TestCase
{
    /**
     * @var DirectoryList|MockObject
     */
    private $directoryList;

    /**
     * @var WriteInterface|MockObject
     */
    private $writeInterface;

    /**
     * @var WriteFactory|MockObject
     */
    private $writeFactory;

    /**
     * @var FileLock|MockObject
     */
    private $lockManager;

    /**
     * @var GeneratedFiles
     */
    private $model;

    /**
     * @var string
     */
    private $pathGeneratedCode = '/var/www/magento/generated/code';

    /**
     * @var string
     */
    private $pathGeneratedMetadata = '/var/www/magento/generated/metadata';

    /**
     * @var string
     */
    private $pathVarCache = '/var/www/magento/generated/var/cache';

    /**
     * Setup mocks for tests
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->directoryList = $this->createMock(DirectoryList::class);
        $this->writeFactory = $this->createMock(WriteFactory::class);
        $this->lockManager = $this->createMock(FileLock::class);
        $this->writeInterface = $this->getMockForAbstractClass(WriteInterface::class);

        $this->directoryList->expects($this->any())->method('getPath')->willReturnMap(
            [
                [DirectoryList::GENERATED_CODE, $this->pathGeneratedCode],
                [DirectoryList::GENERATED_METADATA, $this->pathGeneratedMetadata],
                [DirectoryList::CACHE, $this->pathVarCache],
            ]
        );
        $this->writeInterface->expects($this->any())->method('getRelativePath')->willReturnMap(
            [
                [$this->pathGeneratedCode, $this->pathGeneratedCode],
                [$this->pathGeneratedMetadata, $this->pathGeneratedMetadata],
                [$this->pathVarCache, $this->pathVarCache],
            ]
        );
        $this->writeInterface->expects($this->any())->method('isDirectory')->willReturnMap(
            [
                [$this->pathGeneratedCode, true],
                [$this->pathGeneratedMetadata, true],
                [$this->pathVarCache, true],
            ]
        );

        $this->writeFactory->expects($this->once())->method('create')->willReturn($this->writeInterface);

        $this->model = new GeneratedFiles(
            $this->directoryList,
            $this->writeFactory,
            $this->lockManager
        );
    }

    /**
     * Expect regeneration requested
     *
     * @param int $times
     * @return void
     */
    private function expectRegenerationRequested(int $times): void
    {
        $this->writeInterface->expects($this->exactly($times))->method('touch')->with(GeneratedFiles::REGENERATE_FLAG);
    }

    /**
     * Expect delete not requested
     *
     * @return void
     */
    private function expectDeleteNotRequested(): void
    {
        $this->writeInterface->expects($this->never())->method('delete');
    }

    /**
     * Expect flag present
     *
     * @param int $times
     * @param bool $flagPresent
     * @return void
     */
    private function expectFlagPresent(int $times, bool $flagPresent): void
    {
        $this->writeInterface->expects($this->exactly($times))
            ->method('isExist')
            ->with(GeneratedFiles::REGENERATE_FLAG)
            ->willReturn($flagPresent);
    }

    /**
     * Expect process locked
     *
     * @param int $times
     * @param bool|null $processLocked
     * @return void
     */
    private function expectProcessLocked(int $times, bool $processLocked = false): void
    {
        $this->lockManager->expects($this->exactly($times))
            ->method('isLocked')
            ->with(GeneratedFiles::REGENERATE_LOCK)
            ->willReturn($processLocked);

        if ($processLocked) {
            $this->expectLockOperation(0);
            $this->expectUnlockOperation(0);
        }
    }

    /**
     * Expect lock operation
     *
     * @param int $times
     * @param bool|null $lockResult
     * @return void
     */
    private function expectLockOperation(int $times, ?bool $lockResult = null): void
    {
        $invocationMocker = $this->lockManager->expects($this->exactly($times))
            ->method('lock')
            ->with(GeneratedFiles::REGENERATE_LOCK, GeneratedFiles::REGENERATE_LOCK_TIMEOUT);

        if (null !== $lockResult) {
            $invocationMocker->willReturn($lockResult);
        }
    }

    /**
     * Expect unlock operation
     *
     * @param int $times
     * @param bool|null $unlockResult
     * @return void
     */
    private function expectUnlockOperation(int $times, ?bool $unlockResult = null): void
    {
        $invocationMocker = $this->lockManager->expects($this->exactly($times))
            ->method('unlock')
            ->with(GeneratedFiles::REGENERATE_LOCK);

        if (null !== $unlockResult) {
            $invocationMocker->willReturn($unlockResult);
        }
    }

    /**
     * Expect no action performed, it does not execute any statement inside if condition
     *
     * @return void
     */
    private function expectNoActionPerformed(): void
    {
        $this->expectDeleteNotRequested();
        $this->expectRegenerationRequested(0);
        $this->expectUnlockOperation(0);
    }

    /**
     * Test request regeneration
     *
     * @test
     * @return void
     */
    public function itRequestsRegenerationProperly()
    {
        $this->expectRegenerationRequested(1);
        $this->model->requestRegeneration();
    }

    /**
     * It does not clean generated files if no flag is present
     *
     * @test
     * @return void
     */
    public function itDoesNotCleanGeneratedFilesIfNoFlagIsPresent()
    {
        $this->expectFlagPresent(1, false);
        $this->expectProcessLocked(0);
        $this->expectNoActionPerformed();
        $this->model->cleanGeneratedFiles();
    }

    /**
     * It does not clean generated files if process is locked
     *
     * @test
     * @return void
     */
    public function itDoesNotCleanGeneratedFilesIfProcessIsLocked()
    {
        $this->expectFlagPresent(1, true);
        $this->expectProcessLocked(1, true);
        $this->expectNoActionPerformed();
        $this->model->cleanGeneratedFiles();
    }

    /**
     * It does not clean generated files when checking flag exists due to exceptions
     *
     * @test
     * @param string $exceptionClassName
     * @return void
     *
     * @dataProvider itDoesNotCleanGeneratedFilesDueToExceptionsDataProvider
     */
    public function itDoesNotCleanGeneratedFilesWhenCheckingFlagExistsDueToExceptions(
        string $exceptionClassName
    ) {
        // Configure write interface to throw exception upon flag existence check
        $this->writeInterface->expects($this->any())
            ->method('isExist')
            ->with(GeneratedFiles::REGENERATE_FLAG)
            ->willThrowException(new $exceptionClassName(__('Some error has occurred.')));

        $this->expectProcessLocked(0);
        $this->expectNoActionPerformed();
        $this->model->cleanGeneratedFiles();
    }

    /**
     * It does not clean generated files when checking process lock due to exceptions
     *
     * @test
     * @param string $exceptionClassName
     * @return void
     *
     * @dataProvider itDoesNotCleanGeneratedFilesDueToExceptionsDataProvider
     */
    public function itDoesNotCleanGeneratedFilesWhenCheckingProcessLockDueToExceptions(
        string $exceptionClassName
    ) {
        $this->expectFlagPresent(1, true);

        // Configure lock to throw exception upon process lock check
        $this->lockManager->expects($this->any())
            ->method('isLocked')
            ->with(GeneratedFiles::REGENERATE_LOCK)
            ->willThrowException(new $exceptionClassName(__('Some error has occurred.')));

        $this->expectNoActionPerformed();
        $this->model->cleanGeneratedFiles();
    }

    /**
     * It does not clean generated files due to exceptions in allowed check data provider
     *
     * @return array
     */
    public static function itDoesNotCleanGeneratedFilesDueToExceptionsDataProvider()
    {
        return [
            RuntimeException::class => [RuntimeException::class],
            FileSystemException::class => [FileSystemException::class],
        ];
    }

    /**
     * It does not clean generated files if process lock is not acquired
     *
     * @test
     * @return void
     */
    public function itDoesNotCleanGeneratedFilesIfProcessLockIsNotAcquired()
    {
        $this->expectFlagPresent(1, true);
        $this->expectProcessLocked(1, false);

        // Expect lock manager try to lock, but fail without exception
        $this->lockManager->expects($this->once())->method('lock')->with(
            GeneratedFiles::REGENERATE_LOCK,
            GeneratedFiles::REGENERATE_LOCK_TIMEOUT
        )->willReturn(false);

        $this->expectNoActionPerformed();
        $this->model->cleanGeneratedFiles();
    }

    /**
     * It does not clean generated files if process lock is not acquired due to exception
     *
     * @test
     * @return void
     */
    public function itDoesNotCleanGeneratedFilesIfProcessLockIsNotAcquiredDueToException()
    {
        $this->expectFlagPresent(1, true);
        $this->expectProcessLocked(1, false);

        // Expect lock manager try to lock, but fail with runtime exception
        $this->lockManager->expects($this->once())->method('lock')->with(
            GeneratedFiles::REGENERATE_LOCK,
            GeneratedFiles::REGENERATE_LOCK_TIMEOUT
        )->willThrowException(new RuntimeException(__('Cannot acquire a lock.')));

        $this->expectNoActionPerformed();
        $this->model->cleanGeneratedFiles();
    }

    /**
     * It cleans generated files properly, when no errors or exceptions raised
     *
     * @test
     * @return void
     */
    public function itCleansGeneratedFilesProperly()
    {
        $this->expectFlagPresent(1, true);
        $this->expectProcessLocked(1, false);
        $this->expectLockOperation(1, true);

        $this->writeInterface->expects($this->exactly(4))->method('delete')
            ->willReturnCallback(
                function ($arg) {
                    if ($arg == GeneratedFiles::REGENERATE_FLAG ||
                        $arg == $this->pathGeneratedCode ||
                        $arg == $this->pathGeneratedMetadata ||
                        $arg == $this->pathVarCache) {
                        return null;
                    }
                }
            );

        $this->expectRegenerationRequested(0);
        $this->expectUnlockOperation(1, true);

        $this->model->cleanGeneratedFiles();
    }

    /**
     * It requests regeneration and unlock upon FileSystemException
     *
     * @test
     * @return void
     */
    public function itRequestsRegenerationAndUnlockUponFileSystemException()
    {
        $this->expectFlagPresent(1, true);
        $this->expectProcessLocked(1, false);
        $this->expectLockOperation(1, true);

        $this->writeInterface->expects($this->any())->method('delete')->willThrowException(
            new FileSystemException(__('Some error has occurred.'))
        );

        $this->expectRegenerationRequested(1);
        $this->expectUnlockOperation(1, true);

        $this->model->cleanGeneratedFiles();
    }
}
