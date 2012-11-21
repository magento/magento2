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
 * @package     Mage_Backend
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test class for Mage_Backend_Model_Auth.
 *
 * @group module:Mage_Backend
 */
class Mage_Backend_Model_MenuTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Backend_Model_Menu
     */
    protected $_model;

    public function setUp()
    {
        $this->_model = Mage::getModel('Mage_Backend_Model_Auth');
        Mage::getConfig()->setCurrentAreaCode(Mage::helper('Mage_Backend_Helper_Data')->getAreaCode());
    }

    protected function tearDown()
    {
        $this->_model = null;
    }

    public function testMenuItemManipulation()
    {
        /* @var $menu Mage_Backend_Model_Menu */
        $menu = Mage::getSingleton('Mage_Backend_Model_Menu_Config')->getMenu();
        /* @var $itemFactory Mage_Backend_Model_Menu_Item_Factory */
        $itemFactory = Mage::getModel('Mage_Backend_Model_Menu_Item_Factory');

        // Add new item in top level
        $menu->add($itemFactory->createFromArray(array(
            'id' => 'Mage_Backend::system2',
            'title' => 'Extended System',
            'module' => 'Mage_Backend',
            'resource' => 'Mage_Backend::system2'
        )));

         //Add submenu
        $menu->add($itemFactory->createFromArray(array(
            'id' => 'Mage_Backend::system2_acl',
            'title' => 'Acl',
            'module' => 'Mage_Backend',
            'action' => 'admin/backend/acl/index',
            'resource' => 'Mage_Backend::system2_acl',
        )), 'Mage_Backend::system2');

        // Modify existing menu item
        $menu->get('Mage_Backend::system2')->setTitle('Base system')
            ->setAction('admin/backend/system/base'); // remove dependency from config

        // Change sort order
        $menu->reorder('Mage_Backend::system', 40);

        // Remove menu item
        $menu->remove('Mage_Backend::catalog_attribute');

        // Move menu item
        $menu->move('Mage_Catalog::catalog_products', 'Mage_Backend::system2');
    }
}
