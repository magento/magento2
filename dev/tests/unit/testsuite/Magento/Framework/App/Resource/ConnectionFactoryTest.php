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
namespace Magento\Framework\App\Resource;

class ConnectionFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\App\Resource\ConnectionFactory
     */
    protected $model;

    /**
     * @var \Magento\Framework\ObjectManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManager;

    /**
     * @var \Magento\Framework\App\Arguments|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $localConfig;

    protected function setUp()
    {
        $this->objectManager = $this->getMock(
            '\Magento\Framework\ObjectManager',
            [],
            [],
            '',
            false
        );
        $this->localConfig = $this->getMock(
            '\Magento\Framework\App\Arguments',
            [],
            [],
            '',
            false
        );

        $this->model = new \Magento\Framework\App\Resource\ConnectionFactory(
            $this->objectManager,
            $this->localConfig
        );
    }

    /**
     * @param array $config
     * @dataProvider dataProviderCreateNoActiveConfig
     */
    public function testCreateNoActiveConfig($config)
    {
        $this->localConfig->expects(
            $this->once()
        )->method(
            'getConnection'
        )->with(
            'connection_name'
        )->will(
            $this->returnValue($config)
        );

        $this->assertNull($this->model->create('connection_name'));
    }

    /**
     * @return array
     */
    public function dataProviderCreateNoActiveConfig()
    {
        return [
            [null, null],
            [['value'], null],
            [['active' => 0], null],
        ];
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Adapter is not set for connection "connection_name"
     */
    public function testCreateNoAdapter()
    {
        $config = [
            'active' => 1,
        ];

        $this->localConfig->expects(
            $this->once()
        )->method(
            'getConnection'
        )->with(
            'connection_name'
        )->will(
            $this->returnValue($config)
        );

        $this->model->create('connection_name');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Trying to create wrong connection adapter
     */
    public function testCreateNoWrongAdapter()
    {
        $config = [
            'active' => 1,
            'adapter' => 'StdClass',
        ];

        $this->localConfig->expects(
            $this->once()
        )->method(
            'getConnection'
        )->with(
            'connection_name'
        )->will(
            $this->returnValue($config)
        );

        $adapterMock = $this->getMock('StdClass');

        $this->objectManager->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            'StdClass',
            $config
        )->will(
            $this->returnValue($adapterMock)
        );

        $this->model->create('connection_name');
    }

    public function testCreate()
    {
        $config = [
            'active' => 1,
            'adapter' => 'Magento\Framework\App\Resource\ConnectionAdapterInterface',
        ];

        $this->localConfig->expects(
            $this->once()
        )->method(
            'getConnection'
        )->with(
            'connection_name'
        )->will(
            $this->returnValue($config)
        );

        $adapterMock = $this->getMock('Magento\Framework\App\Resource\ConnectionAdapterInterface');

        $this->objectManager->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            'Magento\Framework\App\Resource\ConnectionAdapterInterface',
            $config
        )->will(
            $this->returnValue($adapterMock)
        );

        $connectionMock = $this->getMock('Magento\Framework\DB\Adapter\AdapterInterface');

        $adapterMock->expects(
            $this->once()
        )->method(
            'getConnection'
        )->will(
            $this->returnValue($connectionMock)
        );

        $this->assertEquals($connectionMock, $this->model->create('connection_name'));
    }
}
