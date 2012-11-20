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

class Mage_Backend_Model_Config_StructureTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Backend_Model_Config_Structure
     */
    protected $_model;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_appMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_converterMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_helperFactoryMock;

    public function setUp()
    {
        $filePath = dirname(__DIR__) . '/_files';

        $this->_appMock = $this->getMock('Mage_Core_Model_App', array(), array(), '', false);
        $this->_converterMock = $this->getMock('Mage_Backend_Model_Config_Structure_Converter');
        $this->_converterMock->expects($this->once())->method('convert')->will($this->returnValue(
            require $filePath . '/converted_config.php'
        ));

        $this->_helperFactoryMock = $this->getMock('Mage_Core_Model_Factory_Helper', array(), array(), '', false);
        $helperMock = $this->getMock('Mage_Backend_Helper_Data', array(), array(), '', false);
        $helperMock->expects($this->any())->method('__')->will($this->returnArgument(0));
        $this->_helperFactoryMock->expects($this->any())->method('get')->will($this->returnValue($helperMock));

        $this->_model = new Mage_Backend_Model_Config_Structure(array(
            'sourceFiles' => array(
                $filePath . '/system_2.xml'
            ),
            'app' => $this->_appMock,
            'converter' => $this->_converterMock,
            'helperFactory' => $this->_helperFactoryMock
        ));
    }

    public function testGetSectionsReturnsAllSections()
    {

        $sections = $this->_model->getSections();
        $this->assertCount(2, $sections);
        $section = reset($sections);
        $this->assertEquals('section_1', $section['id']);
        $section = next($sections);
        $this->assertEquals('section_2', $section['id']);
    }

    public function testGetSectionReturnsSectionByKey()
    {
        $section = $this->_model->getSection('section_1');
        $this->assertEquals('section_1', $section['id']);
        $section = $this->_model->getSection(null, 'section_1');
        $this->assertEquals('section_1', $section['id']);
        $section = $this->_model->getSection(null, null, 'section_1');
        $this->assertEquals('section_1', $section['id']);
    }

    public function testHasChildrenReturnsFalseIfNodeCannotBeShown()
    {
        $this->assertFalse($this->_model->hasChildren(array()));
    }

    public function testHasChildrenReturnsTrueIfNodeIsField()
    {
        $this->_appMock->expects($this->any())->method('isSingleStoreMode')->will($this->returnValue(true));
        $this->assertTrue($this->_model->hasChildren(array()));
    }

    public function testHasChildrenReturnsFalseForEmptySection()
    {
        $this->_appMock->expects($this->any())->method('isSingleStoreMode')->will($this->returnValue(true));
        $this->assertFalse($this->_model->hasChildren(array('groups' => array())));
    }

    public function testHasChildrenReturnsFalseForEmptyGroup()
    {
        $this->_appMock->expects($this->any())->method('isSingleStoreMode')->will($this->returnValue(true));
        $this->assertFalse($this->_model->hasChildren(array('fields' => array())));
    }

    public function testCanShowNodeReturnsFalseByDefault()
    {
        $this->assertFalse($this->_model->hasChildren(array()));
    }

    /**
     * @param array $node
     * @param string $website
     * @param string $store
     * @dataProvider testCanShowNodeReturnsTrueForDisplayableNodesDataProvider
     */
    public function testCanShowNodeReturnsTrueForDisplayableNodes($node, $website, $store)
    {
        $this->assertTrue($this->_model->hasChildren($node, $website, $store));
    }

    public static function testCanShowNodeReturnsTrueForDisplayableNodesDataProvider()
    {
        return array(
            array(array('showInStore' => 1), null, 'store'),
            array(array('showInWebsite' => 1), 'website', null),
            array(array('showInDefault' => 1), null, null)
        );
    }

    /**
     * @param array $node
     * @param string $website
     * @param string $store
     * @dataProvider testCanShowNodeReturnsFalseForNonDisplayableNodesDataProvider
     */
    public function testCanShowNodeReturnsFalseForNonDisplayableNodes($node, $website, $store)
    {
        $this->assertFalse($this->_model->hasChildren($node, $website, $store));
    }

    public static function testCanShowNodeReturnsFalseForNonDisplayableNodesDataProvider()
    {
        return array(
            array(array('showInStore' => 0), null, 'store'),
            array(array('showInWebsite' => 0), 'website', null),
            array(array('showInStore' => 1), 'website', null),
            array(array('showInWebsite' => 1), null, 'store'),
            array(array('showInDefault' => 0), null, null)
        );
    }

    public function testCanShowNodeReturnsTrueForNonDisplayableNodesInSingleStoreMode()
    {
        $this->_appMock->expects($this->any())->method('isSingleStoreMode')->will($this->returnValue(true));
        $this->assertTrue($this->_model->hasChildren(array(), null, null));
    }

    public function testCanShowNodeReturnsFalseForNonDisplayableNodesInSingleStoreModeWithFlag()
    {
        $this->_appMock->expects($this->any())->method('isSingleStoreMode')->will($this->returnValue(true));
        $this->assertTrue($this->_model->hasChildren(array('hide_in_single_store_mode'), null, null));
    }

    public function testGetAttributeModuleReturnsBackendModuleByDefault()
    {
        $this->assertEquals('Mage_Backend', $this->_model->getAttributeModule());
    }

    public function testGetAttributeModuleExtractsModuleAttributeFromNodes()
    {
        $this->assertEquals('Mage_Module1', $this->_model->getAttributeModule(
            array('module' => 'Mage_Module1')
        ));
        $this->assertEquals('Mage_Module2', $this->_model->getAttributeModule(
            array('module' => 'Mage_Module1'), array('module' => 'Mage_Module2')
        ));
        $this->assertEquals('Mage_Module3', $this->_model->getAttributeModule(
            array('module' => 'Mage_Module1'), array('module' => 'Mage_Module2'), array('module' => 'Mage_Module3')
        ));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testGetSystemConfigNodeLabelThrowsExceptionIfSectionNameIsWrong()
    {
        $this->_model->getSystemConfigNodeLabel('unexistentSection');
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testGetSystemConfigNodeLabelThrowsExceptionIfGroupNameIsWrong()
    {
        $this->_model->getSystemConfigNodeLabel('section_1', 'unexistent_group');
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testGetSystemConfigNodeLabelThrowsExceptionIfFieldNameIsWrong()
    {
        $this->_model->getSystemConfigNodeLabel('section_1', 'group_1', 'unexistent_field');
    }

    public function testGetSystemConfigNodeLabelRetreivesLabel()
    {
        $this->assertEquals('Section 1 New', $this->_model->getSystemConfigNodeLabel('section_1'));
        $this->assertEquals('Group 1 New', $this->_model->getSystemConfigNodeLabel('section_1', 'group_1'));
        $this->assertEquals('Field 2', $this->_model->getSystemConfigNodeLabel('section_1', 'group_1', 'field_2'));
    }

    public function testGetEncryptedNodeEntriesPathsReturnsListOfEncryptedFieldPaths()
    {
        $expected = array(
            'section_1/group_1/field_2',
            'section_2/group_3/field_4'
        );
        $this->assertEquals($expected, $this->_model->getEncryptedNodeEntriesPaths());
    }

    public function testGetEncryptedNodeEntriesPathsReturnsListOfEncryptedFieldPathsReturnsExplodedPaths()
    {
        $expected = array(
            array('section' => 'section_1', 'group' => 'group_1', 'field' => 'field_2'),
            array('section' => 'section_2', 'group' => 'group_3', 'field' => 'field_4')
        );
        $this->assertEquals($expected, $this->_model->getEncryptedNodeEntriesPaths(true));
    }
}
