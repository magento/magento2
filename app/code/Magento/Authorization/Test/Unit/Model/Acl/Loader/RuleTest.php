<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Authorization\Test\Unit\Model\Acl\Loader;

class RuleTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Authorization\Model\Acl\Loader\Rule
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_resourceMock;

    /**
     * @var \Magento\Framework\Acl\RootResource
     */
    protected $_rootResourceMock;

    protected function setUp()
    {
        $this->_resourceMock = $this->getMock('Magento\Framework\App\ResourceConnection', [], [], '', false, false);
        $this->_rootResourceMock = new \Magento\Framework\Acl\RootResource('Magento_Backend::all');
        $this->_model = new \Magento\Authorization\Model\Acl\Loader\Rule(
            $this->_rootResourceMock,
            $this->_resourceMock
        );
    }

    public function testPopulateAcl()
    {
        $this->_resourceMock->expects($this->any())->method('getTable')->will($this->returnArgument(1));

        $selectMock = $this->getMock('Magento\Framework\DB\Select', [], [], '', false);
        $selectMock->expects($this->any())->method('from')->will($this->returnValue($selectMock));

        $connectionMock = $this->getMock('Magento\Framework\DB\Adapter\Pdo\Mysql', [], [], '', false);
        $connectionMock->expects($this->once())->method('select')->will($this->returnValue($selectMock));
        $connectionMock->expects(
            $this->once()
        )->method(
            'fetchAll'
        )->will(
            $this->returnValue(
                [
                    ['role_id' => 1, 'resource_id' => 'Magento_Backend::all', 'permission' => 'allow'],
                    ['role_id' => 2, 'resource_id' => 1, 'permission' => 'allow'],
                    ['role_id' => 3, 'resource_id' => 1, 'permission' => 'deny'],
                ]
            )
        );

        $this->_resourceMock->expects($this->once())
            ->method('getConnection')
            ->will($this->returnValue($connectionMock));

        $aclMock = $this->getMock('Magento\Framework\Acl');
        $aclMock->expects($this->any())->method('has')->will($this->returnValue(true));
        $aclMock->expects($this->at(1))->method('allow')->with('1', null, null);
        $aclMock->expects($this->at(2))->method('allow')->with('1', 'Magento_Backend::all', null);
        $aclMock->expects($this->at(4))->method('allow')->with('2', 1, null);
        $aclMock->expects($this->at(6))->method('deny')->with('3', 1, null);

        $this->_model->populateAcl($aclMock);
    }
}
