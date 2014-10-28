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
namespace Magento\Test\Tools\Migration\Acl\Db\Adapter;


require_once realpath(
    __DIR__ . '/../../../../../../../../../../'
) . '/tools/Magento/Tools/Migration/Acl/Db/Adapter/Factory.php';
class FactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var array
     */
    protected $_config;

    protected function setUp()
    {
        $this->_config = array('dbname' => 'some_db_name', 'password' => '', 'username' => '');
    }

    /**
     * @return array
     */
    public function getAdapterDataProvider()
    {
        return array(array('Magento\Framework\DB\Adapter\Pdo\Mysql'), array(''), array(null));
    }

    /**
     * @param $adapterType
     * @dataProvider getAdapterDataProvider
     */
    public function testGetAdapter($adapterType)
    {
        $adapterMock = $this->getMock('Magento\Framework\DB\Adapter\Pdo\Mysql', array(), array(), '', false);

        $objectManager = $this->getMock('Magento\Framework\ObjectManager');
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
        $adapterMock = $this->getMock($adapterType, array(), array(), '', false);

        $objectManager = $this->getMock('Magento\Framework\ObjectManager');
        $objectManager->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            $this->equalTo($adapterType),
            $this->equalTo(array('config' => $this->_config))
        )->will(
            $this->returnValue($adapterMock)
        );

        $factory = new \Magento\Tools\Migration\Acl\Db\Adapter\Factory($objectManager);
        $factory->getAdapter($this->_config, $adapterType);
    }
}
