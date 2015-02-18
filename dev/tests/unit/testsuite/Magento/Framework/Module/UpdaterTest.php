<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Module;

class UpdaterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_factoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $dataSetup;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_moduleListMock;

    /**
     * @var \Magento\Framework\Module\DbVersionInfo|\PHPUnit_Framework_MockObject_MockObject
     */
    private $_dbVersionInfo;

    /**
     * @var \Magento\Framework\Module\Updater
     */
    protected $_model;

    protected function setUp()
    {
        $this->_factoryMock = $this->getMock(
            'Magento\Framework\Module\Updater\SetupFactory',
            [],
            [],
            '',
            false
        );
        $this->dataSetup = $this->getMock('Magento\Setup\Module\DataSetup', [], [], '', false);
        $this->_moduleListMock = $this->getMock('Magento\Framework\Module\ModuleListInterface');
        $this->_moduleListMock
            ->expects($this->any())
            ->method('getNames')
            ->will($this->returnValue(['Test_Module']));
        $this->_moduleListMock
            ->expects($this->any())
            ->method('getOne')
            ->will($this->returnValue(['setup_version' => '2.0.0']));
        $this->_dbVersionInfo = $this->getMock('Magento\Framework\Module\DbVersionInfo', [], [], '', false);
        $this->_model = new \Magento\Framework\Module\Updater(
            $this->_factoryMock,
            $this->dataSetup,
            $this->_moduleListMock,
            $this->_dbVersionInfo
        );
    }

    public function testUpdateDataInstall()
    {
        $this->_dbVersionInfo
            ->expects($this->once())
            ->method('isDataUpToDate')
            ->with('Test_Module')
            ->will(
                $this->returnValue(false)
            );
        $resource = $this->getMock('Magento\Framework\Module\Resource', [], [], '', false);
        $resource
            ->expects($this->once())
            ->method('getDataVersion')
            ->will(
                $this->returnValue(false)
            );
        $this->_factoryMock
            ->expects($this->once())
            ->method('create')
            ->with('Test_Module', 'install')
            ->will($this->returnValue($this->getMock('Magento\Framework\Setup\InstallDataInterface')));
        $this->_model->updateData($resource);
    }

    public function testUpdateDataUpgrade()
    {
        $this->_dbVersionInfo
            ->expects($this->once())
            ->method('isDataUpToDate')
            ->with('Test_Module')
            ->will(
                $this->returnValue(false)
            );
        $resource = $this->getMock('Magento\Framework\Module\Resource', [], [], '', false);
        $resource
            ->expects($this->once())
            ->method('getDataVersion')
            ->will(
                $this->returnValue(true)
            );
        $this->_factoryMock
            ->expects($this->once())
            ->method('create')
            ->with('Test_Module', 'upgrade')
            ->will($this->returnValue($this->getMock('Magento\Framework\Setup\UpgradeDataInterface')));
        $this->_model->updateData($resource);
    }

    public function testUpdateDataNoUpdates()
    {
        $this->_dbVersionInfo
            ->expects($this->once())
            ->method('isDataUpToDate')
            ->with('Test_Module')
            ->will(
                $this->returnValue(true)
            );
        $resource = $this->getMock('Magento\Framework\Module\Resource', [], [], '', false);
        $this->_model->updateData($resource);
    }
}
