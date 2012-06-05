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
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test class for Mage_Core_Model_Layout_Element
 */
class Mage_Admin_Model_ConfigTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Admin_Model_Config
     */
    protected $_config;

    public function setUp()
    {
        $config = $this->getMock('Mage_Core_Model_Config', array('loadModulesConfiguration'), array(), '', false);
        $userHelper = $this->getMock("Mage_User_Helper_Data");
        $userHelper->expects($this->any())->method('__')->will($this->returnValue('User_Translation'));

        $backendHelper = $this->getMock('Mage_Backend_Helper_Data');
        $backendHelper->expects($this->any())->method('__')->will($this->returnValue('Backend_Translation'));

        $this->_config = new Mage_Admin_Model_Config(
            array(
                'app' => $this->getMock('Mage_Core_Model_App'),
                'appConfig' => $config,
                'helpers' => array(
                    'Mage_User' => $userHelper,
                    'Mage_Backend' => $backendHelper
                )
            )
        );

        $this->_config->getAdminhtmlConfig()->loadFile(__DIR__ . '/_files/adminhtml.xml');
    }

    public function testGetAclResourceTree()
    {
        $tree = $this->_config->getAclResourceTree();
        $this->assertEquals('admin', $tree->admin->getAttribute('aclpath'));
        $this->assertEquals('Mage_Backend', $tree->admin->getAttribute('module_c'));
        $this->assertEquals(
            'admin/system/acl/users',
            $tree->admin->children->system->children->acl->children->users->getAttribute('aclpath')
        );
        $this->assertEquals(
            'Mage_User',
            $tree->admin->children->system->children->acl->children->users->getAttribute('module_c')
        );
    }

    public function testGetAclResourceList()
    {
        $list = $this->_config->getAclResourceList();
        $this->assertEquals(
            $list,
            array(
                'admin/system/acl/users' => array('name' => 'User_Translation', 'level' => 6),
                'admin/system/acl/roles' => array('name' => 'User_Translation', 'level' => 6),
                'admin/system/acl' => array('name' => 'User_Translation', 'level' => 4),
                'admin/system' => array('name' => 'Backend_Translation', 'level' => 2),
                'admin' => array('name' => 'Backend_Translation', 'level' => 0)
            )
        );
    }

    public function testGetAclResourceListShortFormat()
    {
        $list = $this->_config->getAclResourceList(true);
        $this->assertEquals(
            $list,
            array(
                'admin/system/acl/users',
                'admin/system/acl/roles',
                'admin/system/acl',
                'admin/system',
                'admin'
            )
        );
    }
}
