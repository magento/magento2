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
 * @package     Magento
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Magento_Test_Db_MysqlTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    protected $_varDir;

    /**
     * @var Magento_Test_Db_Mysql
     */
    protected $_model;

    /**
     * @var string
     */
    protected $_commandPrefix;

    protected function setUp()
    {
        $this->_varDir  = $this->_varDir  = sys_get_temp_dir();
        $this->_model = $this->getMock(
            'Magento_Test_Db_Mysql',
            array('_exec', '_createScript'),
            array('host', 'user', 'pass', 'schema', $this->_varDir)
        );
        $this->_commandPrefix = '--protocol=TCP --host=' . escapeshellarg('host')
            . ' --user=' . escapeshellarg('user') . ' --password=' . escapeshellarg('pass');
    }

    public function testCleanup()
    {
        $this->_model->expects($this->once())
            ->method('_createScript')
            ->with(
                $this->_varDir . DIRECTORY_SEPARATOR . 'drop_create_database.sql',
                'DROP DATABASE `schema`; CREATE DATABASE `schema`'
            );

        $command = 'mysql ' . $this->_commandPrefix . ' ' . escapeshellarg('schema') . ' < '
            . escapeshellarg($this->_varDir . DIRECTORY_SEPARATOR . 'drop_create_database.sql');
        $this->_model->expects($this->once())
            ->method('_exec')
            ->with($this->equalTo($command));
        $this->_model->cleanup();
    }

    public function testCreateBackup()
    {
        $command = 'mysqldump ' . $this->_commandPrefix . ' --skip-opt --quick --single-transaction --create-options'
            . ' --disable-keys --set-charset --extended-insert --hex-blob --insert-ignore --add-drop-table '
            . escapeshellarg('schema') . ' > ' . escapeshellarg($this->_varDir . DIRECTORY_SEPARATOR . 'test.sql');
        $this->_model->expects($this->once())
            ->method('_exec')
            ->with($this->equalTo($command));

        $this->_model->createBackup('test');
    }

    public function testRestoreBackup()
    {
        $command = 'mysql ' . $this->_commandPrefix . ' ' . escapeshellarg('schema') . ' < '
            . escapeshellarg($this->_varDir . DIRECTORY_SEPARATOR . 'test.sql');
        $this->_model->expects($this->once())
            ->method('_exec')
            ->with($this->equalTo($command));

        $this->_model->restoreBackup('test');
    }
}
