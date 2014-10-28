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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Backend\Model;

/**
 * Test class for \Magento\Backend\Model\Auth.
 *
 * @magentoAppArea adminhtml
 */
class MenuTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Backend\Model\Menu
     */
    protected $_model;

    protected function setUp()
    {
        parent::setUp();
        \Magento\TestFramework\Helper\Bootstrap::getInstance()
            ->loadArea(\Magento\Backend\App\Area\FrontNameResolver::AREA_CODE);
        $this->_model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Backend\Model\Auth');
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Framework\Config\ScopeInterface')
            ->setCurrentScope(\Magento\Backend\App\Area\FrontNameResolver::AREA_CODE);
    }

    public function testMenuItemManipulation()
    {
        /* @var $menu \Magento\Backend\Model\Menu */
        $menu = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Backend\Model\Menu\Config'
        )->getMenu();
        /* @var $itemFactory \Magento\Backend\Model\Menu\Item\Factory */
        $itemFactory = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Backend\Model\Menu\Item\Factory'
        );

        // Add new item in top level
        $menu->add(
            $itemFactory->create(
                array(
                    'id' => 'Magento_Backend::system2',
                    'title' => 'Extended System',
                    'module' => 'Magento_Backend',
                    'resource' => 'Magento_Backend::system2'
                )
            )
        );

        //Add submenu
        $menu->add(
            $itemFactory->create(
                array(
                    'id' => 'Magento_Backend::system2_acl',
                    'title' => 'Acl',
                    'module' => 'Magento_Backend',
                    'action' => 'admin/backend/acl/index',
                    'resource' => 'Magento_Backend::system2_acl'
                )
            ),
            'Magento_Backend::system2'
        );

        // Modify existing menu item
        $menu->get('Magento_Backend::system2')->setTitle('Base system')->setAction('admin/backend/system/base');
        // remove dependency from config

        // Change sort order
        $menu->reorder('Magento_Backend::system', 40);

        // Remove menu item
        $menu->remove('Magento_Backend::catalog_attribute');

        // Move menu item
        $menu->move('Magento_Catalog::catalog_products', 'Magento_Backend::system2');
    }
}
