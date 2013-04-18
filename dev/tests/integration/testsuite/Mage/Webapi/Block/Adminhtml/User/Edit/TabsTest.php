<?php
/**
 * Test for Mage_Webapi_Block_Adminhtml_User_Edit_Tabs block.
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
class Mage_Webapi_Block_Adminhtml_User_Edit_TabsTest extends Mage_Backend_Area_TestCase
{
    /**
     * @var Magento_Test_ObjectManager
     */
    protected $_objectManager;

    /**
     * @var Mage_Core_Model_Layout
     */
    protected $_layout;

    /**
     * @var Mage_Webapi_Block_Adminhtml_User_Edit_Tabs
     */
    protected $_block;

    protected function setUp()
    {
        parent::setUp();

        $this->_objectManager = Mage::getObjectManager();
        $this->_layout = $this->_objectManager->get('Mage_Core_Model_Layout');
        $this->_block = $this->_layout->createBlock('Mage_Webapi_Block_Adminhtml_User_Edit_Tabs',
            'webapi.user.edit.tabs');
    }

    protected function tearDown()
    {
        $this->_objectManager->removeSharedInstance('Mage_Core_Model_Layout');
        unset($this->_objectManager, $this->_layout, $this->_block);
    }

    /**
     * Test _beforeToHtml method.
     */
    public function testBeforeToHtml()
    {
        // TODO: Move to unit tests after MAGETWO-4015 complete.
        /** @var Mage_Webapi_Block_Adminhtml_User_Edit_Tab_Main $mainTabBlock */
        $mainTabBlock = $this->_layout->addBlock(
            'Mage_Core_Block_Text',
            'webapi.user.edit.tab.main',
            'webapi.user.edit.tabs'
        )->setText('Main Block Content');

        $this->_layout->addBlock(
            'Mage_Core_Block_Text',
            'webapi.user.edit.tab.roles.grid',
            'webapi.user.edit.tabs'
        )->setText('Grid Block Content');

        $apiUser = new Varien_Object(array(
            'role_id' => 1
        ));
        $this->_block->setApiUser($apiUser);
        $this->_block->toHtml();

        $this->assertSame($apiUser, $mainTabBlock->getApiUser());

        $tabs = $this->_getProtectedTabsValue($this->_block);
        $this->assertArrayHasKey('main_section', $tabs);
        $this->assertInstanceOf('Varien_Object', $tabs['main_section']);
        $this->assertEquals(array(
            'label' => 'User Info',
            'title' => 'User Info',
            'content' => 'Main Block Content',
            'active' => '1',
            'url' => '#',
            'id' => 'main_section',
            'tab_id' => 'main_section',
        ), $tabs['main_section']->getData());

        $this->assertArrayHasKey('roles_section', $tabs);
        $this->assertInstanceOf('Varien_Object', $tabs['roles_section']);
        $this->assertEquals(array(
            'label' => 'User Role',
            'title' => 'User Role',
            'content' => 'Grid Block Content',
            'url' => '#',
            'id' => 'roles_section',
            'tab_id' => 'roles_section'
        ), $tabs['roles_section']->getData());
    }

    /**
     * Get protected _tabs property of Mage_Backend_Block_Widget_Tabs block.
     *
     * @param Mage_Backend_Block_Widget_Tabs $tabs
     * @return array
     */
    protected function _getProtectedTabsValue(Mage_Backend_Block_Widget_Tabs $tabs)
    {
        $result = null;
        try {
            $classReflection = new ReflectionClass(get_class($tabs));
            $tabsProperty = $classReflection->getProperty('_tabs');
            $tabsProperty->setAccessible(true);
            $result = $tabsProperty->getValue($tabs);
        } catch (ReflectionException $exception) {
            $this->fail('Cannot get tabs value');

        }
        $this->assertInternalType('array', $result, 'Tabs value is expected to be an array');
        return $result;
    }
}
