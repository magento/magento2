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
 * @category    Magento
 * @package     \Magento\Backup
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Backup;

class MediaTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\App\Dir
     */
    protected $_dirMock;

    /**
     * @var \Magento\Backup\Factory
     */
    protected $_backupFactoryMock;

    protected function setUp()
    {
        $this->_dirMock = $this->getMock('Magento\App\Dir', array(), array(), '', false);
        $this->_backupFactoryMock = $this->getMock('Magento\Backup\Factory', array(), array(), '', false);
    }
    /**
     * @param string $action
     * @dataProvider actionProvider
     */
    public function testAction($action)
    {
        $snapshot = $this->getMock(
            'Magento\Backup\Snapshot',
            array('create', 'rollback', 'getDbBackupFilename'),
            array($this->_dirMock, $this->_backupFactoryMock)
        );
        $snapshot->expects($this->any())
            ->method('create')
            ->will($this->returnValue(true));
        $snapshot->expects($this->any())
            ->method('rollback')
            ->will($this->returnValue(true));
        $snapshot->expects($this->once())
            ->method('getDbBackupFilename')
            ->will($this->returnValue('var/backup/2.gz'));

        $rootDir = __DIR__ . DIRECTORY_SEPARATOR . '_files';

        $model = new \Magento\Backup\Media($snapshot);
        $model->setRootDir($rootDir);

        $this->assertTrue($model->$action());

        $this->assertEquals(
            array(
                $rootDir . DIRECTORY_SEPARATOR . 'code',
                $rootDir . DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR . 'tmp',
            ),
            $snapshot->getIgnorePaths()
        );
    }

    /**
     * @return array
     */
    public static function actionProvider()
    {
        return array(
            array('create'),
            array('rollback'),
        );
    }

    /**
     * @param string $method
     * @param $parameter
     * @dataProvider methodsProvider
     */
    public function testProxyMethod($method, $parameter)
    {
        $snapshot = $this->getMock('Magento\Backup\Snapshot',
            array($method),
            array($this->_dirMock, $this->_backupFactoryMock));
        $snapshot->expects($this->once())
            ->method($method)
            ->with($parameter)
            ->will($this->returnValue($snapshot));

        $model = new \Magento\Backup\Media($snapshot);
        $this->assertEquals($model, $model->$method($parameter));
    }

    /**
     * @return array
     */
    public function methodsProvider()
    {
        $snapshot = $this->getMock('Magento\Backup\Snapshot', array(), array(), '', false);
        return array(
            array('setBackupExtension', 'test'),
            array('setResourceModel', new \Magento\Backup\Media($snapshot)),
            array('setTime', 1),
            array('setBackupsDir', 'test/test'),
            array('addIgnorePaths', 'test/test'),
            array('setRootDir', 'test/test'),
        );
    }
}
