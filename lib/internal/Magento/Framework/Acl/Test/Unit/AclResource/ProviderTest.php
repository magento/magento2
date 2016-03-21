<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Acl\Test\Unit\AclResource;

class ProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Acl\AclResource\Provider
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_configReaderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_treeBuilderMock;

    protected function setUp()
    {
        $this->_configReaderMock = $this->getMock('Magento\Framework\Config\ReaderInterface');
        $this->_treeBuilderMock = $this->getMock(
            'Magento\Framework\Acl\AclResource\TreeBuilder',
            [],
            [],
            '',
            false
        );
        $this->_model = new \Magento\Framework\Acl\AclResource\Provider(
            $this->_configReaderMock,
            $this->_treeBuilderMock
        );
    }

    public function testGetIfAclResourcesExist()
    {
        $aclResourceConfig['config']['acl']['resources'] = ['ExpectedValue'];
        $this->_configReaderMock->expects($this->once())->method('read')->will($this->returnValue($aclResourceConfig));
        $this->_treeBuilderMock->expects($this->once())->method('build')->will($this->returnValue('ExpectedResult'));
        $this->assertEquals('ExpectedResult', $this->_model->getAclResources());
    }

    public function testGetIfAclResourcesEmpty()
    {
        $this->_configReaderMock->expects($this->once())->method('read')->will($this->returnValue([]));
        $this->_treeBuilderMock->expects($this->never())->method('build');
        $this->assertEquals([], $this->_model->getAclResources());
    }
}
