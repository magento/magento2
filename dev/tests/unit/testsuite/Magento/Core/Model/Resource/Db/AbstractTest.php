<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
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
        $this->_resource = $this->getMock('Magento\Framework\App\Resource', [], [], '', false, false);
        $this->_model = $this->getMock(
            'Magento\Framework\Model\Resource\Db\AbstractDb',
            ['_construct', '_getWriteAdapter'],
            [$this->_resource]
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
        $string = $this->getMock('Magento\Framework\Stdlib\String', [], [], '', false);
        $dateTime = $this->getMock('Magento\Framework\Stdlib\DateTime', null, [], '', true);
        $logger = $this->getMockForAbstractClass('Magento\Framework\DB\LoggerInterface');
        $connection = new \Magento\Framework\DB\Adapter\Pdo\Mysql(
            $string,
            $dateTime,
            $logger,
            ['dbname' => 'test_dbname', 'username' => 'test_username', 'password' => 'test_password']
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
