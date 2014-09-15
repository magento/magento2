<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Framework\Backup;

require_once __DIR__ . '/_files/Fs.php';
require_once __DIR__ . '/_files/Helper.php';
require_once __DIR__ . '/_files/io.php';

class MediaTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\App\Filesystem
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

    protected function setUp()
    {
        $this->_backupDbMock = $this->getMock('Magento\Framework\Backup\Db', array(), array(), '', false);
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

        $this->_filesystemMock = $this->getMock('Magento\Framework\App\Filesystem', array(), array(), '', false);
        $this->_backupFactoryMock = $this->getMock('Magento\Framework\Backup\Factory', array(), array(), '', false);
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

        $model = new \Magento\Framework\Backup\Media($this->_filesystemMock, $this->_backupFactoryMock);
        $model->setRootDir($rootDir);
        $model->setBackupsDir($rootDir);
        $model->{$action}();
        $this->assertTrue($model->getIsSuccess());

        $this->assertTrue($model->{$action}());

        $ignorePaths = $model->getIgnorePaths();

        $rootDir = str_replace('\\', '/', $rootDir);

        foreach ($ignorePaths as &$path) {
            if (strpos($path, '~tmp-') || strpos($path, '_media')) {
                unlink($path);
            }
            $path = str_replace('\\', '/', $path);
        }

        $this->assertTrue(in_array($rootDir, $ignorePaths));
        $this->assertTrue(in_array($rootDir . '/code', $ignorePaths));
        $this->assertTrue(in_array($rootDir . '/var/log', $ignorePaths));
    }

    /**
     * @return array
     */
    public static function actionProvider()
    {
        return array(array('create'), array('rollback'));
    }
}
