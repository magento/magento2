<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Backup\Test\Unit;

use Magento\Framework\Backup\Db;
use Magento\Framework\Backup\Factory;
use Magento\Framework\Backup\Filesystem\Rollback\Fs;
use Magento\Framework\Backup\Nomedia;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/_files/io.php';

class NomediaTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var Filesystem
     */
    protected $_filesystemMock;

    /**
     * @var Factory
     */
    protected $_backupFactoryMock;

    /**
     * @var Db
     */
    protected $_backupDbMock;

    /**
     * @var Fs
     */
    private $fsMock;

    public static function setUpBeforeClass(): void
    {
        require __DIR__ . '/_files/app_dirs.php';
    }

    public static function tearDownAfterClass(): void
    {
        require __DIR__ . '/_files/app_dirs_rollback.php';
    }

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->_backupDbMock = $this->createMock(Db::class);
        $this->_backupDbMock->expects($this->any())->method('setBackupExtension')->willReturnSelf();

        $this->_backupDbMock->expects($this->any())->method('setTime')->willReturnSelf();

        $this->_backupDbMock->expects($this->any())->method('setBackupsDir')->willReturnSelf();

        $this->_backupDbMock->expects($this->any())->method('setResourceModel')->willReturnSelf();

        $this->_backupDbMock->expects(
            $this->any()
        )->method(
            'getBackupPath'
        )->willReturn(
            '\unexistingpath'
        );

        $this->_backupDbMock->expects($this->any())->method('create')->willReturn(true);

        $this->_filesystemMock = $this->createMock(Filesystem::class);
        $dirMock = $this->getMockForAbstractClass(WriteInterface::class);
        $this->_filesystemMock->expects($this->any())
            ->method('getDirectoryWrite')
            ->willReturn($dirMock);

        $this->_backupFactoryMock = $this->createMock(Factory::class);
        $this->_backupFactoryMock->expects(
            $this->once()
        )->method(
            'create'
        )->willReturn(
            $this->_backupDbMock
        );

        $this->fsMock = $this->createMock(Fs::class);
    }

    /**
     * @param string $action
     * @dataProvider actionProvider
     */
    public function testAction($action)
    {
        $this->_backupFactoryMock->expects($this->once())->method('create');

        $rootDir = TESTS_TEMP_DIR . '/Magento/Backup/data';

        $model = $this->objectManager->getObject(
            Nomedia::class,
            [
                'filesystem' => $this->_filesystemMock,
                'backupFactory' => $this->_backupFactoryMock,
                'rollBackFs' => $this->fsMock,
            ]
        );
        $model->setRootDir($rootDir);
        $model->setBackupsDir($rootDir);
        $model->{$action}();
        $this->assertTrue($model->getIsSuccess());

        $this->assertEquals([$rootDir, $rootDir . '/media', $rootDir . '/pub/media'], $model->getIgnorePaths());
    }

    /**
     * @return array
     */
    public static function actionProvider()
    {
        return [['create'], ['rollback']];
    }
}
