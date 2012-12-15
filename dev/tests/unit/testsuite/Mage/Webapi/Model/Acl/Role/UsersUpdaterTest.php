<?php
/**
 * Test class for Mage_Webapi_Model_Acl_Role_UsersUpdater
 *
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
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Webapi_Model_Acl_Role_UsersUpdaterTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Magento_Test_Helper_ObjectManager
     */
    protected $_helper;

    /**
     * @var Mage_Backend_Helper_Data|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_backendHelper;

    /**
     * @var Mage_Core_Controller_Request_Http|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_request;

    /**
     * @var Mage_Webapi_Model_Resource_Acl_User_Collection|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_collection;

    protected function setUp()
    {
        $this->_helper = new Magento_Test_Helper_ObjectManager($this);

        $this->_backendHelper = $this->getMockBuilder('Mage_Backend_Helper_Data')
            ->disableOriginalConstructor()
            ->setMethods(array('prepareFilterString'))
            ->getMock();
        $this->_backendHelper->expects($this->any())->method('prepareFilterString')->will($this->returnArgument(0));

        $this->_request = $this->getMockBuilder('Mage_Core_Controller_Request_Http')
            ->disableOriginalConstructor()
            ->getMock();
        $this->_collection = $this->getMockBuilder('Mage_Webapi_Model_Resource_Acl_User_Collection')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @dataProvider updateDataProvider
     * @param int $roleId
     * @param array $filters
     * @param bool $isAjax
     * @param mixed $param
     */
    public function testUpdate($roleId, $filters, $isAjax, $param)
    {
        $this->_request->expects($this->any())->method('getParam')->will($this->returnValueMap(array(
            array('role_id', null, $roleId),
            array('filter', '', $filters),
        )));
        $this->_request->expects($this->any())->method('isAjax')->will($this->returnValue($isAjax));

        if ($param) {
            $this->_collection->expects($this->once())->method('addFieldToFilter')->with('role_id', $param);
        } else {
            $this->_collection->expects($this->never())->method('addFieldToFilter');
        }

        /** @var Mage_Webapi_Model_Acl_Role_UsersUpdater $model */
        $model = $this->_helper->getModel('Mage_Webapi_Model_Acl_Role_UsersUpdater', array(
            'request' => $this->_request,
            'backendHelper' => $this->_backendHelper
        ));
        $this->assertEquals($this->_collection, $model->update($this->_collection));
    }

    /**
     * @return array
     */
    public function updateDataProvider()
    {
        return array(
            'Yes' => array(
                3,
                array('in_role_users' => 1),
                true,
                3
            ),
            'No' => array(
                4,
                array('in_role_users' => 0),
                true,
                array(
                    array('neq' => 4),
                    array('is' => 'NULL')
                )
            ),
            'Any' => array(
                5,
                array(),
                true,
                null
            ),
            'Yes_on_ajax' => array(
                6,
                array(),
                false,
                6
            ),
            'Any_without_role_id' => array(
                null,
                array('in_role_users' => 1),
                true,
                null
            )
        );
    }
}
