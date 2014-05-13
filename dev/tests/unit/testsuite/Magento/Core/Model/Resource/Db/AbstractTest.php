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

/**
 * Test class for \Magento\Framework\Model\Resource\Db\AbstractDb.
 */
namespace Magento\Core\Model\Resource\Db;

class AbstractTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Model\Resource\Db\AbstractDb|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_model;

    /**
     * @var \Magento\Framework\App\Resource|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_resource;

    protected function setUp()
    {
        $this->_resource = $this->getMock('Magento\Framework\App\Resource', array('getConnection'), array(), '', false, false);
        $this->_model = $this->getMock(
            'Magento\Framework\Model\Resource\Db\AbstractDb',
            array('_construct', '_getWriteAdapter'),
            array($this->_resource)
        );
    }

    /**
     * Test that the model uses resource instance passed to the constructor
     */
    public function testConstructor()
    {
        /* Invariant: resource instance $this->_resource has been passed to the constructor in setUp() method */
        $this->_resource->expects($this->atLeastOnce())->method('getConnection')->with('core_read');
        $this->_model->getReadConnection();
    }

    /**
     * Test that the model detects a connection when it becomes active
     */
    public function testGetConnectionInMemoryCaching()
    {
        $filesystem = $this->getMock('Magento\Framework\App\Filesystem', array(), array(), '', false);
        $string = $this->getMock('Magento\Framework\Stdlib\String', array(), array(), '', false);
        $dateTime = $this->getMock('Magento\Framework\Stdlib\DateTime', null, array(), '', true);
        $connection = new \Magento\Framework\DB\Adapter\Pdo\Mysql(
            $filesystem,
            $string,
            $dateTime,
            array('dbname' => 'test_dbname', 'username' => 'test_username', 'password' => 'test_password')
        );
        $this->_resource->expects(
            $this->atLeastOnce()
        )->method(
            'getConnection'
        )->with(
            'core_read'
        )->will(
            $this->onConsecutiveCalls(false/*inactive connection*/, $connection/*active connection*/, false)
        );
        $this->assertFalse($this->_model->getReadConnection());
        $this->assertSame($connection, $this->_model->getReadConnection(), 'Inactive connection should not be cached');
        $this->assertSame($connection, $this->_model->getReadConnection(), 'Active connection should be cached');
    }
}
