<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tools\Migration\Acl\Menu;

require_once realpath(
    __DIR__ . '/../../../../../../../../'
) . '/tools/Magento/Tools/Migration/Acl/Menu/Generator.php';
/**
 * Tools_Migration_Acl_Menu_Generator_Menu generate test case
 */
class GeneratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var $model \Magento\Tools\Migration\Acl\Menu\Generator
     */
    protected $_model;

    /**
     * @var string
     */
    protected $_fixturePath;

    /**
     * @var array
     */
    protected $_menuFiles = [];

    /**
     * @var array
     */
    protected $_menuIdToXPath = [];

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_fileManagerMock;

    protected function setUp()
    {
        $this->_fixturePath = realpath(__DIR__ . '/../') . '/_files';

        $aclXPathToId = [
            'config/acl/resources/admin/system' => 'Module_Name::acl_resource',
            'config/acl/resources/admin/area_config/design/node' => 'Module_Name::acl_resource_design',
            'config/acl/resources/admin/area_config' => 'Module_Name::acl_resource_area',
            'config/acl/resources/admin/some_other_resource' => 'Module_Name::some_other_resource',
        ];
        $this->_fileManagerMock = $this->getMock('Magento\Tools\Migration\Acl\FileManager');

        $this->_model = new \Magento\Tools\Migration\Acl\Menu\Generator(
            $this->_fixturePath,
            [1],
            $aclXPathToId,
            $this->_fileManagerMock,
            false
        );

        $prefix = $this->_fixturePath . '/app/code/';
        $suffix = '/etc/adminhtml/menu.xml';

        $this->_menuFiles = [
            $prefix . 'community/Namespace/Module' . $suffix,
            $prefix . 'core/ANamespace/Module' . $suffix,
            $prefix . 'core/BNamespace/Module' . $suffix,
            $prefix . 'local/Namespace/Module' . $suffix,
        ];

        $this->_menuIdToXPath = [
            'Module_Name::system' => '/some/resource',
            'Module_Name::system_config' => 'system/config',
            'Module_Name::area_config_design_node' => 'area_config/design/node',
            'Some_Module::area_config_design' => 'area_config/design',
            'Magento_Module::area_config' => 'area_config',
            'Local_Module::area_config_design_node_email_template' => 'area_config/design/node/email_template',
        ];
    }

    public function testGetEtcPattern()
    {
        $path = $this->_fixturePath . '/app/code/*/*/*/etc/';

        $this->assertEquals($path, $this->_model->getEtcDirPattern());
    }

    public function testGetMenuFiles()
    {
        $this->assertEquals($this->_menuFiles, $this->_model->getMenuFiles());
    }

    public function testParseMenuNode()
    {
        $menuFile = $this->_menuFiles[0];
        $dom = new \DOMDocument();
        $dom->load($menuFile);
        $node = $dom->getElementsByTagName('menu')->item(0);
        $expected = [
            'Module_Name::system' => ['parent' => '', 'resource' => '/some/resource'],
            'Module_Name::system_config' => ['parent' => 'Module_Name::system', 'resource' => ''],
            'Module_Name::area_config_design_node' => [
                'parent' => 'Some_Module::area_config_design',
                'resource' => '',
            ],
        ];

        $this->assertEmpty($this->_model->getMenuIdMaps());
        $this->_model->parseMenuNode($node);
        $this->assertEquals($expected, $this->_model->getMenuIdMaps());
    }

    public function testParseMenuFiles()
    {
        $this->_model->parseMenuFiles();
        /**
         * Check that all nodes from all fixture files were read
         */
        $this->assertEquals(6, count($this->_model->getMenuIdMaps()));

        /**
         * Check that dom list is initialized
         */
        $domList = $this->_model->getMenuDomList();
        $this->assertEquals(4, count($domList));
        $this->assertEquals($this->_menuFiles, array_keys($domList));
        $this->assertInstanceOf('DOMDocument', current($domList));
    }

    public function testInitParentItems()
    {
        $this->_model->parseMenuFiles();
        $menuId = 'Local_Module::area_config_design_node_email_template';

        $maps = $this->_model->getMenuIdMaps();
        $this->assertArrayNotHasKey('parents', $maps[$menuId]);

        $this->_model->initParentItems($menuId);

        $expected = [
            'Module_Name::area_config_design_node',
            'Some_Module::area_config_design',
            'Magento_Module::area_config',
        ];
        $maps = $this->_model->getMenuIdMaps();
        $this->assertEquals($expected, $maps[$menuId]['parents']);
    }

    /**
     * @covers \Magento\Tools\Migration\Acl\Menu\Generator::buildMenuItemsXPath
     * @covers \Magento\Tools\Migration\Acl\Menu\Generator::buildXPath
     */
    public function testBuildMenuItemsXPath()
    {
        $this->_model->parseMenuFiles();
        $this->assertEmpty($this->_model->getIdToXPath());

        $this->_model->buildMenuItemsXPath();
        $maps = $this->_model->getIdToXPath();

        $this->assertEquals($this->_menuIdToXPath, $maps);
    }

    public function testMapMenuToAcl()
    {
        $this->assertEmpty($this->_model->getMenuIdToAclId());
        $this->_model->setIdToXPath($this->_menuIdToXPath);
        $result = $this->_model->mapMenuToAcl();
        $map = $this->_model->getMenuIdToAclId();
        $expectedMap = [
            'Module_Name::area_config_design_node' => 'Module_Name::acl_resource_design',
            'Magento_Module::area_config' => 'Module_Name::acl_resource_area',
        ];
        $this->assertEquals($expectedMap, $map);
        $this->assertEquals(array_keys($expectedMap), $result['mapped']);
        $this->assertEquals(4, count($result['not_mapped']));
        $this->assertEquals($expectedMap, json_decode(current($result['artifacts']), true));
    }

    public function testUpdateMenuAttributes()
    {
        $menuFileSource = $this->_fixturePath . '/update_menu_attributes_source.xml';
        $menuFileResult = $this->_fixturePath . '/update_menu_attributes_result.xml';

        $domSource = new \DOMDocument();
        $domSource->load($menuFileSource);

        $domExpected = new \DOMDocument();
        $domExpected->load($menuFileResult);

        $domList = [$menuFileSource => $domSource];
        $menuIdToAclId = ['item1' => 'acl1', 'item2' => 'acl2', 'item3' => 'acl3'];
        $aclXPathToId = [
            'config/acl/resources/admin/some/resource' => 'acl4',
            'config/acl/resources/admin/some_other_resource' => 'acl5',
        ];
        $this->_model->setMenuDomList($domList);
        $this->_model->setMenuIdToAclId($menuIdToAclId);
        $this->_model->setAclXPathToId($aclXPathToId);

        $errors = $this->_model->updateMenuAttributes();

        $this->assertEquals($domExpected->saveXML(), $domSource->saveXML());
        $this->assertEquals(2, count($errors));

        $this->assertContains('item4 is not mapped', $errors[0]);
        $this->assertContains($menuFileSource, $errors[0]);

        $this->assertContains('no ACL resource with XPath', $errors[1]);
        $this->assertContains($menuFileSource, $errors[1]);
    }

    public function testSaveMenuFiles()
    {
        $dom = new \DOMDocument();
        $menuDomList = ['file1' => $dom, 'file2' => $dom, 'file3' => $dom];
        $this->_model->setMenuDomList($menuDomList);

        $this->_fileManagerMock->expects(
            $this->at(0)
        )->method(
            'write'
        )->with(
            $this->equalTo('file1'),
            $this->equalTo($dom->saveXML())
        );

        $this->_fileManagerMock->expects(
            $this->at(1)
        )->method(
            'write'
        )->with(
            $this->equalTo('file2'),
            $this->equalTo($dom->saveXML())
        );

        $this->_fileManagerMock->expects(
            $this->at(2)
        )->method(
            'write'
        )->with(
            $this->equalTo('file3'),
            $this->equalTo($dom->saveXML())
        );

        $this->_model->saveMenuFiles();
    }
}
