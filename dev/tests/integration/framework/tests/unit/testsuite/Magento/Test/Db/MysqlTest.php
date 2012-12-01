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
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Magento_Test_Db_MysqlTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Magento_Shell|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_shell;

    /**
     * @var Magento_Test_Db_Mysql|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_model;

    protected function setUp()
    {
        $this->_shell = $this->getMock('Magento_Shell', array('execute'));
        $this->_model = $this->getMock(
            'Magento_Test_Db_Mysql',
            array('_createScript'),
            array('host', 'user', 'pass', 'schema', __DIR__, $this->_shell)
        );
    }

    protected function tearDown()
    {
        $this->_shell = null;
        $this->_model = null;
    }

    public function testCleanup()
    {
        $expectedSqlFile = __DIR__ . DIRECTORY_SEPARATOR . 'drop_create_database.sql';
        $this->_model
            ->expects($this->once())
            ->method('_createScript')
            ->with($expectedSqlFile, 'DROP DATABASE `schema`; CREATE DATABASE `schema`')
        ;
        $this->_shell
            ->expects($this->once())
            ->method('execute')
            ->with(
                'mysql --protocol=TCP --host=%s --user=%s --password=%s %s < %s',
                array('host', 'user', 'pass', 'schema', $expectedSqlFile)
            )
        ;
        $this->_model->cleanup();
    }
}
