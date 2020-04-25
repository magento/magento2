<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Backup\Test\Unit;

use Magento\Framework\Backup\Db;
use Magento\Framework\Backup\Factory;
use Magento\Framework\Backup\Filesystem\Rollback\Fs;
use Magento\Framework\Backup\Media;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/_files/io.php';

class MediaTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var Filesystem|MockObject
     */
    protected $_filesystemMock;

    /**
     * @var Factory|MockObject
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
        $this->_backupDbMock->expects($this->any())->method('setBackupExtension')->will($this->returnSelf());

        $this->_backupDbMock->expects($this->any())->method('setTime')->will($this->returnSelf());

        $this->_backupDbMock->expects($this->any())->method('setBackupsDir')->will($this->returnSelf());

        $this->_backupDbMock->expects($this->any())->method('setResourceModel')->will($this->returnSelf());

        $this->_backupDbMock->expects(
            $this->any()
        )->method(
            'getBackupPath'
        )->will(
            $this->returnValue('\unexistingpath')
        );

        $this->_backupDbMock->expects($this->any())->method('create')->will($this->returnValue(true));

        $this->_filesystemMock = $this->createMock(Filesystem::class);
        $dirMock = $this->getMockForAbstractClass(WriteInterface::class);
        $this->_filesystemMock->expects($this->any())
            ->method('getDirectoryWrite')
            ->will($this->returnValue($dirMock));

        $this->_backupFactoryMock = $this->createMock(Factory::class);
        $this->_backupFactoryMock->expects(
            $this->once()
        )->method(
            'create'
        )->will(
            $this->returnValue($this->_backupDbMock)
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

        $rootDir = str_replace('\\', '/', TESTS_TEMP_DIR) . '/Magento/Backup/data';

        $model = $this->objectManager->getObject(
            Media::class,
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

        $this->assertTrue($model->{$action}());

        $ignorePaths = $model->getIgnorePaths();

        $expected = [
            $rootDir,
            $rootDir . '/app',
            $rootDir . '/var/log',
        ];
        $ignored = array_intersect($expected, $ignorePaths);
        sort($ignored);
        $this->assertEquals($expected, $ignored);
    }

    /**
     * @return array
     */
    public static function actionProvider()
    {
        return [['create'], ['rollback']];
    }
}
