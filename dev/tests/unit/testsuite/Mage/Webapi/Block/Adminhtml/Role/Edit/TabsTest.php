<?php
/**
 * Test class for Mage_Webapi_Block_Adminhtml_Role_Edit_Tabs
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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Webapi_Block_Adminhtml_Role_Edit_TabsTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Webapi_Block_Adminhtml_Role_Edit_Tabs
     */
    protected $_block;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject|Mage_Core_Model_Layout
     */
    protected $_layout;

    /**
     * @var Magento_Test_Helper_ObjectManager
     */
    protected $_helper;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject|Mage_Core_Controller_Request_Http
     */
    protected $_request;

    protected function setUp()
    {
        /** @var Mage_Backend_Model_Url|PHPUnit_Framework_MockObject_MockObject $urlBuilder */
        $urlBuilder = $this->getMockBuilder('Mage_Backend_Model_Url')
            ->disableOriginalConstructor()
            ->getMock();

        $this->_layout = $this->getMockBuilder('Mage_Core_Model_Layout')
            ->disableOriginalConstructor()
            ->setMethods(array('helper', 'getBlock'))
            ->getMock();

        $this->_request = $this->getMockBuilder('Mage_Core_Controller_Request_Http')
            ->getMock();

        $this->_helper = new Magento_Test_Helper_ObjectManager($this);
        $this->_block = $this->_helper->getBlock('Mage_Webapi_Block_Adminhtml_Role_Edit_Tabs', array(
            'urlBuilder' => $urlBuilder,
            'layout' => $this->_layout,
            'request' => $this->_request
        ));
    }

    /**
     * Test _construct method.
     */
    public function testConstruct()
    {
        $this->assertEquals('page_tabs', $this->_block->getId());
        $this->assertEquals('edit_form', $this->_block->getDestElementId());
        $this->assertEquals('Role Information', $this->_block->getTitle());
    }

    /**
     * Test for _beforeToHtml method.
     *
     * @dataProvider beforeToHtmlDataProvider
     * @param object $apiRole
     * @param array $expectedTabIds
     */
    public function testBeforeToHtml($apiRole, $expectedTabIds)
    {
        $this->_block->setApiRole($apiRole);

        $mainBlock = $this->_helper->getBlock('Mage_Core_Block_Text');
        $resourceBlock = $this->_helper->getBlock('Mage_Core_Block_Text');
        $userBlock = $this->_helper->getBlock('Mage_Core_Block_Text');

        $this->_layout->expects($this->any())
            ->method('getBlock')
            ->will($this->returnValueMap(array(
            array('webapi.role.edit.tab.main', $mainBlock),
            array('webapi.role.edit.tab.resource', $resourceBlock),
            array('webapi.role.edit.tab.users.grid', $userBlock),
        )));

        $this->_request->expects($this->any())->method('getParam')->will($this->returnValueMap(array(
            array('active_tab', null, 'main_section')
        )));

        // TODO: do checks using toHtml() when DI is implemented for abstract blocks
        $toHtmlMethod = new ReflectionMethod($this->_block, '_beforeToHtml');
        $toHtmlMethod->setAccessible(true);
        $toHtmlMethod ->invoke($this->_block);

        $this->assertEquals($expectedTabIds, $this->_block->getTabsIds());
        $this->assertEquals($apiRole, $mainBlock->getApiRole());
        $this->assertEquals($apiRole, $resourceBlock->getApiRole());
    }

    /**
     * @return array
     */
    public function beforeToHtmlDataProvider()
    {
        return array(
            array(
                new Varien_Object(array(
                    'role_id' => 1,
                    'role_name' => 'some_role'
                )),
                array('main_section', 'resource_section', 'user_section'),
            )
        );
    }
}
