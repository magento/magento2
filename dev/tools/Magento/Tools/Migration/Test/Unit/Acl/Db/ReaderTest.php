<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tools\Migration\Test\Unit\Acl\Db;

use Magento\Framework\DB\Select;

require_once realpath(__DIR__ . '/../../../../../../../../') . '/tools/Magento/Tools/Migration/Acl/Db/Reader.php';
class ReaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Tools\Migration\Acl\Db\Reader
     */
    protected $_model;

    /**
     * DB adapter
     *
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_adapterMock;

    protected function setUp()
    {
        $this->_adapterMock = $this->getMock(
            'Magento\Framework\DB\Adapter\Pdo\Mysql',
            ['select', 'fetchPairs'],
            [],
            '',
            false
        );
        $this->_model = new \Magento\Tools\Migration\Acl\Db\Reader($this->_adapterMock, 'dummy');
    }

    protected function tearDown()
    {
        unset($this->_model);
        unset($this->_adapterMock);
    }

    public function testFetchAll()
    {
        $expected = ['all' => 10, 'catalog' => 100];
        $selectMock = $this->getMock(Select::class, [], [], '', false);
        $this->_adapterMock->expects($this->once())->method('select')->will($this->returnValue($selectMock));
        $selectMock->expects($this->once())->method('from')->will($this->returnSelf());
        $selectMock->expects($this->once())->method('columns')->will($this->returnSelf());
        $selectMock->expects($this->once())->method('group')->will($this->returnSelf());
        $this->_adapterMock->expects($this->once())->method('fetchPairs')->will($this->returnValue($expected));
        $actual = $this->_model->fetchAll();
        $this->assertEquals($expected, $actual);
    }
}
