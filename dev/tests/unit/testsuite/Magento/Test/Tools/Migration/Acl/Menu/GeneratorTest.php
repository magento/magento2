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
namespace Magento\Test\Tools\Migration\Acl\Menu;


require_once realpath(
    __DIR__ . '/../../../../../../../../../'
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
    protected $_menuFiles = array();

    /**
     * @var array
     */
    protected $_menuIdToXPath = array();

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_fileManagerMock;

    protected function setUp()
    {
        $this->_fixturePath = realpath(__DIR__ . '/../') . '/_files';

        $aclXPathToId = array(
            'config/acl/resources/admin/system' => 'Module_Name::acl_resource',
            'config/acl/resources/admin/area_config/design/node' => 'Module_Name::acl_resource_design',
            'config/acl/resources/admin/area_config' => 'Module_Name::acl_resource_area',
            'config/acl/resources/admin/some_other_resource' => 'Module_Name::some_other_resource'
        );
        $this->_fileManagerMock = $this->getMock('Magento\Tools\Migration\Acl\FileManager');

        $this->_model = new \Magento\Tools\Migration\Acl\Menu\Generator(
            $this->_fixturePath,
            array(1),
            $aclXPathToId,
            $this->_fileManagerMock,
            false
        );

        $prefix = $this->_fixturePath . '/app/code/';
        $suffix = '/etc/adminhtml/menu.xml';

        $this->_menuFiles = array(
            $prefix . 'community/Namespace/Module' . $suffix,
            $prefix . 'core/ANamespace/Module' . $suffix,
            $prefix . 'core/BNamespace/Module' . $suffix,
            $prefix . 'local/Namespace/Module' . $suffix
        );

        $this->_menuIdToXPath = array(
            'Module_Name::system' => '/some/resource',
            'Module_Name::system_config' => 'system/config',
            'Module_Name::area_config_design_node' => 'area_config/design/node',
            'Some_Module::area_config_design' => 'area_config/design',
            'Magento_Module::area_config' => 'area_config',
            'Local_Module::area_config_design_node_email_template' => 'area_config/design/node/email_template'
        );
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
        $expected = array(
            'Module_Name::system' => array('parent' => '', 'resource' => '/some/resource'),
            'Module_Name::system_config' => array('parent' => 'Module_Name::system', 'resource' => ''),
            'Module_Name::area_config_design_node' => array(
                'parent' => 'Some_Module::area_config_design',
                'resource' => ''
            )
        );

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

        $expected = array(
            'Module_Name::area_config_design_node',
            'Some_Module::area_config_design',
            'Magento_Module::area_config'
        );
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
        $expectedMap = array(
            'Module_Name::area_config_design_node' => 'Module_Name::acl_resource_design',
            'Magento_Module::area_config' => 'Module_Name::acl_resource_area'
        );
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

        $domList = array($menuFileSource => $domSource);
        $menuIdToAclId = array('item1' => 'acl1', 'item2' => 'acl2', 'item3' => 'acl3');
        $aclXPathToId = array(
            'config/acl/resources/admin/some/resource' => 'acl4',
            'config/acl/resources/admin/some_other_resource' => 'acl5'
        );
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
        $menuDomList = array('file1' => $dom, 'file2' => $dom, 'file3' => $dom);
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
