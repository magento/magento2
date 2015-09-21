<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tools\Migration\Test\Unit\Acl\Db\Adapter;

require_once realpath(
    __DIR__ . '/../../../../../../../../../'
) . '/tools/Magento/Tools/Migration/Acl/Db/Adapter/Factory.php';
class FactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var array
     */
    protected $_config;

    protected function setUp()
    {
        $this->_config = ['dbname' => 'some_db_name', 'password' => '', 'username' => ''];
    }

    /**
     * @return array
     */
    public function getConnectionDataProvider()
    {
        return [['Magento\Framework\DB\Adapter\Pdo\Mysql'], [''], [null]];
    }

    /**
     * @param $adapterType
     * @dataProvider getConnectionDataProvider
     */
    public function testGetConnection($adapterType)
    {
        $connectionMock = $this->getMock('Magento\Framework\DB\Adapter\Pdo\Mysql', [], [], '', false);

        $objectManager = $this->getMock('Magento\Framework\ObjectManagerInterface');
        $objectManager->expects(
            $this->any()
        )->method(
            'create'
        )->with(
            $this->equalTo('Magento\Framework\DB\Adapter\Pdo\Mysql')
        )->will(
            $this->returnValue($connectionMock)
        );

        $factory = new \Magento\Tools\Migration\Acl\Db\Adapter\Factory($objectManager);
        $adapter = $factory->getConnection($this->_config, $adapterType);

        $this->assertInstanceOf('Magento\Framework\DB\Adapter\Pdo\Mysql', $adapter);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetConnectionWithInvalidType()
    {
        $adapterType = 'Magento\Framework\DataObject';
        $adapterMock = $this->getMock($adapterType, [], [], '', false);

        $objectManager = $this->getMock('Magento\Framework\ObjectManagerInterface');
        $objectManager->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            $this->equalTo($adapterType),
            $this->equalTo(['config' => $this->_config])
        )->will(
            $this->returnValue($adapterMock)
        );

        $factory = new \Magento\Tools\Migration\Acl\Db\Adapter\Factory($objectManager);
        $factory->getConnection($this->_config, $adapterType);
    }
}
