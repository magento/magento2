<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Test\Unit\Model\Config\Structure\Element\Group;

class ProxyTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Config\Model\Config\Structure\Element\Group\Proxy
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_objectManagerMock;

    protected function setUp()
    {
        $this->_objectManagerMock = $this->createMock(\Magento\Framework\ObjectManagerInterface::class);
        $this->_model = new \Magento\Config\Model\Config\Structure\Element\Group\Proxy($this->_objectManagerMock);
    }

    protected function tearDown()
    {
        unset($this->_model);
        unset($this->_objectManagerMock);
    }

    public function testProxyInitializesProxiedObjectOnFirstCall()
    {
        $groupMock = $this->createMock(\Magento\Config\Model\Config\Structure\Element\Group::class);

        $groupMock->expects($this->once())->method('setData');
        $groupMock->expects($this->once())->method('getId')->will($this->returnValue('group_id'));
        $this->_objectManagerMock->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            \Magento\Config\Model\Config\Structure\Element\Group::class
        )->will(
            $this->returnValue($groupMock)
        );

        $this->_model->setData([], '');
        $this->assertEquals('group_id', $this->_model->getId());
    }
}
