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
namespace Magento\Backend\Model\Config;

class StructureTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Backend\Model\Config\Structure
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_flyweightFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_tabIteratorMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_structureDataMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_scopeDefinerMock;

    /**
     * @var array
     */
    protected $_structureData;

    protected function setUp()
    {
        $this->_flyweightFactory = $this->getMock(
            'Magento\Backend\Model\Config\Structure\Element\FlyweightFactory',
            array(),
            array(),
            '',
            false
        );
        $this->_tabIteratorMock = $this->getMock(
            'Magento\Backend\Model\Config\Structure\Element\Iterator\Tab',
            array(),
            array(),
            '',
            false
        );
        $this->_structureDataMock = $this->getMock(
            'Magento\Backend\Model\Config\Structure\Data',
            array(),
            array(),
            '',
            false
        );
        $this->_scopeDefinerMock = $this->getMock(
            'Magento\Backend\Model\Config\ScopeDefiner',
            array(),
            array(),
            '',
            false
        );
        $this->_scopeDefinerMock->expects($this->any())->method('getScope')->will($this->returnValue('scope'));

        $filePath = dirname(__DIR__) . '/_files';
        $this->_structureData = require $filePath . '/converted_config.php';
        $this->_structureDataMock->expects(
            $this->once()
        )->method(
            'get'
        )->will(
            $this->returnValue($this->_structureData['config']['system'])
        );
        $this->_model = new \Magento\Backend\Model\Config\Structure(
            $this->_structureDataMock,
            $this->_tabIteratorMock,
            $this->_flyweightFactory,
            $this->_scopeDefinerMock
        );
    }

    protected function tearDown()
    {
        unset($this->_flyweightFactory);
        unset($this->_scopeDefinerMock);
        unset($this->_structureData);
        unset($this->_tabIteratorMock);
        unset($this->_structureDataMock);
        unset($this->_model);
    }

    public function testGetTabsBuildsSectionTree()
    {
        $this->_structureDataMock = $this->getMock(
            'Magento\Backend\Model\Config\Structure\Data',
            array(),
            array(),
            '',
            false
        );
        $this->_structureDataMock->expects(
            $this->any()
        )->method(
            'get'
        )->will(
            $this->returnValue(
                array('sections' => array('section1' => array('tab' => 'tab1')), 'tabs' => array('tab1' => array()))
            )
        );
        $expected = array('tab1' => array('children' => array('section1' => array('tab' => 'tab1'))));
        $model = new \Magento\Backend\Model\Config\Structure(
            $this->_structureDataMock,
            $this->_tabIteratorMock,
            $this->_flyweightFactory,
            $this->_scopeDefinerMock
        );
        $this->_tabIteratorMock->expects($this->once())->method('setElements')->with($expected);
        $this->assertEquals($this->_tabIteratorMock, $model->getTabs());
    }

    /**
     * @param string $path
     * @param string $expectedType
     * @param string $expectedId
     * @param string $expectedPath
     * @dataProvider emptyElementDataProvider
     */
    public function testGetElementReturnsEmptyElementIfNotExistingElementIsRequested(
        $path,
        $expectedType,
        $expectedId,
        $expectedPath
    ) {
        $expectedConfig = array('id' => $expectedId, 'path' => $expectedPath, '_elementType' => $expectedType);
        $elementMock = $this->getMock('Magento\Backend\Model\Config\Structure\ElementInterface');
        $elementMock->expects($this->once())->method('setData')->with($expectedConfig);
        $this->_flyweightFactory->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            $expectedType
        )->will(
            $this->returnValue($elementMock)
        );
        $this->assertEquals($elementMock, $this->_model->getElement($path));
    }

    public function emptyElementDataProvider()
    {
        return array(
            array('someSection/group_1/nonexisting_field', 'field', 'nonexisting_field', 'someSection/group_1'),
            array('section_1/group_1/nonexisting_field', 'field', 'nonexisting_field', 'section_1/group_1'),
            array('section_1/nonexisting_group', 'group', 'nonexisting_group', 'section_1'),
            array('nonexisting_section', 'section', 'nonexisting_section', '')
        );
    }

    public function testGetElementReturnsProperElementByPath()
    {
        $elementMock = $this->getMock(
            'Magento\Backend\Model\Config\Structure\Element\Field',
            array(),
            array(),
            '',
            false
        );
        $section = $this->_structureData['config']['system']['sections']['section_1'];
        $fieldData = $section['children']['group_level_1']['children']['field_3'];
        $elementMock->expects($this->once())->method('setData')->with($fieldData, 'scope');

        $this->_flyweightFactory->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            'field'
        )->will(
            $this->returnValue($elementMock)
        );
        $this->assertEquals($elementMock, $this->_model->getElement('section_1/group_level_1/field_3'));
    }

    public function testGetFirstSectionReturnsFirstAllowedSection()
    {
        $tabMock = $this->getMock(
            'Magento\Backend\Model\Config\Structure\Element\Tab',
            array('current', 'getChildren', 'rewind'),
            array(),
            '',
            false
        );
        $tabMock->expects($this->any())->method('getChildren')->will($this->returnSelf());
        $tabMock->expects($this->once())->method('rewind');
        $tabMock->expects($this->once())->method('current')->will($this->returnValue('currentSection'));
        $this->_tabIteratorMock->expects($this->once())->method('rewind');
        $this->_tabIteratorMock->expects($this->once())->method('current')->will($this->returnValue($tabMock));
        $this->assertEquals('currentSection', $this->_model->getFirstSection());
    }

    public function testGetElementReturnsProperElementByPathCachesObject()
    {
        $elementMock = $this->getMock(
            'Magento\Backend\Model\Config\Structure\Element\Field',
            array(),
            array(),
            '',
            false
        );
        $section = $this->_structureData['config']['system']['sections']['section_1'];
        $fieldData = $section['children']['group_level_1']['children']['field_3'];
        $elementMock->expects($this->once())->method('setData')->with($fieldData, 'scope');

        $this->_flyweightFactory->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            'field'
        )->will(
            $this->returnValue($elementMock)
        );
        $this->assertEquals($elementMock, $this->_model->getElement('section_1/group_level_1/field_3'));
        $this->assertEquals($elementMock, $this->_model->getElement('section_1/group_level_1/field_3'));
    }

    /**
     * @param $attributeName
     * @param $attributeValue
     * @param $paths
     * @dataProvider getFieldPathsByAttributeDataProvider
     */
    public function testGetFieldPathsByAttribute($attributeName, $attributeValue, $paths)
    {
        $this->assertEquals($paths, $this->_model->getFieldPathsByAttribute($attributeName, $attributeValue));
    }

    public function getFieldPathsByAttributeDataProvider()
    {
        return array(
            array('backend_model', 'Magento\Backend\Model\Config\Backend\Encrypted', array(
                'section_1/group_1/field_2',
                'section_1/group_level_1/group_level_2/group_level_3/field_3_1_1',
                'section_2/group_3/field_4',
            )),
            array('attribute_2', 'test_value_2', array('section_2/group_3/field_4'))
        );
    }
}
