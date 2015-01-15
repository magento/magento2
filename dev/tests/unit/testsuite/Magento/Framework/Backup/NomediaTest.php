<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Backup;

require_once __DIR__ . '/_files/Fs.php';
require_once __DIR__ . '/_files/Helper.php';
require_once __DIR__ . '/_files/io.php';

class NomediaTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $_filesystemMock;

    /**
     * @var \Magento\Framework\Backup\Factory
     */
    protected $_backupFactoryMock;

    /**
     * @var \Magento\Framework\Backup\Db
     */
    protected $_backupDbMock;

    public static function setUpBeforeClass()
    {
        require __DIR__ . '/_files/app_dirs.php';
    }

    public static function tearDownAfterClass()
    {
        require __DIR__ . '/_files/app_dirs_rollback.php';
    }

    protected function setUp()
    {
        $this->_backupDbMock = $this->getMock('Magento\Framework\Backup\Db', [], [], '', false);
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

        $this->_filesystemMock = $this->getMock('Magento\Framework\Filesystem', [], [], '', false);
        $dirMock = $this->getMockForAbstractClass('\Magento\Framework\Filesystem\Directory\WriteInterface');
        $this->_filesystemMock->expects($this->any())
            ->method('getDirectoryWrite')
            ->will($this->returnValue($dirMock));

        $this->_backupFactoryMock = $this->getMock('Magento\Framework\Backup\Factory', [], [], '', false);
        $this->_backupFactoryMock->expects(
            $this->once()
        )->method(
            'create'
        )->will(
            $this->returnValue($this->_backupDbMock)
        );
    }

    /**
     * @param string $action
     * @dataProvider actionProvider
     */
    public function testAction($action)
    {
        $this->_backupFactoryMock->expects($this->once())->method('create');

        $rootDir = TESTS_TEMP_DIR . '/Magento/Backup/data';

        $model = new \Magento\Framework\Backup\Nomedia($this->_filesystemMock, $this->_backupFactoryMock);
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
