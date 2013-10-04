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
 * @category    Magento
 * @package     Magento_User
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\User\Model\Acl\Loader;

class RuleTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\User\Model\Acl\Loader\Rule
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_resourceMock;

    /**
     * @var \Magento\Core\Model\Acl\RootResource
     */
    protected $_rootResourceMock;

    protected function setUp()
    {
        $this->_resourceMock = $this->getMock('Magento\Core\Model\Resource', array(), array(), '', false, false);
        $this->_rootResourceMock = new \Magento\Core\Model\Acl\RootResource('Magento_Adminhtml::all');
        $this->_model = new \Magento\User\Model\Acl\Loader\Rule(
            $this->_rootResourceMock,
            $this->_resourceMock
        );
    }

    public function testPopulateAcl()
    {
        $this->_resourceMock->expects($this->any())->method('getTable')->will($this->returnArgument(1));

        $selectMock = $this->getMock('Magento\DB\Select', array(), array(), '', false);
        $selectMock->expects($this->any())
            ->method('from')
            ->will($this->returnValue($selectMock));

        $adapterMock = $this->getMock('Magento\DB\Adapter\Pdo\Mysql', array(), array(), '', false);
        $adapterMock->expects($this->once())
            ->method('select')
            ->will($this->returnValue($selectMock));
        $adapterMock->expects($this->once())
            ->method('fetchAll')
            ->will($this->returnValue(array(
            array(
                'role_id' => 1,
                'role_type' => 'G',
                'resource_id' => 'Magento_Adminhtml::all',
                'permission' => 'allow'
            ),
            array('role_id' => 2, 'role_type' => 'U', 'resource_id' => 1, 'permission' => 'allow'),
            array('role_id' => 3, 'role_type' => 'U', 'resource_id' => 1, 'permission' => 'deny'),
        )));

        $this->_resourceMock->expects($this->once())
            ->method('getConnection')
            ->will($this->returnValue($adapterMock));

        $aclMock = $this->getMock('Magento\Acl');
        $aclMock->expects($this->any())->method('has')->will($this->returnValue(true));
        $aclMock->expects($this->at(1))->method('allow')->with('G1', null, null);
        $aclMock->expects($this->at(2))->method('allow')->with('G1', 'Magento_Adminhtml::all', null);
        $aclMock->expects($this->at(4))->method('allow')->with('U2', 1, null);
        $aclMock->expects($this->at(6))->method('deny')->with('U3', 1, null);

        $this->_model->populateAcl($aclMock);
    }
}
