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
    protected $_resourceResolver;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_moduleListMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_resourceSetupMock;

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
        $this->_moduleListMock = $this->getMock('Magento\Framework\Module\ModuleListInterface');
        $this->_resourceResolver = $this->getMock('Magento\Framework\Module\ResourceResolverInterface');
        $this->_resourceSetupMock = $this->getMock(
            'Magento\Catalog\Model\Resource\Setup',
            [],
            [],
            '',
            false
        );

        $this->_moduleListMock->expects($this->any())->method('getNames')->will($this->returnValue(['Test_Module']));

        $resourceList = ['catalog_setup'];
        $this->_resourceResolver->expects($this->any())
            ->method('getResourceList')
            ->with('Test_Module')
            ->will($this->returnValue($resourceList));

        $this->_dbVersionInfo = $this->getMock('Magento\Framework\Module\DbVersionInfo', [], [], '', false);

        $this->_model = new \Magento\Framework\Module\Updater(
            $this->_factoryMock,
            $this->_moduleListMock,
            $this->_resourceResolver,
            $this->_dbVersionInfo
        );
    }

    public function testUpdateData()
    {
        $this->_dbVersionInfo->expects($this->once())
            ->method('isDataUpToDate')
            ->with('Test_Module', 'catalog_setup')
            ->will(
                $this->returnValue(false)
            );
        $this->_factoryMock->expects($this->any())
            ->method('create')
            ->with('catalog_setup', 'Test_Module')
            ->will($this->returnValue($this->_resourceSetupMock));
        $this->_resourceSetupMock->expects($this->once())
            ->method('applyDataUpdates');

        $this->_model->updateData();
    }

    public function testUpdateDataNoUpdates()
    {
        $this->_dbVersionInfo->expects($this->once())
            ->method('isDataUpToDate')
            ->with('Test_Module', 'catalog_setup')
            ->will($this->returnValue(true));
        $this->_factoryMock->expects($this->never())
            ->method('create');

        $this->_model->updateData();
    }
}
