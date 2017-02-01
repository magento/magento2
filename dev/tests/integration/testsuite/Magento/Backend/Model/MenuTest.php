<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
    private $model;

    /** @var \Magento\Framework\ObjectManagerInterface */
    private $objectManager;

    protected function setUp()
    {
        parent::setUp();
        \Magento\TestFramework\Helper\Bootstrap::getInstance()
            ->loadArea(\Magento\Backend\App\Area\FrontNameResolver::AREA_CODE);
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->model = $this->objectManager->create(\Magento\Backend\Model\Auth::class);
        $this->objectManager->get(\Magento\Framework\Config\ScopeInterface::class)
            ->setCurrentScope(\Magento\Backend\App\Area\FrontNameResolver::AREA_CODE);
    }

    public function testMenuItemManipulation()
    {
        /* @var $menu \Magento\Backend\Model\Menu */
        $menu = $this->objectManager->create(\Magento\Backend\Model\Menu\Config::class)->getMenu();
        /* @var $itemFactory \Magento\Backend\Model\Menu\Item\Factory */
        $itemFactory = $this->objectManager->create(\Magento\Backend\Model\Menu\Item\Factory::class);

        // Add new item in top level
        $menu->add(
            $itemFactory->create(
                [
                    'id' => 'Magento_Backend::system2',
                    'title' => 'Extended System',
                    'module' => 'Magento_Backend',
                    'resource' => 'Magento_Backend::system2',
                ]
            )
        );

        // Add submenu
        $menu->add(
            $itemFactory->create(
                [
                    'id' => 'Magento_Backend::system2_acl',
                    'title' => 'Acl',
                    'module' => 'Magento_Backend',
                    'action' => 'admin/backend/acl/index',
                    'resource' => 'Magento_Backend::system2_acl',
                ]
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

    /**
     * @magentoAppIsolation enabled
     */
    public function testSerialize()
    {
        /** @var Menu $menu */
        $menu = $this->objectManager->get(\Magento\Backend\Model\MenuFactory::class)->create();
        /* @var \Magento\Backend\Model\Menu\Item\Factory $itemFactory */
        $itemFactory = $this->objectManager->create(\Magento\Backend\Model\Menu\Item\Factory::class);

        // Add new item in top level
        $menu->add(
            $itemFactory->create(
                [
                    'id' => 'Magento_Backend::system3',
                    'title' => 'Extended System',
                    'module' => 'Magento_Backend',
                    'resource' => 'Magento_Backend::system3',
                ]
            )
        );

        // Add submenu
        $menu->add(
            $itemFactory->create(
                [
                    'id' => 'Magento_Backend::system3_acl',
                    'title' => 'Acl',
                    'module' => 'Magento_Backend',
                    'action' => 'admin/backend/acl/index',
                    'resource' => 'Magento_Backend::system3_acl',
                ]
            ),
            'Magento_Backend::system3'
        );
        $serializedString = $menu->serialize();
        $expected = '[{"parent_id":null,"module_name":"Magento_Backend","sort_index":null,"depends_on_config":null,'
            . '"id":"Magento_Backend::system3","resource":"Magento_Backend::system3","path":"","action":null,'
            . '"depends_on_module":null,"tooltip":"","title":"Extended System",'
            . '"target":null,"sub_menu":[{"parent_id":null,"module_name":"Magento_Backend","sort_index":null,'
            . '"depends_on_config":null,"id":"Magento_Backend::system3_acl","resource":"Magento_Backend::system3_acl",'
            . '"path":"","action":"admin\/backend\/acl\/index","depends_on_module":null,"tooltip":"","title":"Acl",'
            . '"target":null,"sub_menu":null}]}]';
        $this->assertEquals($expected, $serializedString);
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testUnserialize()
    {
        $serializedMenu = '[{"parent_id":null,"module_name":"Magento_Backend","sort_index":null,'
            . '"depends_on_config":null,"id":"Magento_Backend::system3","resource":"Magento_Backend::system3",'
            . '"path":"","action":null,"depends_on_module":null,"tooltip":"","title":"Extended System",'
            . '"target":null,"sub_menu":[{"parent_id":null,"module_name":"Magento_Backend","sort_index":null,'
            . '"depends_on_config":null,"id":"Magento_Backend::system3_acl","resource":"Magento_Backend::system3_acl",'
            . '"path":"","action":"admin\/backend\/acl\/index","depends_on_module":null,"tooltip":"","title":"Acl",'
            . '"target":null,"sub_menu":null}]}]';
        /** @var Menu $menu */
        $menu = $this->objectManager->get(\Magento\Backend\Model\MenuFactory::class)->create();
        $menu->unserialize($serializedMenu);
        $expected = [
            [
                'parent_id' => null,
                'module_name' => 'Magento_Backend',
                'sort_index' => null,
                'depends_on_config' => null,
                'id' => 'Magento_Backend::system3',
                'resource' => 'Magento_Backend::system3',
                'path' => '',
                'action' => null,
                'depends_on_module' => null,
                'tooltip' => '',
                'title' => 'Extended System',
                'target' => null,
                'sub_menu' =>
                    [
                        [
                            'parent_id' => null,
                            'module_name' => 'Magento_Backend',
                            'sort_index' => null,
                            'depends_on_config' => null,
                            'id' => 'Magento_Backend::system3_acl',
                            'resource' => 'Magento_Backend::system3_acl',
                            'path' => '',
                            'action' => 'admin/backend/acl/index',
                            'depends_on_module' => null,
                            'tooltip' => '',
                            'title' => 'Acl',
                            'sub_menu' => null,
                            'target' => null
                        ],
                    ],
            ],
        ];
        $this->assertEquals($expected, $menu->toArray());
    }
}
