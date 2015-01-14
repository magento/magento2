<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tools\Migration\Acl\Db\Adapter;

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
    public function getAdapterDataProvider()
    {
        return [['Magento\Framework\DB\Adapter\Pdo\Mysql'], [''], [null]];
    }

    /**
     * @param $adapterType
     * @dataProvider getAdapterDataProvider
     */
    public function testGetAdapter($adapterType)
    {
        $adapterMock = $this->getMock('Magento\Framework\DB\Adapter\Pdo\Mysql', [], [], '', false);

        $objectManager = $this->getMock('Magento\Framework\ObjectManagerInterface');
        $objectManager->expects(
            $this->any()
        )->method(
            'create'
        )->with(
            $this->equalTo('Magento\Framework\DB\Adapter\Pdo\Mysql')
        )->will(
            $this->returnValue($adapterMock)
        );

        $factory = new \Magento\Tools\Migration\Acl\Db\Adapter\Factory($objectManager);
        $adapter = $factory->getAdapter($this->_config, $adapterType);

        $this->assertInstanceOf('Zend_Db_Adapter_Abstract', $adapter);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetAdapterWithInvalidType()
    {
        $adapterType = 'Magento\Framework\Object';
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
        $factory->getAdapter($this->_config, $adapterType);
    }
}
